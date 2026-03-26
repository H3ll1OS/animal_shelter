<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DonationController extends Controller
{
    /**
     * Display a listing of donations for the admin panel.
     */
    public function index()
    {
        $donations = Donation::latest()->paginate(20);
        return Inertia::render('Admin/Donations/Index', [
            'donations' => $donations,
        ]);
    }

    /**
     * Display the specified donation record.
     */
    public function show(Donation $donation)
    {
        $donation->load('user:id,name,email');

        return Inertia::render('Admin/Donations/Show', [
            'donation' => $donation,
        ]);
    }
    
    /**
     * Remove the specified donation from storage.
     */
    public function destroy(Donation $donation)
    {
        $donation->delete();
        
        return redirect()->route('admin.donations.index')
                         ->with('error', 'Donation record deleted successfully.');
    }
}
