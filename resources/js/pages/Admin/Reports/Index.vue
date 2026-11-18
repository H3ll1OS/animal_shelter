<template>
    <AdminLayout>
        <section class="report-page">
            <header class="report-header">
                <div>
                    <p class="eyebrow">Operations intelligence</p>
                    <h1>Reports &amp; Analytics</h1>
                    <p>{{ period.label }}</p>
                </div>
                <div class="actions">
                    <button type="button" class="btn btn-neutral" @click="printReport">Print</button>
                    <a class="btn btn-neutral" :href="exportUrl('/admin/reports/export/pdf')">PDF</a>
                    <a class="btn btn-primary" :href="exportUrl('/admin/reports/export/excel')">Excel</a>
                </div>
            </header>

            <form class="filters" @submit.prevent="applyFilters">
                <label>Period
                    <select v-model="form.period">
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="custom">Custom date range</option>
                    </select>
                </label>
                <label v-if="form.period === 'weekly'">Week
                    <input v-model="form.date" type="date">
                </label>
                <label v-if="form.period === 'monthly' || form.period === 'yearly'">Year
                    <input v-model.number="form.year" type="number" min="2000" max="2100">
                </label>
                <label v-if="form.period === 'monthly'">Month
                    <select v-model.number="form.month">
                        <option v-for="month in 12" :key="month" :value="month">{{ monthNames[month - 1] }}</option>
                    </select>
                </label>
                <template v-if="form.period === 'custom'">
                    <label>Start date
                        <input v-model="form.start_date" type="date">
                    </label>
                    <label>End date
                        <input v-model="form.end_date" type="date">
                    </label>
                </template>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Generate report</button>
                    <button type="button" class="btn btn-neutral" @click="resetFilters">Reset</button>
                </div>
            </form>
            <p v-if="filterError" class="error">{{ filterError }}</p>

            <div class="summary-grid">
                <article v-for="card in cards" :key="card.label" class="summary-card">
                    <span>{{ card.label }}</span>
                    <strong>{{ card.value }}</strong>
                </article>
            </div>

            <div class="chart-grid">
                <article class="panel">
                    <h2>Pets by species</h2>
                    <BarChart :labels="Object.keys(charts.pets_species)" :values="Object.values(charts.pets_species)" />
                </article>
                <article class="panel">
                    <h2>Adoption applications by status</h2>
                    <BarChart :labels="Object.keys(charts.adoption_status)" :values="Object.values(charts.adoption_status)" />
                </article>
            </div>

            <article class="panel">
                <h2>Adoption applications</h2>
                <DataTable :rows="details.adoptions.data" empty="No adoption applications for this period."
                    :columns="['Applicant','Pet','Status','Date']">
                    <template #row="{ row }">
                        <td>{{ row.full_name || row.user?.name || 'N/A' }}</td>
                        <td>{{ row.pet?.name || 'N/A' }}</td>
                        <td>{{ row.status }}</td>
                        <td>{{ formatDate(row.created_at) }}</td>
                    </template>
                </DataTable>
            </article>

            <article class="panel">
                <h2>Donation submissions</h2>
                <DataTable :rows="details.donations.data" empty="No donations for this period."
                    :columns="['Donor','Amount','Payment Method','Status','Date']">
                    <template #row="{ row }">
                        <td>{{ row.anonymous ? 'Anonymous' : [row.name, row.last_name].filter(Boolean).join(' ') }}</td>
                        <td>{{ formatMoney(row.amount) }}</td>
                        <td>{{ row.payment_method }}</td>
                        <td>{{ row.status || 'Pending' }}</td>
                        <td>{{ formatDate(row.created_at) }}</td>
                    </template>
                </DataTable>
            </article>

            <article class="panel">
                <h2>Post-adoption monitoring</h2>
                <div class="mini-grid">
                    <span><strong>{{ monitoring.scheduled }}</strong> Scheduled</span>
                    <span><strong>{{ monitoring.completed }}</strong> Completed</span>
                    <span><strong>{{ monitoring.upcoming }}</strong> Upcoming</span>
                    <span><strong>{{ monitoring.overdue }}</strong> Overdue</span>
                </div>
            </article>
        </section>
    </AdminLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import BarChart from './components/BarChart.vue';
import DataTable from './components/DataTable.vue';

const props = defineProps({
    period: Object,
    summary: Object,
    charts: Object,
    details: Object,
    health: Object,
    vaccination: Object,
    monitoring: Object,
    system_activity: Object,
    filters: Object,
});

const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const today = new Date();
const form = reactive({
    period: props.filters.period || 'monthly',
    date: props.filters.date || today.toISOString().slice(0, 10),
    month: props.filters.month || today.getMonth() + 1,
    year: props.filters.year || today.getFullYear(),
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});
const filterError = ref('');

const cards = computed(() => [
    { label: 'Pets Registered', value: props.summary.pets_registered },
    { label: 'Adoption Applications', value: props.summary.adoption_applications },
    { label: 'Successful Adoptions', value: props.summary.successful_adoptions },
    { label: 'Verified Donations', value: props.summary.verified_donations },
    { label: 'Pending Donations', value: props.summary.pending_donations },
    { label: 'Rejected Donations', value: props.summary.rejected_donations },
    { label: 'Verified Amount', value: formatMoney(props.summary.verified_donation_amount) },
    { label: 'Completed Monitoring', value: props.summary.completed_monitoring_visits },
    { label: 'Euthanasia Records', value: props.summary.euthanasia_records },
]);

function applyFilters() {
    filterError.value = '';
    if (form.period === 'custom' && (!form.start_date || !form.end_date || form.start_date > form.end_date)) {
        filterError.value = 'Start date must not be later than end date.';
        return;
    }
    router.get('/admin/reports', { ...form }, { preserveState: true, preserveScroll: true });
}

function resetFilters() {
    form.period = 'monthly';
    form.month = today.getMonth() + 1;
    form.year = today.getFullYear();
    form.date = today.toISOString().slice(0, 10);
    form.start_date = '';
    form.end_date = '';
    router.get('/admin/reports');
}

function exportUrl(base) {
    const params = new URLSearchParams();
    Object.entries(form).forEach(([key, value]) => {
        if (value !== '' && value !== null && value !== undefined) params.set(key, String(value));
    });
    return `${base}?${params}`;
}

function printReport() { window.print(); }
function formatMoney(value) { return `PHP ${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`; }
function formatDate(value) { return value ? String(value).slice(0, 10) : 'N/A'; }
</script>

<style scoped>
.report-page { display: grid; gap: 1.25rem; padding: clamp(1rem,2vw,1.75rem); }
.report-header { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-end; }
.report-header h1 { margin: .2rem 0; font-size: 1.75rem; color: #0f172a; }
.eyebrow { color: #f97316; font-weight: 800; text-transform: uppercase; font-size: .75rem; }
.actions, .filter-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.filters { display: flex; align-items: end; gap: .75rem; flex-wrap: wrap; background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; }
.filters label { display: grid; gap: .35rem; color: #475569; font-size: .82rem; font-weight: 700; }
.filters input, .filters select { border: 1px solid #cbd5e1; border-radius: 8px; padding: .55rem .65rem; min-width: 150px; background: white; }
.btn { border-radius: 8px; padding: .55rem .8rem; text-decoration: none; border: 1px solid #cbd5e1; background: white; color: #334155; cursor: pointer; }
.btn-primary { background: #f97316; border-color: #f97316; color: white; }
.error, .notice { color: #b45309; }
.summary-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: .75rem; }
.summary-card { background: white; border: 1px solid #e2e8f0; border-left: 4px solid #f97316; border-radius: 10px; padding: 1rem; display: grid; }
.summary-card span { color: #64748b; font-size: .82rem; }
.summary-card strong { color: #0f172a; font-size: 1.45rem; }
.chart-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 1rem; }
.panel { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; }
.panel h2 { color: #1e293b; margin-bottom: .75rem; }
.mini-grid { display:grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap:.5rem; }
.mini-grid span { background:#f8fafc; border-radius:8px; padding:.75rem; display:grid; }
@media (max-width: 900px) { .report-header, .chart-grid { grid-template-columns:1fr; flex-direction: column; } .summary-grid { grid-template-columns:1fr 1fr; } .mini-grid { grid-template-columns:1fr 1fr; } }
@media print { aside, .filters, .actions { display:none !important; } .report-page { padding:0; } }
</style>
