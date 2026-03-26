<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Adoption;
use App\Models\Donation;
use App\Models\Pet;
use App\Models\User;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        // 1. Core Counts
        $totalPets = Pet::count(); 
        $availablePets = Pet::where('status', 'Available')->count();
        $newApplications = Adoption::where('status', 'Pending Review')->count();
        
        $monthlyDonations = Donation::whereYear('created_at', now()->year)
                                    ->whereMonth('created_at', now()->month)
                                    ->sum('amount');
        
        // 2. Recent Activity
        $recentApplications = Adoption::with('pet', 'user')->latest()->take(5)->get();

        return Inertia::render('Admin/Dashboard', [
            'totalPets' => $totalPets,
            'availablePets' => $availablePets,
            'newApplications' => $newApplications,
            'recentApplications' => $recentApplications,
            'monthlyDonations' => $monthlyDonations
        ]);
    }
}

