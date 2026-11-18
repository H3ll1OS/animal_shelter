<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPermissionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $permission = $this->permissionForRoute($request);

        abort_unless($user?->isAdmin() && $user->isActive(), 403, 'Administrator access required.');
        abort_unless($permission && $user->hasPermission($permission), 403, 'You do not have permission to perform this action.');

        return $next($request);
    }

    private function permissionForRoute(Request $request): ?string
    {
        $name = (string) $request->route()?->getName();
        $method = $request->method();
        $module = match (true) {
            $name === 'admin.index', $name === 'admin.notifications.clear' => 'dashboard',
            str_starts_with($name, 'admin.reports.') => 'reports',
            str_starts_with($name, 'admin.pets.') => 'pets',
            str_starts_with($name, 'admin.adoptions.') => 'adoptions',
            str_starts_with($name, 'admin.post-adoption.') => 'post_adoption',
            str_starts_with($name, 'admin.health.monitoring') => 'health',
            str_starts_with($name, 'admin.health.vaccin') => 'vaccinations',
            str_starts_with($name, 'admin.health.euthanasia') => 'euthanasia',
            str_starts_with($name, 'admin.donations.') => 'donations',
            default => null,
        };

        if ($module === null) {
            return null;
        }

        $action = match (true) {
            in_array($name, ['admin.index', 'admin.notifications.clear'], true) => 'view',
            in_array($name, ['admin.donations.verify', 'admin.donations.reject'], true) => 'edit',
            str_contains($name, 'approve'), str_contains($name, 'reject') => 'approve',
            in_array($method, ['GET', 'HEAD'], true) && str_ends_with($name, '.create') => 'create',
            in_array($method, ['GET', 'HEAD'], true) && str_ends_with($name, '.edit') => 'edit',
            in_array($method, ['GET', 'HEAD'], true) && str_ends_with($name, '_create') => 'create',
            in_array($method, ['GET', 'HEAD'], true) => 'view',
            $method === 'POST' && (str_ends_with($name, '.store') || str_ends_with($name, '_store')) => 'create',
            $method === 'DELETE' => 'delete',
            default => 'edit',
        };

        if ($module === 'post_adoption' && in_array($name, ['admin.post-adoption.complete', 'admin.post-adoption.cancel'], true)) {
            $action = 'edit';
        }

        return $module.'.'.$action;
    }
}
