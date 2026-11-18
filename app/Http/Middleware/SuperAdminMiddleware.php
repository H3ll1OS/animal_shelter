<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        abort_unless(Auth::user()->isSuperAdmin() && Auth::user()->isActive(), 403, 'Active Super Admin access required.');

        return $next($request);
    }
}
