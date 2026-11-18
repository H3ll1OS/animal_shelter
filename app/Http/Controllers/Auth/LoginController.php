<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Support\ActivityLogger;
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

        return redirect()->to($user->isSuperAdmin() ? route('superadmin.dashboard') : ($user->isAdmin() ? route('admin.index') : '/'));
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request, ActivityLogger $logger)
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

            $user->forceFill(['last_login_at' => now()])->save();
            $logger->record('account_login', 'security', $user, ['channel' => 'web'], $user, $request);

            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }

            if ($user->isAdmin()) {
                return redirect()->route('admin.index');
            }

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
