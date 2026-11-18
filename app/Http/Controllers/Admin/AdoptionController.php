<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Adoption;
use App\Services\AdoptionDecisionService;
use Inertia\Inertia;

class AdoptionController extends Controller
{
    public function index()
    {
        Adoption::query()
            ->whereNull('admin_seen_at')
            ->update(['admin_seen_at' => now()]);

        $applications = Adoption::with('pet', 'user')->latest()->paginate(15);

        return Inertia::render('Admin/Adoptions/Index', [
            'applications' => $applications,
        ]);
    }

    public function show(Adoption $adoption)
    {
        if ($adoption->admin_seen_at === null) {
            $adoption->update(['admin_seen_at' => now()]);
        }

        $adoption->load('pet', 'user');

        return Inertia::render('Admin/Adoptions/Show', [
            'adoption' => $adoption,
        ]);
    }

    /**
     * Approves an adoption application and updates the pet status (if needed).
     */
    public function approve(Adoption $adoption, AdoptionDecisionService $decisions)
    {
        $result = $decisions->approve($adoption);
        if ($result !== 'approved') {
            $message = $result === 'pet_unavailable'
                ? 'This pet is no longer available for adoption.'
                : 'Only pending applications can be approved.';

            return back()->with('error', $message);
        }

        return redirect()->route('admin.adoptions.index')
            ->with('success', 'Application #'.$adoption->id.' approved successfully. The pet status has been updated.');
    }

    /**
     * Rejects an adoption application.
     */
    public function reject(Adoption $adoption, AdoptionDecisionService $decisions)
    {
        if ($decisions->reject($adoption) !== 'rejected') {
            return back()->with('error', 'Only pending applications can be rejected.');
        }

        return redirect()->route('admin.adoptions.index')
            ->with('error', 'Application #'.$adoption->id.' has been rejected.');
    }
}
