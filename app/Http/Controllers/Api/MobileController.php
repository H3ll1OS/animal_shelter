<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Adoption;
use App\Models\Donation;
use App\Models\EuthanasiaLog;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;
use App\Models\VaccineSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MobileController extends Controller
{
    public function bootstrap(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        $query = Pet::query()->with(['healthRecords', 'vaccineSchedules', 'euthanasiaLogs']);
        if (! $user || ! $user->isAdmin()) {
            $query->where('status', 'Available')->whereDoesntHave('euthanasiaLogs');
        }

        $pets = $query->orderBy('name')->get();

        $payload = [
            'user' => $user ? $this->userData($user) : null,
            'pets' => $pets->map(fn (Pet $pet) => $this->petData($pet))->values(),
            'adoptions' => collect(),
            'donations' => collect(),
            'health_records' => collect(),
            'vaccine_schedules' => collect(),
            'euthanasia_logs' => collect(),
            'users' => collect(),
        ];

        if ($user) {
            $adoptionsQuery = Adoption::with('pet');
            if (! $user->isAdmin()) {
                $adoptionsQuery->where('user_id', $user->id);
            }
            $payload['adoptions'] = $adoptionsQuery->latest()->get()->map(fn (Adoption $adoption) => $this->adoptionData($adoption))->values();
        }

        if ($user && $user->isAdmin()) {
            $payload['donations'] = Donation::with('user:id,name,email')->latest()->get()->map(fn (Donation $donation) => $this->donationData($donation))->values();
            $payload['health_records'] = HealthRecord::with('pet:id,name')->latest()->get()->map(fn (HealthRecord $record) => $this->healthData($record))->values();
            $payload['vaccine_schedules'] = VaccineSchedule::with('pet:id,name')->latest('next_due_date')->get()->map(fn (VaccineSchedule $schedule) => $this->vaccineData($schedule))->values();
            $payload['euthanasia_logs'] = EuthanasiaLog::with(['pet:id,name', 'user:id,name'])->latest('euthanasia_date')->get()->map(fn (EuthanasiaLog $log) => $this->euthanasiaData($log))->values();
        }

        if ($user && $user->isDeveloper()) {
            $payload['users'] = User::where('id', '!=', $user->id)->latest()->get()->map(fn (User $managedUser) => $this->userData($managedUser))->values();
        }

        return response()->json($payload);
    }

    public function updateAccount(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        abort_unless($user, 401, 'Unauthenticated.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
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

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $this->userData($user->fresh()),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        abort_unless($user, 401, 'Unauthenticated.');

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    public function storeAdoption(Request $request, Pet $pet): JsonResponse
    {
        $user = $this->resolveUser($request);
        abort_unless($user, 401, 'Unauthenticated.');

        if ($pet->status !== 'Available' || $pet->euthanasiaLogs()->exists()) {
            return response()->json([
                'message' => 'This pet is no longer available for adoption.',
            ], 422);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'message' => ['nullable', 'string'],
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['string', 'max:255'],
        ]);

        $adoption = Adoption::create([
            'pet_id' => $pet->id,
            'user_id' => $user->id,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'message' => $validated['message'] ?? '',
            'document_path' => json_encode(array_values($validated['documents'])),
            'status' => 'Pending',
        ]);

        return response()->json([
            'message' => 'Adoption application submitted successfully.',
            'adoption' => $this->adoptionData($adoption->load('pet')),
        ], 201);
    }

    public function storeDonation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', Rule::in(['gcash', 'paypal', 'bank_transfer', 'credit_card'])],
            'anonymous' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $anonymous = (bool) ($validated['anonymous'] ?? false);

        $donation = Donation::create([
            'user_id' => $request->user()?->id,
            'name' => $anonymous ? 'Anonymous' : ($validated['name'] ?? $request->user()?->name ?? 'Donor'),
            'last_name' => $anonymous ? 'Donor' : ($validated['last_name'] ?? ''),
            'email' => $anonymous ? 'anonymous@donor.local' : ($validated['email'] ?? $request->user()?->email ?? 'donor@example.com'),
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? null,
            'zip_code' => $validated['zip_code'] ?? null,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
            'anonymous' => $anonymous,
        ]);

        return response()->json([
            'message' => 'Donation recorded successfully.',
            'donation' => $this->donationData($donation),
        ], 201);
    }

    public function storePet(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $pet = Pet::create($this->validatePet($request));

        return response()->json([
            'message' => 'Pet created successfully.',
            'pet' => $this->petData($pet),
        ], 201);
    }

    public function updatePet(Request $request, Pet $pet): JsonResponse
    {
        $this->ensureAdmin($request);

        $pet->update($this->validatePet($request));

        return response()->json([
            'message' => 'Pet updated successfully.',
            'pet' => $this->petData($pet->fresh()),
        ]);
    }

    public function destroyPet(Request $request, Pet $pet): JsonResponse
    {
        $this->ensureAdmin($request);

        $pet->healthRecords()->delete();
        $pet->vaccineSchedules()->delete();
        $pet->adoptions()->delete();
        if ($pet->image_path && ! $this->isExternalUrl($pet->image_path)) {
            Storage::disk('public')->delete($pet->image_path);
        }
        $pet->delete();

        return response()->json([
            'message' => 'Pet deleted successfully.',
        ]);
    }

    public function approveAdoption(Request $request, Adoption $adoption): JsonResponse
    {
        $this->ensureAdmin($request);

        $adoption->update([
            'status' => 'Approved',
            'decision_seen_at' => null,
        ]);

        if ($adoption->pet) {
            $adoption->pet->update(['status' => 'Adopted']);
        }

        return response()->json([
            'message' => 'Application approved successfully.',
        ]);
    }

    public function rejectAdoption(Request $request, Adoption $adoption): JsonResponse
    {
        $this->ensureAdmin($request);

        $adoption->update([
            'status' => 'Rejected',
            'decision_seen_at' => null,
        ]);

        return response()->json([
            'message' => 'Application rejected successfully.',
        ]);
    }

    public function destroyDonation(Request $request, Donation $donation): JsonResponse
    {
        $this->ensureAdmin($request);

        $donation->delete();

        return response()->json([
            'message' => 'Donation deleted successfully.',
        ]);
    }

    public function storeHealthRecord(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'pet_id' => ['required', 'exists:pets,id'],
            'diagnosis' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'veterinarian' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_critical' => ['nullable', 'boolean'],
        ]);

        $record = HealthRecord::create([
            ...$validated,
            'reported_date' => now()->toDateString(),
            'is_critical' => (bool) ($validated['is_critical'] ?? false),
        ]);

        return response()->json([
            'message' => 'Health alert created successfully.',
            'health_record' => $this->healthData($record->load('pet')),
        ], 201);
    }

    public function updateHealthRecord(Request $request, HealthRecord $healthRecord): JsonResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'pet_id' => ['required', 'exists:pets,id'],
            'diagnosis' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'veterinarian' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_critical' => ['nullable', 'boolean'],
        ]);

        $healthRecord->update([
            ...$validated,
            'is_critical' => (bool) ($validated['is_critical'] ?? false),
        ]);

        return response()->json([
            'message' => 'Health alert updated successfully.',
            'health_record' => $this->healthData($healthRecord->fresh()->load('pet')),
        ]);
    }

    public function destroyHealthRecord(Request $request, HealthRecord $healthRecord): JsonResponse
    {
        $this->ensureAdmin($request);
        $healthRecord->delete();

        return response()->json([
            'message' => 'Health alert deleted successfully.',
        ]);
    }

    public function storeVaccineSchedule(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'pet_id' => ['required', 'exists:pets,id'],
            'vaccine_type' => ['required', 'string', 'max:255'],
            'next_due_date' => ['required', 'date'],
            'last_given_date' => ['nullable', 'date'],
            'veterinarian' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $schedule = VaccineSchedule::create([
            ...$validated,
            'is_completed' => false,
        ]);

        return response()->json([
            'message' => 'Vaccination schedule created successfully.',
            'vaccine_schedule' => $this->vaccineData($schedule->load('pet')),
        ], 201);
    }

    public function completeVaccineSchedule(Request $request, VaccineSchedule $vaccineSchedule): JsonResponse
    {
        $this->ensureAdmin($request);

        if (! $vaccineSchedule->is_completed) {
            $vaccineSchedule->update([
                'is_completed' => true,
                'last_given_date' => now()->toDateString(),
            ]);

            if (in_array($vaccineSchedule->vaccine_type, ['Rabies', 'Distemper'], true)) {
                VaccineSchedule::create([
                    'pet_id' => $vaccineSchedule->pet_id,
                    'vaccine_type' => $vaccineSchedule->vaccine_type,
                    'next_due_date' => now()->addYear()->toDateString(),
                    'notes' => 'Automatic annual follow-up scheduled after completion.',
                    'is_completed' => false,
                ]);
            }
        }

        return response()->json([
            'message' => 'Vaccination updated successfully.',
        ]);
    }

    public function destroyVaccineSchedule(Request $request, VaccineSchedule $vaccineSchedule): JsonResponse
    {
        $this->ensureAdmin($request);
        $vaccineSchedule->delete();

        return response()->json([
            'message' => 'Vaccination schedule deleted successfully.',
        ]);
    }

    public function storeEuthanasiaLog(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'pet_id' => ['required', 'exists:pets,id'],
            'reason' => ['required', 'string'],
            'euthanasia_date' => ['required', 'date'],
            'veterinarian' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $log = EuthanasiaLog::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        if ($pet = Pet::find($validated['pet_id'])) {
            $pet->update(['status' => 'Euthanized']);
        }

        return response()->json([
            'message' => 'Euthanasia log created successfully.',
            'euthanasia_log' => $this->euthanasiaData($log->load(['pet', 'user'])),
        ], 201);
    }

    public function updateEuthanasiaLog(Request $request, EuthanasiaLog $euthanasiaLog): JsonResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'pet_id' => ['required', 'exists:pets,id'],
            'reason' => ['required', 'string'],
            'euthanasia_date' => ['required', 'date'],
            'veterinarian' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $euthanasiaLog->update($validated);

        if ($pet = Pet::find($validated['pet_id'])) {
            $pet->update(['status' => 'Euthanized']);
        }

        return response()->json([
            'message' => 'Euthanasia log updated successfully.',
            'euthanasia_log' => $this->euthanasiaData($euthanasiaLog->fresh()->load(['pet', 'user'])),
        ]);
    }

    public function destroyEuthanasiaLog(Request $request, EuthanasiaLog $euthanasiaLog): JsonResponse
    {
        $this->ensureAdmin($request);
        $euthanasiaLog->delete();

        return response()->json([
            'message' => 'Euthanasia log deleted successfully.',
        ]);
    }

    public function updateUserRoleStatus(Request $request, User $user): JsonResponse
    {
        $this->ensureDeveloper($request);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot modify your own account.'], 422);
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'developer', 'user'])],
            'status' => ['required', Rule::in(['Active', 'Blocked'])],
        ]);

        if ($user->role === 'developer' && $validated['role'] !== 'developer' && User::where('role', 'developer')->count() <= 1) {
            return response()->json(['message' => 'Cannot remove the last developer account.'], 422);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $this->userData($user->fresh()),
        ]);
    }

    public function destroyUser(Request $request, User $user): JsonResponse
    {
        $this->ensureDeveloper($request);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        if ($user->role === 'developer' && User::where('role', 'developer')->count() <= 1) {
            return response()->json(['message' => 'Cannot delete the last developer account.'], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    /**
     * Resolve the current user from the session/guard, falling back to the
     * hashed api_token sent as a bearer token.
     */
    private function resolveUser(Request $request): ?User
    {
        $user = $request->user();

        if (! $user && $request->bearerToken()) {
            $user = User::where('api_token', hash('sha256', $request->bearerToken()))->first();
        }

        return $user;
    }

    private function isExternalUrl(?string $path): bool
    {
        return $path !== null && Str::startsWith($path, ['http://', 'https://']);
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Admin access required.');
    }

    private function ensureDeveloper(Request $request): void
    {
        abort_unless($request->user()?->isDeveloper(), 403, 'Developer access required.');
    }

    private function validatePet(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'species' => ['required', Rule::in(['Dog', 'Cat', 'Rabbit', 'Bird', 'Other'])],
            'breed' => ['nullable', 'string', 'max:255'],
            'age' => ['required', 'integer', 'min:0'],
            'gender' => ['required', Rule::in(['Male', 'Female', 'Unknown'])],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Available', 'Adopted', 'Pending', 'Sick', 'Fostered', 'Euthanized'])],
            'date_received' => ['required', 'date'],
            'size' => ['required', Rule::in(['small', 'medium', 'large', 'Small', 'Medium', 'Large'])],
            'image_path' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function userData(User $user): array
    {
        $profileImageVersion = optional($user->updated_at)->timestamp;
        $profileImageUrl = null;

        if ($user->profile_image) {
            $profileImageUrl = $this->isExternalUrl($user->profile_image)
                ? $user->profile_image
                : asset('storage/' . $user->profile_image) . ($profileImageVersion ? '?v=' . $profileImageVersion : '');
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'phone' => $user->phone,
            'date_of_birth' => optional($user->date_of_birth)->format('Y-m-d'),
            'address' => $user->address,
            'profile_image' => $user->profile_image,
            'profile_image_url' => $profileImageUrl,
            'profile_image_updated_at' => $profileImageVersion,
        ];
    }

    private function petData(Pet $pet): array
    {
        $imageVersion = optional($pet->updated_at)->timestamp;
        $imageUrl = null;

        if ($pet->image_path) {
            // Seeded pets store a full external URL; uploaded ones store a storage path.
            $imageUrl = $this->isExternalUrl($pet->image_path)
                ? $pet->image_path
                : asset('storage/' . $pet->image_path) . ($imageVersion ? '?v=' . $imageVersion : '');
        }

        return [
            'id' => $pet->id,
            'name' => $pet->name,
            'species' => $pet->species,
            'breed' => $pet->breed,
            'age' => $pet->age,
            'gender' => $pet->gender,
            'description' => $pet->description,
            'image_label' => $pet->image_path ?: $pet->name,
            'image_path' => $pet->image_path,
            'image_url' => $imageUrl,
            'image_updated_at' => $imageVersion,
            'status' => $pet->status,
            'date_received' => optional($pet->date_received)->format('Y-m-d'),
            'size' => ucfirst((string) $pet->size),
        ];
    }

    private function adoptionData(Adoption $adoption): array
    {
        return [
            'id' => $adoption->id,
            'pet_id' => $adoption->pet_id,
            'pet_name' => $adoption->pet?->name,
            'user_id' => $adoption->user_id,
            'full_name' => $adoption->full_name,
            'email' => $adoption->email,
            'phone' => $adoption->phone,
            'message' => $adoption->message,
            'documents' => json_decode((string) $adoption->document_path, true) ?: [],
            'status' => $adoption->status,
            'created_at' => optional($adoption->created_at)->toISOString(),
            'updated_at' => optional($adoption->updated_at)->toISOString(),
            'decision_seen_at' => optional($adoption->decision_seen_at)->toISOString(),
            'admin_seen_at' => optional($adoption->admin_seen_at)->toISOString(),
        ];
    }

    private function donationData(Donation $donation): array
    {
        return [
            'id' => $donation->id,
            'user_id' => $donation->user_id,
            'name' => $donation->name,
            'last_name' => $donation->last_name,
            'email' => $donation->email,
            'state' => $donation->state,
            'country' => $donation->country,
            'zip_code' => $donation->zip_code,
            'amount' => (float) $donation->amount,
            'payment_method' => $donation->payment_method,
            'notes' => $donation->notes,
            'anonymous' => (bool) $donation->anonymous,
            'created_at' => optional($donation->created_at)->toISOString(),
            'admin_seen_at' => optional($donation->admin_seen_at)->toISOString(),
        ];
    }

    private function healthData(HealthRecord $record): array
    {
        return [
            'id' => $record->id,
            'pet_id' => $record->pet_id,
            'diagnosis' => $record->diagnosis,
            'description' => $record->description,
            'veterinarian' => $record->veterinarian,
            'notes' => $record->notes,
            'is_critical' => (bool) $record->is_critical,
            'reported_date' => optional($record->reported_date)->format('Y-m-d'),
        ];
    }

    private function vaccineData(VaccineSchedule $schedule): array
    {
        return [
            'id' => $schedule->id,
            'pet_id' => $schedule->pet_id,
            'vaccine_type' => $schedule->vaccine_type,
            'vaccination_date' => optional($schedule->vaccination_date)->format('Y-m-d'),
            'last_given_date' => optional($schedule->last_given_date)->format('Y-m-d'),
            'next_due_date' => optional($schedule->next_due_date)->format('Y-m-d'),
            'veterinarian' => $schedule->veterinarian,
            'notes' => $schedule->notes,
            'is_completed' => (bool) $schedule->is_completed,
        ];
    }

    private function euthanasiaData(EuthanasiaLog $log): array
    {
        return [
            'id' => $log->id,
            'pet_id' => $log->pet_id,
            'reason' => $log->reason,
            'euthanasia_date' => optional($log->euthanasia_date)->format('Y-m-d'),
            'user_id' => $log->user_id,
            'veterinarian' => $log->veterinarian,
            'notes' => $log->notes,
        ];
    }
}