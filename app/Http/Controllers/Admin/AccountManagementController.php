<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AccountManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['Active', 'Blocked'])],
        ]);
        $users = User::where('role', 'user')
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query
                ->where(fn (Builder $query) => $query
                    ->where('name', 'like', '%' . addcslashes($search, '%_\\') . '%')
                    ->orWhere('email', 'like', '%' . addcslashes($search, '%_\\') . '%')))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $filters,
        ]);
    }

    public function updateStatus(Request $request, User $user, ActivityLogger $logger): RedirectResponse
    {
        abort_unless($user->role === 'user', 404);
        $validated = $request->validate([
            'status' => ['required', Rule::in(['Active', 'Blocked'])],
        ]);
        $previousStatus = $user->status;
        $user->update($validated);

        if ($previousStatus !== $user->status) {
            $logger->record($user->isActive() ? 'account_activated' : 'account_deactivated', 'users', $user, [
                'from' => $previousStatus,
                'to' => $user->status,
                'performed_by_role' => 'admin',
            ]);
        }

        return back()->with('success', 'Registered user status updated.');
    }
}
