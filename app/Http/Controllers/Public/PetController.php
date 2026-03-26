<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Models\Adoption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PetController extends Controller
{
    /**
     * Display a listing of available pets for adoption.
     */
    public function index()
    {
        $species = request()->query('species', 'all');

        $query = Pet::where('status', 'Available')
            ->whereDoesntHave('euthanasiaLogs');

        if ($species && $species !== 'all') {
            $normalized = strtolower($species);
            if (in_array($normalized, ['dog', 'cat'], true)) {
                $query->whereRaw('LOWER(species) LIKE ?', ['%' . $normalized . '%']);
            }
        }

        $pets = $query->paginate(6)->appends([
            'species' => $species,
        ]);

        return Inertia::render('Frontend/Adopt', [
            'pets' => $pets,
            'filters' => [
                'species' => $species,
                'age' => request()->query('age', 'all'),
                'size' => request()->query('size', 'all'),
                'gender' => request()->query('gender', 'all'),
                'sort' => request()->query('sort', 'name_asc'),
            ]
        ]);
    }

    /**
     * Display the specified pet.
     */
    public function show(Pet $pet)
    {
        if ($pet->status !== 'Available' || $pet->euthanasiaLogs()->exists()) {
            abort(404);
        }
        return Inertia::render('Frontend/Pet', [
            'pet' => $pet,
            'user' => Auth::user()
        ]);
    }

    /**
     * Submit an adoption application for the specified pet.
     */
    public function adopt(Request $request, Pet $pet)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string',
            'documents' => 'required|array|min:1',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $documentPaths = collect($request->file('documents'))
            ->map(fn ($file) => $file->store('adoption-documents', 'public'))
            ->values()
            ->toJson();

        Adoption::create([
            'pet_id' => $pet->id,
            'user_id' => Auth::id(),
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'message' => $request->message ?? '',
            'document_path' => $documentPaths,
            'status' => 'Pending',
        ]);

        return redirect()->back()->with('success', 'Adoption application submitted successfully!');
    }
}

