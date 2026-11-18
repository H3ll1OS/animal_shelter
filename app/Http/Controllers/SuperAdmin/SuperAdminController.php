<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('SuperAdmin/Dashboard', [
            'stats' => [
                'accounts' => User::count(),
                'administrators' => User::where('role', 'admin')->count(),
                'registered_users' => User::where('role', 'user')->count(),
                'active' => User::where('status', 'Active')->count(),
                'inactive' => User::where('status', '!=', 'Active')->count(),
            ],
            'roleCounts' => Role::withCount(['accounts'])->orderBy('id')->get(['id', 'key', 'label']),
        ]);
    }

    public function accounts(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', Rule::in(['superadmin', 'admin', 'user'])],
            'status' => ['nullable', Rule::in(['Active', 'Blocked'])],
        ]);

        $users = User::query()
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query
                ->where(fn (Builder $query) => $query
                    ->where('name', 'like', '%' . addcslashes($search, '%_\\') . '%')
                    ->orWhere('email', 'like', '%' . addcslashes($search, '%_\\') . '%')))
            ->when($filters['role'] ?? null, fn (Builder $query, string $role) => $query->where('role', $role))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('SuperAdmin/Accounts/Index', [
            'users' => $users,
            'roles' => Role::orderBy('id')->get(['key', 'label']),
            'filters' => $filters,
            'currentUserId' => $request->user()->id,
        ]);
    }

    public function createAccount(): Response
    {
        return Inertia::render('SuperAdmin/Accounts/Form', [
            'user' => null,
            'roles' => Role::orderBy('id')->get(['key', 'label']),
        ]);
    }

    public function storeAccount(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['superadmin', 'admin', 'user'])],
            'status' => ['required', Rule::in(['Active', 'Blocked'])],
        ]);

        $user = DB::transaction(function () use ($validated, $logger) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'status' => $validated['status'],
            ]);
            $logger->record('account_created', 'users', $user, [
                'role' => $user->role,
                'status' => $user->status,
            ]);

            return $user;
        });

        return redirect()->route('superadmin.accounts.show', $user)->with('success', 'Account created.');
    }

    public function showAccount(User $user): Response
    {
        return Inertia::render('SuperAdmin/Accounts/Show', [
            'user' => $user,
            'currentUserId' => request()->user()->id,
        ]);
    }

    public function editAccount(User $user): Response
    {
        return Inertia::render('SuperAdmin/Accounts/Form', [
            'user' => $user,
            'roles' => Role::orderBy('id')->get(['key', 'label']),
        ]);
    }

    public function updateAccount(Request $request, User $user, ActivityLogger $logger): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 422, 'You cannot edit your own account here.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['superadmin', 'admin', 'user'])],
            'status' => ['required', Rule::in(['Active', 'Blocked'])],
        ]);

        DB::transaction(function () use ($user, $validated, $logger) {
            $before = ['role' => $user->role, 'status' => $user->status];
            $this->protectLastActiveSuperAdmin($user, $validated['role'], $validated['status']);
            $changes = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'status' => $validated['status'],
            ];
            if ($validated['password'] ?? null) {
                $changes['password'] = Hash::make($validated['password']);
            }
            $user->update($changes);

            $logger->record('account_updated', 'users', $user, [
                'role_before' => $before['role'],
                'role_after' => $user->role,
                'status_before' => $before['status'],
                'status_after' => $user->status,
                'profile_fields_updated' => ['name', 'email'],
                'password_changed' => (bool) ($validated['password'] ?? null),
            ]);

            if ($before['role'] !== $user->role) {
                $logger->record('role_changed', 'users', $user, [
                    'from' => $before['role'],
                    'to' => $user->role,
                ]);
            }
            if ($before['status'] !== $user->status) {
                $logger->record($user->isActive() ? 'account_activated' : 'account_deactivated', 'users', $user, [
                    'from' => $before['status'],
                    'to' => $user->status,
                ]);
            }
        });

        return redirect()->route('superadmin.accounts.show', $user)->with('success', 'Account updated.');
    }

    public function destroyAccount(User $user, ActivityLogger $logger): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 422, 'You cannot delete your own account.');

        DB::transaction(function () use ($user, $logger) {
            $this->protectLastActiveSuperAdmin($user, null, null, true);
            $logger->record('account_deleted', 'users', $user, [
                'role' => $user->role,
                'status' => $user->status,
            ]);
            $user->delete();
        });

        return redirect()->route('superadmin.accounts.index')->with('success', 'Account deleted.');
    }

    public function accessManagement(): Response
    {
        return Inertia::render('SuperAdmin/AccessManagement/Index', [
            'roles' => Role::withCount('accounts')
                ->with('permissions:id,module,action,label,description')
                ->orderBy('id')
                ->get(),
            'permissions' => Permission::orderBy('module')->orderBy('id')->get()->groupBy('module'),
        ]);
    }

    public function updateRoleAccess(Request $request, Role $role, ActivityLogger $logger): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:1000'],
            'permission_ids' => ['present', 'array'],
            'permission_ids.*' => ['integer', 'distinct', 'exists:permissions,id'],
        ]);

        $permissionIds = collect($validated['permission_ids'])->map(fn ($id) => (int) $id);
        if ($role->key === 'superadmin') {
            $permissionIds = Permission::pluck('id');
        }

        DB::transaction(function () use ($role, $validated, $permissionIds, $logger) {
            $before = $role->permissions()->pluck('permissions.id')->sort()->values()->all();
            $descriptionBefore = $role->description;

            $role->update(['description' => $validated['description']]);
            $role->permissions()->sync($permissionIds->unique()->values()->all());
            $logger->record('role_access_updated', 'permissions', null, [
                'role' => $role->key,
                'description_changed' => $descriptionBefore !== $role->description,
                'permissions_before' => $before,
                'permissions_after' => $permissionIds->unique()->sort()->values()->all(),
            ]);
        });

        return back()->with('success', 'Role access updated.');
    }

    public function activityLogs(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'action' => ['nullable', 'string', 'max:80'],
            'actor' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $search = isset($filters['search']) ? addcslashes($filters['search'], '%_\\') : null;
        $logs = ActivityLog::query()
            ->when($search, fn (Builder $query, string $value) => $query->where(fn (Builder $query) => $query
                ->where('actor_name', 'like', '%' . $value . '%')
                ->orWhere('actor_email', 'like', '%' . $value . '%')
                ->orWhere('subject_name', 'like', '%' . $value . '%')
                ->orWhere('subject_email', 'like', '%' . $value . '%')))
            ->when($filters['action'] ?? null, fn (Builder $query, string $action) => $query->where('action', $action))
            ->when($filters['actor'] ?? null, fn (Builder $query, int $actor) => $query->where('actor_id', $actor))
            ->when($filters['from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('SuperAdmin/ActivityLogs/Index', [
            'logs' => $logs,
            'filters' => $filters,
            'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
            'administrators' => User::whereIn('role', ['admin', 'superadmin'])->orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function protectLastActiveSuperAdmin(
        User $user,
        ?string $newRole,
        ?string $newStatus,
        bool $deleting = false,
    ): void {
        if (! $user->isSuperAdmin() || $user->status !== 'Active') {
            return;
        }
        if (! $deleting && $newRole === 'superadmin' && $newStatus === 'Active') {
            return;
        }

        $activeSuperAdmins = User::where('role', 'superadmin')
            ->where('status', 'Active')
            ->lockForUpdate()
            ->get(['id'])
            ->count();
        abort_if($activeSuperAdmins <= 1, 422, 'The last active Super Admin cannot be demoted, deactivated, or deleted.');
    }
}
