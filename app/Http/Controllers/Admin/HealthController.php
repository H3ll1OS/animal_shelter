<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HealthRecord; 
use App\Models\VaccineSchedule;
use App\Models\Pet;
use App\Models\EuthanasiaLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class HealthController extends Controller
{
    public function monitoring()
    {
        
        $alerts = HealthRecord::with('pet')
                                ->latest()
                                ->paginate(10);
        
        return Inertia::render('Admin/Health/Monitoring/Index', [
            'alerts' => $alerts,
        ]);
    }

    /**
     * Show form to log a new critical alert.
     */
    public function createMonitoring()
    {
        $pets = Pet::orderBy('name')->get(['id', 'name', 'status']);
        return Inertia::render('Admin/Health/Monitoring/Create', [
            'pets' => $pets,
        ]);
    }

    /**
     * Store a new critical health alert.
     */
    public function storeMonitoring(Request $request)
    {
        $validated = $request->validate([
            'pet_id' => 'required|exists:pets,id',
            'diagnosis' => 'required|string|max:255',
            'description' => 'required|string',
            'veterinarian' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'is_critical' => 'nullable|boolean',
        ]);

        HealthRecord::create([
            'pet_id' => $validated['pet_id'],
            'diagnosis' => $validated['diagnosis'],
            'description' => $validated['description'],
            'veterinarian' => $validated['veterinarian'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_critical' => (bool) ($validated['is_critical'] ?? false),
            'reported_date' => now(),
        ]);

        return redirect()->route('admin.health.monitoring')->with('success', 'Health alert added successfully.');
    }

    /**
     * Show a single alert.
     */
    public function showMonitoring(HealthRecord $alert)
    {
        $alert->load('pet');
        return Inertia::render('Admin/Health/Monitoring/Show', [
            'alert' => $alert,
        ]);
    }

    /**
     * Edit form for an alert.
     */
    public function editMonitoring(HealthRecord $alert)
    {
        $alert->load('pet');
        $pets = Pet::orderBy('name')->get(['id', 'name', 'status']);
        return Inertia::render('Admin/Health/Monitoring/Edit', [
            'alert' => $alert,
            'pets' => $pets,
        ]);
    }

    /**
     * Update an alert.
     */
    public function updateMonitoring(Request $request, HealthRecord $alert)
    {
        $validated = $request->validate([
            'pet_id' => 'required|exists:pets,id',
            'diagnosis' => 'required|string|max:255',
            'description' => 'required|string',
            'veterinarian' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'is_critical' => 'nullable|boolean',
        ]);

        $alert->update([
            'pet_id' => $validated['pet_id'],
            'diagnosis' => $validated['diagnosis'],
            'description' => $validated['description'],
            'veterinarian' => $validated['veterinarian'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_critical' => $validated['is_critical'] ?? false,
        ]);

        return redirect()->route('admin.health.monitoring.show', $alert)->with('success', 'Health updated.');
    }

    /**
     * Delete an alert.
     */
    public function destroyMonitoring(HealthRecord $alert)
    {
        $alert->delete();
        return redirect()->route('admin.health.monitoring')->with('success', 'Alert deleted.');
    }

    public function vaccinationSchedule()
    {
        
        $schedules = VaccineSchedule::where('next_due_date', '>=', now()->subDay())
                            ->where('is_completed', false)
                            ->with('pet')
                            ->latest('next_due_date') 
                            ->paginate(15);
        
        return Inertia::render('Admin/Health/Vaccination/Index', [
            'schedules' => $schedules,
        ]);
    }

    public function showVaccineSchedule(VaccineSchedule $schedule)
    {
        $schedule->load('pet');
        return Inertia::render('Admin/Health/Vaccination/Show', [
            'schedule' => $schedule,
        ]);
    }

    public function createVaccineSchedule()
    {
        $pets = Pet::where('status', '!=', 'Adopted')->get(['id', 'name']);

        return Inertia::render('Admin/Health/Vaccination/Create', [
            'pets' => $pets,
        ]);
    }

    public function markVaccineComplete(VaccineSchedule $schedule)
    {
            if ($schedule->is_completed) {
                return redirect()->route('admin.health.vaccination')
                                ->with('info', 'This vaccination was already marked complete.');
            }
            
            $schedule->update([
                'is_completed' => true,
                'last_given_date' => now(),
            ]);

            $message = 'Vaccination marked as complete.'; 

            if ($schedule->vaccine_type === 'Rabies' || $schedule->vaccine_type === 'Distemper') {
                
                $followUpIntervalDays = 365;
                $newDueDate = now()->addDays($followUpIntervalDays);
                
                VaccineSchedule::create([
                    'pet_id' => $schedule->pet_id,
                    'vaccine_type' => $schedule->vaccine_type,
                    'next_due_date' => $newDueDate,
                    'notes' => "Automatic annual follow-up scheduled after dose completed on " . now()->format('Y-m-d'), 
                    'is_completed' => false,
                ]);
                
                $message = 'Vaccination marked complete, and annual follow-up scheduled for ' . $newDueDate->format('Y-m-d') . '.';
            }

            return redirect()->route('admin.health.vaccination')->with('success', $message);
        }

        /**
        * Stores a new vaccination schedule record.
        */
        public function storeVaccineSchedule(Request $request)
        {
            // 1. Validation
            $request->validate([
                'pet_id' => 'required|exists:pets,id',
                'vaccine_type' => 'required|string|max:255',
                'next_due_date' => 'required|date|after_or_equal:today',
                'notes' => 'nullable|string',
                'last_given_date' => 'nullable|date|before_or_equal:today', 
            ]); 

            $data = $request->only([
            'pet_id', 
            'vaccine_type', 
            'next_due_date', 
            'notes',
            'last_given_date', 
        ]);
        
            if (empty($data['last_given_date'])) {
                unset($data['last_given_date']); 
            }
            
            $data['is_completed'] = false;

            // 2. Create the record
            VaccineSchedule::create($data);

            return redirect()->route('admin.health.vaccination')
                            ->with('success', 'New vaccination schedule added successfully.');
        }

        /**
         * Removes (soft deletes) a vaccination schedule record.
         */
    public function destroy(VaccineSchedule $schedule)
    {

        Log::info('Attempting to delete vaccine schedule ID: ' . $schedule->id); 

           
            $petName = $schedule->pet->name ?? 'Unknown Pet'; 

            $schedule->delete(); 

            return redirect()->route('admin.health.vaccination')
                            ->with('warning', 'Vaccination schedule for ' . $petName  . ' has been removed.');
        }

    public function euthanasiaLogs()
    {
    
        $logs = EuthanasiaLog::with('pet:id,name')->latest('euthanasia_date')->paginate(15);
        
        return Inertia::render('Admin/Health/Euthanasia/Index', [
            'logs' => $logs,
        ]);
    }

    public function createEuthanasia()
    {
        $pets = Pet::orderBy('name')->get(['id', 'name', 'status']);
        return Inertia::render('Admin/Health/Euthanasia/Create', [
            'pets' => $pets,
        ]);
    }

    public function storeEuthanasia(Request $request)
    {
        $validated = $request->validate([
            'pet_id' => 'required|exists:pets,id',
            'reason' => 'required|string|max:255',
            'euthanasia_date' => 'required|date',
            'veterinarian' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        EuthanasiaLog::create([
            'pet_id' => $validated['pet_id'],
            'reason' => $validated['reason'],
            'euthanasia_date' => $validated['euthanasia_date'],
            'veterinarian' => $validated['veterinarian'],
            'notes' => $validated['notes'] ?? null,
            'user_id' => auth()->id(),
        ]);

        // Ensure the pet is no longer available for adoption
        if ($pet = Pet::find($validated['pet_id'])) {
            $pet->update(['status' => 'Euthanized']);
        }

        return redirect()->route('admin.health.euthanasia')->with('success', 'Euthanasia record added.');
    }

    public function showEuthanasia(EuthanasiaLog $log)
    {
        $log->load('pet', 'user:id,name');
        return Inertia::render('Admin/Health/Euthanasia/Show', [
            'log' => $log,
        ]);
    }

    public function editEuthanasia(EuthanasiaLog $log)
    {
        $log->load('pet');
        $pets = Pet::orderBy('name')->get(['id', 'name', 'status']);
        return Inertia::render('Admin/Health/Euthanasia/Edit', [
            'log' => $log,
            'pets' => $pets,
        ]);
    }

    public function updateEuthanasia(Request $request, EuthanasiaLog $log)
    {
        $validated = $request->validate([
            'pet_id' => 'required|exists:pets,id',
            'reason' => 'required|string|max:255',
            'euthanasia_date' => 'required|date',
            'veterinarian' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $log->update([
            'pet_id' => $validated['pet_id'],
            'reason' => $validated['reason'],
            'euthanasia_date' => $validated['euthanasia_date'],
            'veterinarian' => $validated['veterinarian'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Keep pet marked as euthanized / unavailable
        if ($pet = Pet::find($validated['pet_id'])) {
            $pet->update(['status' => 'Euthanized']);
        }

        return redirect()->route('admin.health.euthanasia.show', $log)->with('success', 'Euthanasia record updated.');
    }

    public function destroyEuthanasia(EuthanasiaLog $log)
    {
        $log->delete();
        return redirect()->route('admin.health.euthanasia')->with('success', 'Euthanasia record deleted.');
    }
}
