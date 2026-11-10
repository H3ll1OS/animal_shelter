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
    public function index(Request $request)
    {
        $filters = [
            'species' => strtolower((string) $request->query('species', 'all')),
            'age' => strtolower((string) $request->query('age', 'all')),
            'size' => strtolower((string) $request->query('size', 'all')),
            'gender' => strtolower((string) $request->query('gender', 'all')),
            'sort' => (string) $request->query('sort', 'name_asc'),
        ];

        if (! in_array($filters['species'], ['all', 'dog', 'cat'], true)) {
            $filters['species'] = 'all';
        }
        if (! in_array($filters['age'], ['all', 'baby', 'young', 'adult', 'senior'], true)) {
            $filters['age'] = 'all';
        }
        if (! in_array($filters['size'], ['all', 'small', 'medium', 'large'], true)) {
            $filters['size'] = 'all';
        }
        if (! in_array($filters['gender'], ['all', 'male', 'female'], true)) {
            $filters['gender'] = 'all';
        }
        if (! in_array($filters['sort'], ['name_asc', 'name_desc', 'age_asc', 'age_desc'], true)) {
            $filters['sort'] = 'name_asc';
        }

        $query = Pet::where('status', 'Available')
            ->whereDoesntHave('euthanasiaLogs');

        if ($filters['species'] !== 'all') {
            $query->whereRaw('LOWER(species) = ?', [$filters['species']]);
        }

        if ($filters['age'] !== 'all') {
            [$minAge, $maxAge] = match ($filters['age']) {
                'baby' => [0, 1],
                'young' => [1, 3],
                'adult' => [3, 8],
                'senior' => [8, null],
            };

            $query->where('age', '>=', $minAge);
            if ($maxAge !== null) {
                $query->where('age', '<', $maxAge);
            }
        }

        if ($filters['size'] !== 'all') {
            $query->whereRaw('LOWER(size) = ?', [$filters['size']]);
        }

        if ($filters['gender'] !== 'all') {
            $query->whereRaw('LOWER(gender) = ?', [$filters['gender']]);
        }

        if (str_starts_with($filters['sort'], 'age_')) {
            $query->orderByRaw('CASE WHEN age IS NULL THEN 1 ELSE 0 END')
                ->orderBy('age', $filters['sort'] === 'age_asc' ? 'asc' : 'desc')
                ->orderBy('name')
                ->orderBy('id');
        } else {
            $query->orderBy('name', $filters['sort'] === 'name_asc' ? 'asc' : 'desc')
                ->orderBy('id');
        }

        $pets = $query->paginate(6)->appends($filters);

        return Inertia::render('Frontend/Adopt', [
            'pets' => $pets,
            'filters' => $filters,
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
        if ($pet->status !== 'Available' || $pet->euthanasiaLogs()->exists()) {
            return redirect()->back()->with('error', 'This pet is no longer available for adoption.');
        }

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

