<?php

namespace App\Support;

use App\Models\EuthanasiaLog;
use App\Models\Pet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EuthanasiaRecorder
{
    public function record(
        array $petIds,
        string $reason,
        string $euthanasiaDate,
        string $veterinarian,
        ?string $notes,
        int $userId,
    ): Collection {
        $petIds = array_map('intval', $petIds);

        return DB::transaction(function () use ($petIds, $reason, $euthanasiaDate, $veterinarian, $notes, $userId) {
            $pets = Pet::query()
                ->whereIn('id', $petIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($pets->count() !== count($petIds)) {
                throw ValidationException::withMessages([
                    'pet_ids' => 'One or more selected pets no longer exist. Refresh the list and try again.',
                ]);
            }

            $petsWithLogs = EuthanasiaLog::query()
                ->whereIn('pet_id', $petIds)
                ->pluck('pet_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $ineligiblePets = $pets->filter(
                fn (Pet $pet) => $pet->status === 'Euthanized' || in_array($pet->id, $petsWithLogs, true)
            );

            if ($ineligiblePets->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'pet_ids' => 'These pets are no longer eligible: ' . $ineligiblePets->pluck('name')->implode(', ') . '. Refresh the list and try again.',
                ]);
            }

            return $pets->map(function (Pet $pet) use ($reason, $euthanasiaDate, $veterinarian, $notes, $userId) {
                $log = $pet->euthanasiaLogs()->create([
                    'reason' => $reason,
                    'euthanasia_date' => $euthanasiaDate,
                    'veterinarian' => $veterinarian,
                    'notes' => $notes,
                    'user_id' => $userId,
                ]);

                $pet->update(['status' => 'Euthanized']);

                return $log;
            });
        });
    }

    public function update(
        EuthanasiaLog $log,
        int $petId,
        string $reason,
        string $euthanasiaDate,
        string $veterinarian,
        ?string $notes,
    ): EuthanasiaLog {
        return DB::transaction(function () use ($log, $petId, $reason, $euthanasiaDate, $veterinarian, $notes) {
            $petIds = array_values(array_unique([$log->pet_id, $petId]));
            sort($petIds);
            $pets = Pet::query()->whereIn('id', $petIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $pet = $pets->get($petId);

            if (! $pet) {
                throw ValidationException::withMessages(['pet_id' => 'The selected pet no longer exists.']);
            }

            if ($petId !== (int) $log->pet_id) {
                $alreadyRecorded = EuthanasiaLog::query()->where('pet_id', $petId)->exists();
                if ($pet->status === 'Euthanized' || $alreadyRecorded) {
                    throw ValidationException::withMessages(['pet_id' => 'This pet already has a euthanasia record or is no longer eligible.']);
                }
            }

            $log->update([
                'pet_id' => $petId,
                'reason' => $reason,
                'euthanasia_date' => $euthanasiaDate,
                'veterinarian' => $veterinarian,
                'notes' => $notes,
            ]);
            $pet->update(['status' => 'Euthanized']);

            return $log->refresh();
        });
    }
}
