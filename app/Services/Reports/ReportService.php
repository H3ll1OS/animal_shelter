<?php

namespace App\Services\Reports;

use App\Models\Adoption;
use App\Models\ActivityLog;
use App\Models\Donation;
use App\Models\EuthanasiaLog;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\PostAdoptionVisit;
use App\Models\SmsNotification;
use App\Models\User;
use App\Models\VaccineSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function period(array $filters): array
    {
        $timezone = config('app.timezone');
        $period = $filters['period'] ?? 'monthly';
        $now = Carbon::now($timezone);

        if ($period === 'weekly') {
            $start = Carbon::parse($filters['date'] ?? $now->toDateString(), $timezone)->startOfWeek(Carbon::MONDAY);
            $end = $start->copy()->endOfWeek(Carbon::SUNDAY);
        } elseif ($period === 'yearly') {
            $year = (int) ($filters['year'] ?? $now->year);
            $start = Carbon::create($year, 1, 1, 0, 0, 0, $timezone)->startOfDay();
            $end = $start->copy()->endOfYear();
        } elseif ($period === 'custom') {
            $start = Carbon::parse($filters['start_date'], $timezone)->startOfDay();
            $end = Carbon::parse($filters['end_date'], $timezone)->endOfDay();
        } else {
            $month = max(1, min(12, (int) ($filters['month'] ?? $now->month)));
            $year = (int) ($filters['year'] ?? $now->year);
            $start = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfDay();
            $end = $start->copy()->endOfMonth();
        }

        return [
            'period' => $period,
            'start' => $start,
            'end' => $end,
            'label' => $period === 'yearly'
                ? $start->format('Y')
                : ($start->format('Y-m-d') . ' to ' . $end->format('Y-m-d')),
        ];
    }

    public function build(array $filters): array
    {
        $p = $this->period($filters);
        $start = $p['start'];
        $end = $p['end'];

        $petsRegistered = Pet::whereBetween('created_at', [$start, $end])->count();
        $adoptions = Adoption::whereBetween('created_at', [$start, $end])->get();
        $successfulAdoptions = Adoption::where('status', 'Approved')
            ->whereHas('pet', fn ($q) => $q->where('status', 'Adopted'))
            ->whereBetween('created_at', [$start, $end])
            ->distinct()->count('id');

        $donations = Donation::whereBetween('created_at', [$start, $end])->get();
        $verifiedDonations = Donation::where('status', 'Verified')
            ->whereBetween('verified_at', [$start, $end])
            ->get();
        $pendingDonations = Donation::where('status', 'Pending')
            ->whereBetween('created_at', [$start, $end])
            ->count();
        $rejectedDonations = Donation::where('status', 'Rejected')
            ->whereBetween('rejected_at', [$start, $end])
            ->count();
        $monitoringCompleted = PostAdoptionVisit::where('schedule_status', 'Completed')
            ->whereBetween('actual_visit_date', [$start->toDateString(), $end->toDateString()])
            ->count();

        $summary = [
            'pets_registered' => $petsRegistered,
            'current_pet_population' => Pet::count(),
            'adoption_applications' => $adoptions->count(),
            'successful_adoptions' => $successfulAdoptions,
            'donation_submissions' => $donations->count(),
            'pending_donations' => $pendingDonations,
            'verified_donations' => $verifiedDonations->count(),
            'rejected_donations' => $rejectedDonations,
            'verified_donation_amount' => (float) $verifiedDonations
                ->whereNotNull('amount')
                ->sum(fn ($d) => is_numeric($d->amount) ? (float) $d->amount : 0),
            'completed_monitoring_visits' => $monitoringCompleted,
            'euthanasia_records' => EuthanasiaLog::whereBetween('euthanasia_date', [$start, $end])->count(),
        ];

        $petsBySpecies = Pet::select('species', DB::raw('COUNT(*) as total'))
            ->groupBy('species')->orderBy('species')->pluck('total', 'species');
        $petsByStatus = Pet::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->orderBy('status')->pluck('total', 'status');

        $adoptionStatus = $adoptions->countBy(fn ($a) => $a->status ?: 'Unknown');
        $donationStatus = [
            'Pending' => $pendingDonations,
            'Verified' => $verifiedDonations->count(),
            'Rejected' => $rejectedDonations,
        ];

        $healthRecords = HealthRecord::whereBetween('reported_date', [$start->toDateString(), $end->toDateString()]);
        $vaccinationsAdministered = VaccineSchedule::where('is_completed', true)
            ->whereBetween('last_given_date', [$start->toDateString(), $end->toDateString()])
            ->count();
        $vaccinationByType = VaccineSchedule::select('vaccine_type', DB::raw('COUNT(*) as total'))
            ->where('is_completed', true)
            ->whereBetween('last_given_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('vaccine_type')->orderBy('vaccine_type')->pluck('total', 'vaccine_type');

        $monitoring = [
            'scheduled' => PostAdoptionVisit::whereBetween('created_at', [$start, $end])->count(),
            'completed' => $monitoringCompleted,
            'upcoming' => PostAdoptionVisit::where('schedule_status', 'Pending')
                ->whereBetween('scheduled_date', [$start->toDateString(), $end->toDateString()])
                ->whereDate('scheduled_date', '>=', $start->toDateString())->count(),
            'overdue' => PostAdoptionVisit::where('schedule_status', 'Pending')
                ->whereDate('scheduled_date', '<', now($p['start']->timezoneName)->toDateString())
                ->whereBetween('scheduled_date', [$start->toDateString(), $end->toDateString()])
                ->count(),
        ];

        $euthanasiaReason = EuthanasiaLog::whereBetween('euthanasia_date', [$start, $end])
            ->select('reason', DB::raw('COUNT(*) as total'))->groupBy('reason')->pluck('total', 'reason');

        $smsSent = class_exists(SmsNotification::class)
            ? SmsNotification::whereBetween('created_at', [$start, $end])->whereIn('status', ['sent', 'delivered'])->count()
            : 0;
        $smsFailed = class_exists(SmsNotification::class)
            ? SmsNotification::whereBetween('created_at', [$start, $end])->where('status', 'failed')->count()
            : 0;

        $adoptionPage = (int) request()->integer('adoption_page', 1);
        $adoptionDetails = Adoption::with(['pet:id,name,species,status', 'user:id,name,email'])
            ->whereBetween('created_at', [$start, $end])
            ->latest()->paginate(10, ['*'], 'adoption_page', $adoptionPage);

        $donationDetails = Donation::whereBetween('created_at', [$start, $end])
            ->latest()->paginate(10, ['*'], 'donation_page');
        $healthDetails = (clone $healthRecords)->with('pet:id,name,species')
            ->latest('reported_date')->paginate(10, ['*'], 'health_page');
        $vaccinationDetails = VaccineSchedule::with('pet:id,name,species')
            ->whereBetween('updated_at', [$start, $end])
            ->latest('updated_at')->paginate(10, ['*'], 'vaccination_page');
        $monitoringDetails = PostAdoptionVisit::with(['pet:id,name,species', 'adopter:id,name,email'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end])
                    ->orWhereBetween('scheduled_date', [$start->toDateString(), $end->toDateString()]);
            })->latest('scheduled_date')->paginate(10, ['*'], 'monitoring_page');
        $euthanasiaDetails = EuthanasiaLog::with('pet:id,name,species')
            ->whereBetween('euthanasia_date', [$start, $end])
            ->latest('euthanasia_date')->paginate(10, ['*'], 'euthanasia_page');
        $activityDetails = ActivityLog::whereBetween('created_at', [$start, $end])
            ->latest()->paginate(10, ['*'], 'activity_page');

        return [
            'period' => $p,
            'summary' => $summary,
            'charts' => [
                'pets_species' => $petsBySpecies,
                'pets_status' => $petsByStatus,
                'adoption_status' => $adoptionStatus,
                'donation_status' => $donationStatus,
                'vaccination_type' => $vaccinationByType,
                'monitoring_status' => $monitoring,
                'euthanasia_reason' => $euthanasiaReason,
            ],
            'details' => [
                'adoptions' => $adoptionDetails,
                'donations' => $donationDetails,
                'health' => $healthDetails,
                'vaccinations' => $vaccinationDetails,
                'monitoring' => $monitoringDetails,
                'euthanasia' => $euthanasiaDetails,
                'activity' => $activityDetails,
            ],
            'health' => [
                'records' => $healthRecords->count(),
                'pets_with_conditions' => (clone $healthRecords)->distinct()->count('pet_id'),
            ],
            'vaccination' => [
                'administered' => $vaccinationsAdministered,
                'upcoming' => VaccineSchedule::where('is_completed', false)
                    ->whereBetween('next_due_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
                    ->count(),
                'overdue' => VaccineSchedule::where('is_completed', false)
                    ->whereDate('next_due_date', '<', now()->toDateString())->count(),
            ],
            'monitoring' => $monitoring,
            'system_activity' => [
                'new_accounts' => User::withTrashed()->whereBetween('created_at', [$start, $end])->count(),
                'new_admin_accounts' => User::withTrashed()->where('role', 'admin')->whereBetween('created_at', [$start, $end])->count(),
                'sms_sent' => $smsSent,
                'sms_failed' => $smsFailed,
            ],
        ];
    }
}
