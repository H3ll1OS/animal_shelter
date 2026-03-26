<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Needed for validation rules
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the users, excluding the currently logged-in user.
     */
    public function index()
    {
        $users = User::where('id', '!=', auth()->id())
                     ->latest()
                     ->paginate(20); 
        
        return Inertia::render('Developer/Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Update the role and status of the specified user.
     */
    public function updateRole(Request $request, User $user)
    {
        // 1. Validate the incoming data
        $request->validate([
            'role' => ['required', Rule::in(['admin', 'developer', 'user'])],
            'status' => ['required', Rule::in(['Active', 'Blocked'])],
        ]);

        // 2. Prevent developers from modifying themselves 
        if ($user->id === auth()->id()) {
             return redirect()->route('developer.users.index')
                              ->with('error', "You cannot modify your own account settings from this panel.");
        }

        // 3. Update the user record
        $user->update([
            'role' => $request->role,
            'status' => $request->status,
        ]);

        return redirect()->route('developer.users.index')
                         ->with('success', "User {$user->name} updated successfully (Role: {$user->role}, Status: {$user->status}).");
    }
    
    /**
     * Removes (soft deletes) the specified user from storage.
     */
    public function destroy(User $user)
    {
        // 1. Safety check: Prevent deleting yourself
        if ($user->id === auth()->id()) {
             return redirect()->route('developer.users.index')
                              ->with('error', "You cannot delete your own account.");
        }
        
        // 2. Safety check: Prevent deleting the last developer (CRITICAL)
        if ($user->role === 'developer' && User::where('role', 'developer')->count() <= 1) {
             return redirect()->route('developer.users.index')
                              ->with('error', "Cannot delete the last remaining developer account.");
        }
        
        // 3. Perform the soft delete (requires SoftDeletes trait on the User model)
        $user->delete();

        return redirect()->route('developer.users.index')
                         ->with('warning', "User {$user->name} has been removed.");
    }
}
