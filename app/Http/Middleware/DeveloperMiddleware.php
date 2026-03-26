<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // Use the Auth facade for clarity

class DeveloperMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        if (Auth::user()->isDeveloper()) {
            return $next($request);
        }
        abort(403, 'Unauthorized Access: You must have the developer role.');
    }
}