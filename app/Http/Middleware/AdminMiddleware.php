<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {

        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        if ($user->isAdmin() || $user->isDeveloper()) {
            return $next($request);
        }
        return abort(403, 'Unauthorized access. Only Admin or Developer accounts can access this area.');
    }
}