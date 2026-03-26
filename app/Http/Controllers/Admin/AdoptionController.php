<?php

namespace App\Http\Controllers\Admin;

use App\Models\Adoption;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdoptionController extends Controller
{
    public function index() 
    {
        $applications = Adoption::with('pet', 'user')->latest()->paginate(15);
        return Inertia::render('Admin/Adoptions/Index', [
            'applications' => $applications,
        ]);
    }

    public function show(Adoption $adoption) 
    {
        $adoption->load('pet', 'user'); 
        return Inertia::render('Admin/Adoptions/Show', [
            'adoption' => $adoption,
        ]);
    }

    /**
     * Approves an adoption application and updates the pet status (if needed).
     */
    public function approve(Adoption $adoption)
    {
        // 1. Update the adoption status
        $adoption->update(['status' => 'Approved']);

        // 2. Optionally, update the pet's status (e.g., to 'Reserved' or 'Adopted')
        if ($adoption->pet) {
            $adoption->pet->update(['status' => 'Adopted']);
        }
        
        // 3. Redirect back with a success message
        return redirect()->route('admin.adoptions.index')
                         ->with('success', 'Application #' . $adoption->id . ' approved successfully. The pet status has been updated.');
    }

    /**
     * Rejects an adoption application.
     */
    public function reject(Adoption $adoption)
    {
       
        $adoption->update(['status' => 'Rejected']);
        
        
        return redirect()->route('admin.adoptions.index')
                         ->with('error', 'Application #' . $adoption->id . ' has been rejected.');
    }
}
