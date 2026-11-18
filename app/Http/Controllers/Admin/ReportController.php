<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Adoption;
use App\Models\Donation;
use App\Models\EuthanasiaLog;
use App\Models\HealthRecord;
use App\Models\PostAdoptionVisit;
use App\Models\VaccineSchedule;
use App\Services\Reports\ReportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $reports)
    {
        $filters = $request->validate([
            'period' => ['nullable', Rule::in(['weekly', 'monthly', 'yearly', 'custom'])],
            'date' => ['nullable', 'date'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'start_date' => ['nullable', 'required_if:period,custom', 'date'],
            'end_date' => ['nullable', 'required_if:period,custom', 'date', 'after_or_equal:start_date'],
        ]);

        $filters['period'] ??= 'monthly';
        return Inertia::render('Admin/Reports/Index', $reports->build($filters) + [
            'filters' => $filters,
        ]);
    }

    public function pdf(Request $request, ReportService $reports)
    {
        $filters = $request->validate([
            'period' => ['nullable', Rule::in(['weekly', 'monthly', 'yearly', 'custom'])],
            'date' => ['nullable', 'date'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'start_date' => ['nullable', 'required_if:period,custom', 'date'],
            'end_date' => ['nullable', 'required_if:period,custom', 'date', 'after_or_equal:start_date'],
        ]);
        $filters['period'] ??= 'monthly';
        $data = $reports->build($filters);
        $p = $data['period'];
        $data['details']['adoptions'] = Adoption::with(['pet:id,name', 'user:id,name'])
            ->whereBetween('created_at', [$p['start'], $p['end']])
            ->latest()->get();
        $data['details']['donations'] = Donation::whereBetween('created_at', [$p['start'], $p['end']])
            ->latest()->get();
        $html = view('reports.pdf', $data)->render();
        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="animal-shelter-report.pdf"',
        ]);
    }

    public function excel(Request $request, ReportService $reports)
    {
        $filters = $request->validate([
            'period' => ['nullable', Rule::in(['weekly', 'monthly', 'yearly', 'custom'])],
            'date' => ['nullable', 'date'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'start_date' => ['nullable', 'required_if:period,custom', 'date'],
            'end_date' => ['nullable', 'required_if:period,custom', 'date', 'after_or_equal:start_date'],
        ]);
        $filters['period'] ??= 'monthly';
        $data = $reports->build($filters);
        $p = $data['period'];

        $book = new Spreadsheet();
        $book->getProperties()->setTitle('Animal Shelter Reports & Analytics');
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Summary');
        $sheet->fromArray([['Animal Shelter Management System'], ['Reporting period', $p['label']], ['Date generated', now()->toDateTimeString()]], null, 'A1');
        $row = 5;
        foreach ($data['summary'] as $key => $value) {
            $sheet->setCellValue("A{$row}", str($key)->replace('_', ' ')->headline()->toString());
            $sheet->setCellValue("B{$row}", $value);
            $row++;
        }

        $this->addSheet($book, 'Pets', ['ID', 'Name', 'Species', 'Gender', 'Current Status', 'Date Received'], function () use ($p) {
            return \App\Models\Pet::latest('created_at')->get()
                ->map(fn ($pet) => [$pet->id, $pet->name, $pet->species, $pet->gender, $pet->status, $pet->date_received?->format('Y-m-d')]);
        });
        $this->addSheet($book, 'Adoptions', ['ID', 'Applicant', 'Pet', 'Status', 'Date'], function () use ($p) {
            return Adoption::with(['pet:id,name', 'user:id,name'])->whereBetween('created_at', [$p['start'], $p['end']])->latest()->get()
                ->map(fn ($a) => [$a->id, $a->full_name ?: $a->user?->name, $a->pet?->name, $a->status, $a->created_at?->toDateTimeString()]);
        });
        $this->addSheet($book, 'Donations', ['ID', 'Donor', 'Amount', 'Payment Method', 'Verification Status', 'Submitted Date', 'Verified Date', 'Rejected Date'], function () use ($p) {
            return Donation::whereBetween('created_at', [$p['start'], $p['end']])->latest()->get()
                ->map(fn ($d) => [$d->id, $d->anonymous ? 'Anonymous' : trim($d->name . ' ' . $d->last_name), (float) $d->amount, $d->payment_method, $d->status ?: 'Pending', $d->created_at?->toDateTimeString(), $d->verified_at?->toDateTimeString(), $d->rejected_at?->toDateTimeString()]);
        });
        $this->addSheet($book, 'Health', ['ID', 'Pet', 'Diagnosis', 'Critical', 'Reported Date'], function () use ($p) {
            return HealthRecord::with('pet:id,name')->whereBetween('reported_date', [$p['start']->toDateString(), $p['end']->toDateString()])->latest('reported_date')->get()
                ->map(fn ($h) => [$h->id, $h->pet?->name, $h->diagnosis, $h->is_critical ? 'Yes' : 'No', $h->reported_date?->format('Y-m-d')]);
        });
        $this->addSheet($book, 'Vaccinations', ['ID', 'Pet', 'Type', 'Last Given', 'Next Due', 'Completed'], function () use ($p) {
            return VaccineSchedule::with('pet:id,name')->whereBetween('updated_at', [$p['start'], $p['end']])->latest('updated_at')->get()
                ->map(fn ($v) => [$v->id, $v->pet?->name, $v->vaccine_type, $v->last_given_date?->format('Y-m-d'), $v->next_due_date?->format('Y-m-d'), $v->is_completed ? 'Yes' : 'No']);
        });
        $this->addSheet($book, 'Monitoring', ['ID', 'Pet', 'Status', 'Scheduled Date', 'Actual Visit Date', 'Findings'], function () use ($p) {
            return PostAdoptionVisit::with('pet:id,name')->whereBetween('scheduled_date', [$p['start']->toDateString(), $p['end']->toDateString()])->latest('scheduled_date')->get()
                ->map(fn ($v) => [$v->id, $v->pet?->name, $v->schedule_status, $v->scheduled_date?->format('Y-m-d'), $v->actual_visit_date?->format('Y-m-d'), $v->findings]);
        });
        $this->addSheet($book, 'Euthanasia', ['ID', 'Pet', 'Reason', 'Date'], function () use ($p) {
            return EuthanasiaLog::with('pet:id,name')->whereBetween('euthanasia_date', [$p['start'], $p['end']])->latest('euthanasia_date')->get()
                ->map(fn ($e) => [$e->id, $e->pet?->name, $e->reason, $e->euthanasia_date?->toDateTimeString()]);
        });
        $this->addSheet($book, 'System Activity', ['Metric', 'Value'], function () use ($data) {
            return collect([
                ['New accounts', $data['system_activity']['new_accounts']],
                ['New administrator accounts', $data['system_activity']['new_admin_accounts']],
                ['SMS sent', $data['system_activity']['sms_sent']],
                ['SMS failed', $data['system_activity']['sms_failed']],
            ]);
        });

        $path = tempnam(sys_get_temp_dir(), 'report') . '.xlsx';
        (new Xlsx($book))->save($path);

        return response()->download($path, 'animal-shelter-report.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function addSheet(Spreadsheet $book, string $title, array $headers, callable $rows): void
    {
        $sheet = $book->createSheet()->setTitle(substr($title, 0, 31));
        $sheet->fromArray([$headers], null, 'A1');
        $data = $rows();
        if ($data->isNotEmpty()) {
            $sheet->fromArray($data->values()->all(), null, 'A2');
        }
        foreach (range('A', min(chr(64 + count($headers)), 'Z')) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
