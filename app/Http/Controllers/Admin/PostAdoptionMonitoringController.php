<?php

namespace App\Http\Controllers\Admin;

use App\Services\Sms\SmsMessageTemplates;
use App\Services\Sms\SmsNotificationService;

use App\Http\Controllers\Controller;
use App\Models\Adoption;
use App\Models\PostAdoptionVisit;
use App\Models\PostAdoptionVisitAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PostAdoptionMonitoringController extends Controller
{
    private function baseQuery()
    {
        return PostAdoptionVisit::with(['pet:id,name,image_path,species,breed', 'adoption:id,full_name,email,phone,user_id', 'adopter:id,name,email,phone', 'assignedUser:id,name', 'attachments:id,post_adoption_visit_id,original_filename']);
    }

    private function eligibleAdoptions()
    {
        return Adoption::with(['pet:id,name,image_path,species,breed,status', 'user:id,name,email,phone'])
            ->where('status', 'Approved')->whereHas('pet', fn ($q) => $q->where('status', 'Adopted'));
    }

    public function dashboard(Request $request)
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from']);
        $completed = fn () => PostAdoptionVisit::where('schedule_status', 'Completed')
            ->when($request->filled('from'), fn ($q) => $q->whereDate('actual_visit_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('actual_visit_date', '<=', $request->input('to')));
        $scheduled = fn () => PostAdoptionVisit::query()
            ->when($request->filled('from'), fn ($q) => $q->whereDate('scheduled_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('scheduled_date', '<=', $request->input('to')));
        return Inertia::render('Admin/PostAdoption/Dashboard', [
            'stats' => [
                'adoptedPets' => $this->eligibleAdoptions()->distinct('pet_id')->count('pet_id'),
                'monitoredPets' => $completed()->distinct('pet_id')->count('pet_id'),
                'pending' => $scheduled()->where('schedule_status', 'Pending')->count(),
                'upcoming' => $scheduled()->where('schedule_status', 'Pending')->whereDate('scheduled_date', '>=', today())->count(),
                'overdue' => $scheduled()->where('schedule_status', 'Pending')->whereDate('scheduled_date', '<', today())->count(),
                'attention' => $completed()->where(fn ($q) => $q->whereIn('health_condition', ['Needs Medical Attention', 'Critical Condition'])->orWhereIn('pet_status', ['Missing', 'Deceased'])->orWhere('ownership_status', 'Returned to Shelter'))->distinct('pet_id')->count('pet_id'),
                'alive' => $completed()->where('pet_status', 'Alive')->distinct('pet_id')->count('pet_id'),
                'deceased' => $completed()->where('pet_status', 'Deceased')->distinct('pet_id')->count('pet_id'),
                'missing' => $completed()->where('pet_status', 'Missing')->distinct('pet_id')->count('pet_id'),
                'medicalAttention' => $completed()->whereIn('health_condition', ['Needs Medical Attention', 'Critical Condition'])->distinct('pet_id')->count('pet_id'),
                'ownershipChanged' => $completed()->whereIn('ownership_status', ['Rehomed / Transferred', 'Returned to Shelter'])->distinct('pet_id')->count('pet_id'),
                'completed' => $completed()->count(),
            ],
            'upcomingVisits' => $this->baseQuery()->where('schedule_status', 'Pending')->orderBy('scheduled_date')->take(8)->get(),
            'recentVisits' => $completed()
                ->with(['pet:id,name', 'adoption:id,full_name', 'assignedUser:id,name'])
                ->orderByDesc('actual_visit_date')
                ->take(5)
                ->get(),
            'filters' => $request->only('from', 'to'),
        ]);
    }

    public function index(Request $request)
    {
        $query = $this->baseQuery();
        if ($request->filled('status')) {
            if ($request->status === 'Overdue') $query->where('schedule_status', 'Pending')->whereDate('scheduled_date', '<', today());
            elseif ($request->status === 'Upcoming') $query->where('schedule_status', 'Pending')->whereDate('scheduled_date', '>=', today());
            else $query->where('schedule_status', $request->status);
        }
        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(fn ($q) => $q->whereHas('pet', fn ($p) => $p->where('name', 'like', "%{$term}%"))->orWhereHas('adoption', fn ($a) => $a->where('full_name', 'like', "%{$term}%")));
        }
        return Inertia::render('Admin/PostAdoption/Schedules', [
            'visits' => $query->orderByDesc('created_at')->orderByDesc('id')->paginate(15)->withQueryString(),
            'filters' => $request->only('status', 'search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/PostAdoption/Form', ['visit' => null, 'adoptions' => $this->eligibleAdoptions()->get(), 'staff' => $this->staff()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateSchedule($request);
        $adoption = $this->eligibleAdoptions()->findOrFail($data['adoption_id']);
        if (PostAdoptionVisit::where('pet_id', $adoption->pet_id)->whereDate('scheduled_date', $data['scheduled_date'])->whereIn('schedule_status', ['Pending', 'Completed'])->exists()) {
            return back()->withErrors(['scheduled_date' => 'A monitoring activity already exists for this pet on that date.'])->withInput();
        }
        $data += ['pet_id' => $adoption->pet_id, 'adopter_id' => $adoption->user_id, 'created_by' => $request->user()->id, 'schedule_status' => 'Pending'];
        $visit = PostAdoptionVisit::create($data);
        $visit->load('adopter', 'adoption.pet');
        $adopter = $visit->adopter;
        app(SmsNotificationService::class)->queue(
            'monitoring:' . $visit->id . ':created',
            'monitoring.scheduled',
            SmsMessageTemplates::monitoringScheduled($visit),
            $adopter,
            $adopter?->phone,
            $visit,
            $adopter?->sms_opted_in_at
        );
        return redirect()->route('admin.post-adoption.schedules')->with('success', 'Follow-up scheduled.');
    }

    public function edit(PostAdoptionVisit $visit)
    {
        abort_unless($visit->schedule_status === 'Pending', 403);
        return Inertia::render('Admin/PostAdoption/Form', ['visit' => $visit->load('adoption.pet', 'adoption.user'), 'adoptions' => $this->eligibleAdoptions()->get(), 'staff' => $this->staff()]);
    }

    public function update(Request $request, PostAdoptionVisit $visit)
    {
        abort_unless($visit->schedule_status === 'Pending', 403);
        $data = $this->validateSchedule($request);
        $adoption = $this->eligibleAdoptions()->findOrFail($data['adoption_id']);
        if (PostAdoptionVisit::where('pet_id', $adoption->pet_id)->whereDate('scheduled_date', $data['scheduled_date'])->where('id', '!=', $visit->id)->whereIn('schedule_status', ['Pending', 'Completed'])->exists()) {
            return back()->withErrors(['scheduled_date' => 'A monitoring activity already exists for this pet on that date.'])->withInput();
        }
        $visit->update($data + ['pet_id' => $adoption->pet_id, 'adopter_id' => $adoption->user_id]);
        $visit->load('adopter', 'adoption.pet');
        $adopter = $visit->adopter;
        app(SmsNotificationService::class)->queue(
            'monitoring:' . $visit->id . ':updated:' . $visit->updated_at?->format('YmdHis'),
            'monitoring.updated',
            SmsMessageTemplates::monitoringScheduled($visit, true),
            $adopter,
            $adopter?->phone,
            $visit,
            $adopter?->sms_opted_in_at
        );
        return redirect()->route('admin.post-adoption.schedules')->with('success', 'Schedule updated.');
    }

    public function show(PostAdoptionVisit $visit)
    {
        return Inertia::render('Admin/PostAdoption/Show', ['visit' => $visit->load(['pet', 'adoption', 'assignedUser', 'completer', 'attachments'])]);
    }

    public function complete(Request $request, PostAdoptionVisit $visit)
    {
        abort_unless($visit->schedule_status === 'Pending', 422);
        $data = $request->validate([
            'actual_visit_date' => 'required|date|before_or_equal:today',
            'monitoring_method' => ['required', Rule::in(['Home Visit', 'Phone Call', 'Video Call', 'Other'])],
            'assigned_user_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', ['admin', 'superadmin'])->where('status', 'Active'))],
            'pet_status' => ['required', Rule::in(['Alive', 'Deceased', 'Missing', 'Unknown / Unable to Verify'])],
            'health_condition' => ['required', Rule::in(['Healthy', 'Needs Medical Attention', 'Critical Condition', 'Not Assessed'])],
            'living_condition' => ['required', Rule::in(['Good', 'Acceptable', 'Poor', 'Not Assessed'])],
            'ownership_status' => ['required', Rule::in(['With Original Adopter', 'Rehomed / Transferred', 'Returned to Shelter', 'Cannot Be Verified'])],
            'findings' => 'required|string|max:10000', 'recommendations' => 'nullable|string|max:10000', 'notes' => 'nullable|string|max:10000',
            'next_follow_up_date' => 'nullable|date|after:actual_visit_date',
            'evidence' => 'nullable|array|max:10', 'evidence.*' => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);
        if (in_array($data['pet_status'], ['Deceased', 'Missing']) || $data['ownership_status'] === 'Returned to Shelter') $data['next_follow_up_date'] = null;
        if ($data['next_follow_up_date'] && PostAdoptionVisit::where('pet_id', $visit->pet_id)->whereDate('scheduled_date', $data['next_follow_up_date'])->whereIn('schedule_status', ['Pending', 'Completed'])->exists()) {
            return back()->withErrors(['next_follow_up_date' => 'A monitoring activity already exists for the pet on that date.']);
        }

        $files = $request->file('evidence', []);
        unset($data['evidence']);
        DB::transaction(function () use ($visit, $data, $files, $request) {
            $visit->update($data + ['schedule_status' => 'Completed', 'completed_by' => $request->user()->id]);
            foreach ($files as $file) {
                $path = $file->store('post-adoption-evidence', 'local');
                $visit->attachments()->create(['file_path' => $path, 'original_filename' => $file->getClientOriginalName()]);
            }
            if ($visit->next_follow_up_date) {
                PostAdoptionVisit::create([
                    'adoption_id' => $visit->adoption_id, 'pet_id' => $visit->pet_id, 'adopter_id' => $visit->adopter_id,
                    'assigned_user_id' => $visit->assigned_user_id, 'created_by' => $request->user()->id,
                    'scheduled_date' => $visit->next_follow_up_date, 'monitoring_method' => $visit->monitoring_method,
                    'purpose' => 'Follow-up after monitoring visit #' . $visit->id, 'schedule_status' => 'Pending',
                ]);
            }
        });
        return redirect()->route('admin.post-adoption.show', $visit)->with('success', 'Monitoring record completed.');
    }

    public function cancel(PostAdoptionVisit $visit)
    {
        abort_unless($visit->schedule_status === 'Pending', 422);
        $visit->update(['schedule_status' => 'Cancelled']);
        return back()->with('success', 'Schedule cancelled.');
    }

    public function history(Request $request)
    {
        $query = $this->baseQuery()->where('schedule_status', 'Completed');
        if ($request->filled('pet')) $query->where('pet_id', $request->integer('pet'));
        if ($request->filled('status')) $query->where('pet_status', $request->input('status'));
        if ($request->filled('health')) $query->where('health_condition', $request->input('health'));
        if ($request->filled('ownership')) $query->where('ownership_status', $request->input('ownership'));
        if ($request->filled('adopter')) $query->whereHas('adoption', fn ($q) => $q->where('full_name', 'like', '%' . $request->input('adopter') . '%'));
        if ($request->filled('from')) $query->whereDate('actual_visit_date', '>=', $request->input('from'));
        if ($request->filled('to')) $query->whereDate('actual_visit_date', '<=', $request->input('to'));
        return Inertia::render('Admin/PostAdoption/History', ['visits' => $query->latest('actual_visit_date')->paginate(15)->withQueryString(), 'filters' => $request->only('pet', 'adopter', 'status', 'health', 'ownership', 'from', 'to'), 'pets' => $this->eligibleAdoptions()->get()->pluck('pet')->unique('id')->values()]);
    }

    public function petProfile(int $pet)
    {
        $adoption = $this->eligibleAdoptions()->where('pet_id', $pet)->latest()->firstOrFail();
        return Inertia::render('Admin/PostAdoption/PetProfile', [
            'adoption' => $adoption,
            'visits' => $this->baseQuery()->where('pet_id', $pet)->orderByDesc('scheduled_date')->get(),
        ]);
    }

    public function evidence(PostAdoptionVisitAttachment $attachment)
    {
        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);
        return Storage::disk('local')->response($attachment->file_path, $attachment->original_filename, [
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    private function validateSchedule(Request $request): array
    {
        return $request->validate([
            'adoption_id' => 'required|exists:adoptions,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'assigned_user_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', ['admin', 'superadmin'])->where('status', 'Active'))],
            'monitoring_method' => ['required', Rule::in(['Home Visit', 'Phone Call', 'Video Call', 'Other'])],
            'purpose' => 'nullable|string|max:255', 'notes' => 'nullable|string|max:5000',
        ]);
    }

    private function staff()
    {
        return User::whereIn('role', ['admin', 'superadmin'])->where('status', 'Active')->orderBy('name')->get(['id', 'name']);
    }
}
