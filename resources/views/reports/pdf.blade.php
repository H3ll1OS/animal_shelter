<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reports &amp; Analytics</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { color: #0f172a; border-bottom: 3px solid #f97316; padding-bottom: 8px; }
        h2 { margin-top: 24px; color: #334155; }
        .meta { color: #64748b; margin-bottom: 16px; }
        .card { display: inline-block; width: 22%; border: 1px solid #e2e8f0; padding: 8px; margin: 0 1% 8px 0; }
        .card strong { display: block; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #0f172a; color: white; }
        th, td { border: 1px solid #cbd5e1; padding: 5px; text-align: left; }
    </style>
</head>
<body>
    <h1>Animal Shelter Management System - Reports &amp; Analytics</h1>
    <div class="meta">
        <strong>Reporting period:</strong> {{ $period['label'] }}<br>
        <strong>Date generated:</strong> {{ now()->toDayDateTimeString() }}
    </div>
    <h2>Summary</h2>
    @foreach ($summary as $label => $value)
        <div class="card">
            {{ str($label)->replace('_', ' ')->headline() }}
            <strong>{{ is_float($value) ? number_format($value, 2) : $value }}</strong>
        </div>
    @endforeach
    <h2>Adoption applications</h2>
    <table>
        <thead><tr><th>ID</th><th>Applicant</th><th>Pet</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        @foreach ($details['adoptions'] as $item)
            <tr><td>{{ $item->id }}</td><td>{{ $item->full_name ?: $item->user?->name }}</td><td>{{ $item->pet?->name }}</td><td>{{ $item->status }}</td><td>{{ $item->created_at?->format('Y-m-d') }}</td></tr>
        @endforeach
        </tbody>
    </table>
    <h2>Donations</h2>
    <table>
        <thead><tr><th>ID</th><th>Donor</th><th>Amount</th><th>Payment Method</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        @foreach ($details['donations'] as $item)
            <tr><td>{{ $item->id }}</td><td>{{ $item->anonymous ? 'Anonymous' : trim($item->name . ' ' . $item->last_name) }}</td><td>PHP {{ number_format((float) $item->amount, 2) }}</td><td>{{ $item->payment_method }}</td><td>{{ $item->status ?: 'Pending' }}</td><td>{{ $item->created_at?->format('Y-m-d') }}</td></tr>
        @endforeach
        </tbody>
    </table>
    <h2>Pet Management</h2>
    <table>
        <thead><tr><th>Metric</th><th>Value</th></tr></thead>
        <tbody>
            <tr><td>Pets registered in period</td><td>{{ $summary['pets_registered'] }}</td></tr>
            <tr><td>Current pet population</td><td>{{ $summary['current_pet_population'] }}</td></tr>
        </tbody>
    </table>
    <h2>Health and Vaccination</h2>
    <table>
        <thead><tr><th>Metric</th><th>Value</th></tr></thead>
        <tbody>
            <tr><td>Health records</td><td>{{ $health['records'] }}</td></tr>
            <tr><td>Pets with recorded conditions</td><td>{{ $health['pets_with_conditions'] }}</td></tr>
            <tr><td>Vaccinations administered</td><td>{{ $vaccination['administered'] }}</td></tr>
            <tr><td>Upcoming schedules</td><td>{{ $vaccination['upcoming'] }}</td></tr>
            <tr><td>Overdue schedules</td><td>{{ $vaccination['overdue'] }}</td></tr>
        </tbody>
    </table>
    <h2>Post-Adoption Monitoring</h2>
    <table>
        <thead><tr><th>Scheduled</th><th>Completed</th><th>Upcoming</th><th>Overdue</th></tr></thead>
        <tbody><tr><td>{{ $monitoring['scheduled'] }}</td><td>{{ $monitoring['completed'] }}</td><td>{{ $monitoring['upcoming'] }}</td><td>{{ $monitoring['overdue'] }}</td></tr></tbody>
    </table>
    <h2>Euthanasia</h2>
    <table>
        <thead><tr><th>Metric</th><th>Value</th></tr></thead>
        <tbody><tr><td>Records in period</td><td>{{ $summary['euthanasia_records'] }}</td></tr></tbody>
    </table>
    <h2>System Activity</h2>
    <table>
        <thead><tr><th>Metric</th><th>Value</th></tr></thead>
        <tbody>
            <tr><td>New accounts</td><td>{{ $system_activity['new_accounts'] }}</td></tr>
            <tr><td>New administrator accounts</td><td>{{ $system_activity['new_admin_accounts'] }}</td></tr>
            <tr><td>SMS sent</td><td>{{ $system_activity['sms_sent'] }}</td></tr>
            <tr><td>SMS failed</td><td>{{ $system_activity['sms_failed'] }}</td></tr>
        </tbody>
    </table>
</body>
</html>
