<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $featuredAnimals = Pet::where('status', 'Available')
                                ->whereDoesntHave('euthanasiaLogs')
                                ->limit(6)
                                ->get(); 

        return Inertia::render('Frontend/Home', [
            'featuredAnimals' => $featuredAnimals
        ]);
    }

    public function about()
    {
        return Inertia::render('Frontend/About');
    }

    public function donate()
    {
        return Inertia::render('Frontend/Donate');
    }

    public function contact()
    {
        return Inertia::render('Frontend/Contact');
    }
}
