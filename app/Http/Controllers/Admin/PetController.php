<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pet; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PetController extends Controller
{
    /**
     * Display a listing of the pets.
     */
    public function index()
    {
        $pets = Pet::latest()->paginate(15);
        return Inertia::render('Admin/Pets/Index', ['pets' => $pets]);
    }

    /**
     * Show the form for creating a new pet.
     */
    public function create()
    {
        return Inertia::render('Admin/Pets/Create');
    }

    /**
     * Store a newly created pet in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'species' => ['required', Rule::in(['Dog', 'Cat', 'Rabbit', 'Bird', 'Other'])],
            'breed' => 'nullable|string|max:255',
            'age' => 'required|integer|min:0',
            'gender' => ['required', Rule::in(['Male', 'Female', 'Unknown'])],
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['Available', 'Adopted', 'Sick', 'Fostered', 'Euthanized'])],
            'date_received' => 'required|date',
            'image' => 'nullable|image|max:2048', 
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('pets', 'public');
        }

        Pet::create($data);

        return redirect()->route('admin.pets.index')->with('success', 'New pet added successfully!');
    }

    /**
     * Display the specified pet.
     */
    public function show(Pet $pet)
    {
        $pet->load('healthRecords'); 
        return Inertia::render('Admin/Pets/Show', ['pet' => $pet]);
    }

    /**
     * Show the form for editing the specified pet.
     */
    public function edit(Pet $pet)
    {
        return Inertia::render('Admin/Pets/Edit', ['pet' => $pet]);
    }

    public function update(Request $request, Pet $pet)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'species' => ['required', Rule::in(['Dog', 'Cat', 'Rabbit', 'Bird', 'Other'])],
            'breed' => 'nullable|string|max:255',
            'age' => 'required|integer|min:0',
            'gender' => ['required', Rule::in(['Male', 'Female', 'Unknown'])],
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['Available', 'Adopted', 'Sick', 'Fostered', 'Euthanized'])],
            'date_received' => 'required|date',
            'image' => 'nullable|image|max:2048', 
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            if ($pet->image_path) {
                Storage::disk('public')->delete($pet->image_path);
            }
            $data['image_path'] = $request->file('image')->store('pets', 'public');
        }

        $pet->update($data);

        return redirect()->route('admin.pets.index')->with('success', $pet->name . ' updated successfully!');
    }

    /**
     * Remove the specified pet from storage.
     */
    public function destroy(Pet $pet)
    {
        $pet->healthRecords()->delete();
        $pet->vaccineSchedules()->delete();
        $pet->adoptions()->delete();
        
        if ($pet->image_path) {
            Storage::disk('public')->delete($pet->image_path);
        }
        
        $pet->delete();

        return redirect()->route('admin.pets.index')->with('error', $pet->name . ' record and all associated data deleted.');
    }
}

