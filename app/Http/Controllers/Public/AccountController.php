<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Adoption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function adoptions(Request $request)
    {
        Adoption::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['Approved', 'Rejected'])
            ->whereNull('decision_seen_at')
            ->update([
                'decision_seen_at' => now(),
            ]);

        $adoptions = $request->user()
            ->adoptions()
            ->with('pet:id,name,species,breed,image_path,status')
            ->latest()
            ->get()
            ->map(function ($adoption) {
                return [
                    'id' => $adoption->id,
                    'status' => $adoption->status,
                    'full_name' => $adoption->full_name,
                    'email' => $adoption->email,
                    'phone' => $adoption->phone,
                    'message' => $adoption->message,
                    'created_at' => optional($adoption->created_at)->toISOString(),
                    'updated_at' => optional($adoption->updated_at)->toISOString(),
                    'decision_seen_at' => optional($adoption->decision_seen_at)->toISOString(),
                    'pet' => $adoption->pet ? [
                        'id' => $adoption->pet->id,
                        'name' => $adoption->pet->name,
                        'species' => $adoption->pet->species,
                        'breed' => $adoption->pet->breed,
                        'image_path' => $adoption->pet->image_path,
                        'status' => $adoption->pet->status,
                    ] : null,
                ];
            })
            ->values();

        return Inertia::render('Frontend/MyAdoptions', [
            'adoptions' => $adoptions,
        ]);
    }

    public function edit()
    {
        return Inertia::render('Frontend/AccountSettings');
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:1000'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $validated['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }

        $user->fill($validated);
        $user->save();

        return redirect()
            ->route('account.settings')
            ->with('status', 'Profile updated successfully.');
    }

    public function editPassword()
    {
        return Inertia::render('Frontend/ChangePassword');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('account.password.edit')
            ->with('status', 'Password updated successfully.');
    }
}
