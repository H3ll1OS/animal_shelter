<?php

namespace App\Http\Middleware;

use App\Models\Adoption;
use App\Models\Donation;
use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $adminPermissions = $user?->isAdmin()
            ? Permission::query()
                ->whereHas('roles', fn ($query) => $query->where('key', $user->role))
                ->get(['module', 'action'])
                ->map(fn (Permission $permission) => $permission->module . '.' . $permission->action)
                ->values()
                ->all()
            : [];

        $adminNotifications = [];
        $adminNotificationCount = 0;

        if ($request->user()?->isAdmin()) {
            $adoptionNotifications = Adoption::query()
                ->with('pet:id,name')
                ->whereNull('admin_seen_at')
                ->latest()
                ->take(8)
                ->get()
                ->map(function (Adoption $adoption) {
                    return [
                        'id' => 'adoption-' . $adoption->id,
                        'kind' => 'adoption',
                        'message' => 'New adoption request for ' . ($adoption->pet?->name ?: 'a pet') . '.',
                        'time' => optional($adoption->created_at)->toISOString(),
                        'time_label' => optional($adoption->created_at)?->diffForHumans(),
                        'href' => route('admin.adoptions.show', $adoption),
                    ];
                });

            $donationNotifications = Donation::query()
                ->whereNull('admin_seen_at')
                ->latest()
                ->take(8)
                ->get()
                ->map(function (Donation $donation) {
                    $donorName = $donation->anonymous
                        ? 'Anonymous donor'
                        : trim(($donation->name ?? '') . ' ' . ($donation->last_name ?? ''));

                    return [
                        'id' => 'donation-' . $donation->id,
                        'kind' => 'donation',
                        'message' => $donorName . ' submitted a donation of PHP ' . number_format((float) $donation->amount, 2) . '.',
                        'time' => optional($donation->created_at)->toISOString(),
                        'time_label' => optional($donation->created_at)?->diffForHumans(),
                        'href' => route('admin.donations.show', $donation),
                    ];
                });

            $adminNotifications = $adoptionNotifications
                ->concat($donationNotifications)
                ->sortByDesc('time')
                ->take(10)
                ->values()
                ->all();

            $adminNotificationCount = Adoption::query()
                ->whereNull('admin_seen_at')
                ->count() + Donation::query()
                ->whereNull('admin_seen_at')
                ->count();
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user
                    ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'permissions' => $adminPermissions,
                        'phone' => $user->phone,
                        'date_of_birth' => $user->date_of_birth,
                        'address' => $user->address,
                        'profile_image' => $user->profile_image,
                        'updated_at' => optional($user->updated_at)->toISOString(),
                    ]
                    : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'notifications' => [
                'adoption_decisions_count' => fn () => $request->user()
                    ? Adoption::query()
                        ->where('user_id', $request->user()->id)
                        ->whereIn('status', ['Approved', 'Rejected'])
                        ->whereNull('decision_seen_at')
                        ->count()
                    : 0,
                'admin_items' => fn () => $adminNotifications,
                'admin_count' => fn () => $adminNotificationCount,
            ],
        ]);
    }
}
