<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Inertia\Inertia;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Let privileged users choose where to go after login.
     */
    public function dashboardChoice()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Non-privileged users back home
        if (!$user->isAdmin() && !$user->isDeveloper()) {
            return redirect()->intended('/');
        }

        return Inertia::render('Auth/DashboardChoice', ['user' => $user]);
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user(); 

            if (!$user->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => ['Your account is blocked. Please contact an administrator.'],
                ]);
            }

            // Privileged users get a choice page
            if ($user->isDeveloper() || $user->role === 'admin') {
                return redirect()->route('dashboard.choice');
            }

            // Regular users go to the intended/public area
            return redirect()->intended('/');
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

