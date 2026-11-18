<template>
    <AdminLayout>
        <section class="report-page" :aria-busy="loading">
            <header class="report-header">
                <div class="report-heading">
                    <p class="eyebrow"><span class="eyebrow-dot"></span> Operations intelligence</p>
                    <h1>Reports <span>&amp; Analytics</span></h1>
                    <p class="report-subtitle">A clear view of shelter activity and outcomes.</p>
                    <div class="period-chip">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3.5" y="5" width="17" height="15.5" rx="2" />
                            <path d="M7.5 3.5v3M16.5 3.5v3M3.5 9.5h17" />
                        </svg>
                        <span>{{ period.label }}</span>
                    </div>
                </div>
                <div class="actions" aria-label="Report actions">
                    <button type="button" class="btn btn-neutral" @click="printReport">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 8V3.5h10V8M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M7 14h10v6.5H7z" /><path d="M17.5 11.5h.01" /></svg>
                        Print
                    </button>
                    <a class="btn btn-neutral" :href="exportUrl('/admin/reports/export/pdf')">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 3.5h8l4 4v13H6zM14 3.5v4h4M8.5 15h2a1.5 1.5 0 0 0 0-3h-2v5M14 17v-5h1.5a2.5 2.5 0 0 1 0 5H14z" /></svg>
                        PDF
                    </a>
                    <a class="btn btn-primary" :href="exportUrl('/admin/reports/export/excel')">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 3.5h8l4 4v13H6zM14 3.5v4h4M8.5 12l3 5m0-5-3 5m5-5h2.5m-2.5 2.5h2.5m-2.5 2.5h2.5" /></svg>
                        Export Excel
                    </a>
                </div>
            </header>

            <form class="filters" @submit.prevent="applyFilters">
                <div class="filter-intro">
                    <div class="filter-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M4 6h16M7 12h10m-7 6h4" /><circle cx="8" cy="6" r="1.5" /><circle cx="15" cy="12" r="1.5" /><circle cx="11" cy="18" r="1.5" /></svg>
                    </div>
                    <div><h2>Report period</h2><p>Select a date range to refresh the figures below.</p></div>
                </div>
                <div class="filter-controls">
                    <label class="filter-field">Period
                        <select v-model="form.period">
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="custom">Custom range</option>
                        </select>
                    </label>
                    <label v-if="form.period === 'weekly'" class="filter-field">Week of
                        <input v-model="form.date" type="date">
                    </label>
                    <label v-if="form.period === 'monthly' || form.period === 'yearly'" class="filter-field">Year
                        <input v-model.number="form.year" type="number" min="2000" max="2100">
                    </label>
                    <label v-if="form.period === 'monthly'" class="filter-field">Month
                        <select v-model.number="form.month">
                            <option v-for="month in 12" :key="month" :value="month">{{ monthNames[month - 1] }}</option>
                        </select>
                    </label>
                    <template v-if="form.period === 'custom'">
                        <label class="filter-field">Start date
                            <input v-model="form.start_date" type="date">
                        </label>
                        <label class="filter-field">End date
                            <input v-model="form.end_date" type="date">
                        </label>
                    </template>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary" :disabled="loading">
                            <span v-if="loading" class="button-spinner" aria-hidden="true"></span>
                            {{ loading ? 'Generating...' : 'Generate report' }}
                        </button>
                        <button type="button" class="btn btn-reset" :disabled="loading" @click="resetFilters">Reset</button>
                    </div>
                </div>
            </form>
            <p v-if="filterError" class="error" role="alert">{{ filterError }}</p>

            <section class="summary-section">
                <div class="section-heading">
                    <div><p class="section-kicker">At a glance</p><h2>Key indicators</h2></div>
                    <span class="section-caption">For {{ period.label }}</span>
                </div>
                <div class="summary-grid">
                    <article v-for="card in cards" :key="card.label" class="summary-card" :class="`summary-card--${card.tone}`">
                        <div class="card-topline">
                            <span class="card-icon" aria-hidden="true">{{ card.symbol }}</span>
                            <span class="card-period">{{ card.current ? 'Current' : 'This period' }}</span>
                        </div>
                        <span class="card-label">{{ card.label }}</span>
                        <strong>{{ card.value }}</strong>
                        <small>{{ card.description }}</small>
                    </article>
                </div>
            </section>

            <section class="analytics-section">
                <div class="section-heading">
                    <div><p class="section-kicker">Explore the data</p><h2>Operational breakdown</h2></div>
                    <span class="section-caption">Live database totals</span>
                </div>
                <div class="chart-grid">
                    <article class="panel chart-panel">
                        <div class="panel-title">
                            <span class="panel-icon panel-icon--orange" aria-hidden="true">P</span>
                            <div><h3>Pet population by species</h3><p>Current pet inventory</p></div>
                        </div>
                        <BarChart :labels="Object.keys(charts.pets_species)" :values="Object.values(charts.pets_species)" />
                    </article>
                    <article class="panel chart-panel">
                        <div class="panel-title">
                            <span class="panel-icon panel-icon--blue" aria-hidden="true">A</span>
                            <div><h3>Applications by status</h3><p>Applications received in this period</p></div>
                        </div>
                        <BarChart :labels="Object.keys(charts.adoption_status)" :values="Object.values(charts.adoption_status)" />
                    </article>
                </div>
            </section>

            <section class="records-section">
                <div class="section-heading">
                    <div><p class="section-kicker">Recent activity</p><h2>Detailed records</h2></div>
                    <span class="section-caption">Matching selected dates</span>
                </div>
                <article class="panel table-panel">
                    <div class="panel-title">
                        <span class="panel-icon panel-icon--blue" aria-hidden="true">A</span>
                        <div><h3>Adoption applications</h3><p>{{ details.adoptions.total }} records</p></div>
                    </div>
                    <DataTable :rows="details.adoptions.data" empty="No adoption applications for this period."
                        :columns="['Applicant','Pet','Status','Date']">
                        <template #row="{ row }">
                            <td><span class="primary-cell">{{ row.full_name || row.user?.name || 'N/A' }}</span></td>
                            <td>{{ row.pet?.name || 'N/A' }}</td>
                            <td><span class="status-badge" :class="statusTone(row.status)">{{ row.status }}</span></td>
                            <td>{{ formatDate(row.created_at) }}</td>
                        </template>
                    </DataTable>
                </article>

                <article class="panel table-panel">
                    <div class="panel-title">
                        <span class="panel-icon panel-icon--green" aria-hidden="true">₱</span>
                        <div><h3>Donation submissions</h3><p>{{ details.donations.total }} records · verified amount only</p></div>
                    </div>
                    <DataTable :rows="details.donations.data" empty="No donations for this period."
                        :columns="['Donor','Amount','Payment Method','Status','Date']">
                        <template #row="{ row }">
                            <td><span class="primary-cell">{{ row.anonymous ? 'Anonymous' : [row.name, row.last_name].filter(Boolean).join(' ') }}</span></td>
                            <td class="amount-cell">{{ formatMoney(row.amount) }}</td>
                            <td>{{ row.payment_method }}</td>
                            <td><span class="status-badge" :class="statusTone(row.status || 'Pending')">{{ row.status || 'Pending' }}</span></td>
                            <td>{{ formatDate(row.created_at) }}</td>
                        </template>
                    </DataTable>
                </article>
            </section>

            <article class="panel monitoring-panel">
                <div class="panel-title">
                    <span class="panel-icon panel-icon--violet" aria-hidden="true">M</span>
                    <div><h3>Post-adoption monitoring</h3><p>Visit status summary</p></div>
                </div>
                <div class="mini-grid">
                    <div class="monitoring-metric"><strong>{{ monitoring.scheduled }}</strong><span>Scheduled</span></div>
                    <div class="monitoring-metric"><strong>{{ monitoring.completed }}</strong><span>Completed</span></div>
                    <div class="monitoring-metric"><strong>{{ monitoring.upcoming }}</strong><span>Upcoming</span></div>
                    <div class="monitoring-metric"><strong>{{ monitoring.overdue }}</strong><span>Overdue</span></div>
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
const loading = ref(false);

const cards = computed(() => [
    { label: 'Pets Registered', value: props.summary.pets_registered, description: 'Added during this period', symbol: 'P', tone: 'orange' },
    { label: 'Pet Population', value: props.summary.current_pet_population, description: 'Current shelter inventory', symbol: '↗', tone: 'slate', current: true },
    { label: 'Adoption Applications', value: props.summary.adoption_applications, description: 'Applications received', symbol: 'A', tone: 'blue' },
    { label: 'Successful Adoptions', value: props.summary.successful_adoptions, description: 'Approved and adopted', symbol: '✓', tone: 'green' },
    { label: 'Verified Donations', value: props.summary.verified_donations, description: 'Verified in this period', symbol: 'V', tone: 'green' },
    { label: 'Pending Donations', value: props.summary.pending_donations, description: 'Awaiting review', symbol: '…', tone: 'amber' },
    { label: 'Rejected Donations', value: props.summary.rejected_donations, description: 'Rejected in this period', symbol: '!', tone: 'rose' },
    { label: 'Verified Amount', value: formatMoney(props.summary.verified_donation_amount), description: 'Verified donations only', symbol: '₱', tone: 'green' },
    { label: 'Monitoring Visits', value: props.summary.completed_monitoring_visits, description: 'Completed during period', symbol: 'M', tone: 'violet' },
    { label: 'Euthanasia Records', value: props.summary.euthanasia_records, description: 'Recorded during period', symbol: 'E', tone: 'slate' },
]);

function applyFilters() {
    filterError.value = '';
    if (form.period === 'custom' && (!form.start_date || !form.end_date || form.start_date > form.end_date)) {
        filterError.value = 'Start date must not be later than end date.';
        return;
    }
    router.get('/admin/reports', { ...form }, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => { loading.value = true; },
        onFinish: () => { loading.value = false; },
    });
}

function resetFilters() {
    form.period = 'monthly';
    form.month = today.getMonth() + 1;
    form.year = today.getFullYear();
    form.date = today.toISOString().slice(0, 10);
    form.start_date = '';
    form.end_date = '';
    router.get('/admin/reports', {}, {
        onStart: () => { loading.value = true; },
        onFinish: () => { loading.value = false; },
    });
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
function statusTone(value) {
    return {
        Approved: 'status-badge--green',
        Verified: 'status-badge--green',
        Completed: 'status-badge--green',
        Pending: 'status-badge--amber',
        Rejected: 'status-badge--rose',
    }[value] || 'status-badge--slate';
}
</script>

<style scoped>
.report-page { --ink:#172033; --muted:#64748b; --line:#e4e9f1; --accent:#f97316; display:grid; gap:1.55rem; padding:clamp(1rem,2.4vw,2rem); max-width:1600px; margin:0 auto; color:var(--ink); }
.report-header { display:flex; justify-content:space-between; align-items:center; gap:1.5rem; padding:.35rem 0 .25rem; }
.report-heading { min-width:0; }
.eyebrow { display:flex; align-items:center; gap:.5rem; margin:0 0 .45rem; color:#ea580c; font-weight:800; text-transform:uppercase; font-size:.72rem; letter-spacing:.11em; }
.eyebrow-dot { width:.45rem; height:.45rem; border-radius:50%; background:#f97316; box-shadow:0 0 0 4px #ffedd5; }
.report-header h1 { margin:0; color:#111827; font-size:clamp(1.7rem,3vw,2.25rem); letter-spacing:-.045em; line-height:1.12; font-weight:780; }
.report-header h1 span { color:#64748b; font-weight:580; }
.report-subtitle { margin:.55rem 0 .8rem; color:var(--muted); font-size:.94rem; }
.period-chip { display:inline-flex; align-items:center; gap:.45rem; border:1px solid #fed7aa; border-radius:999px; background:#fff7ed; padding:.35rem .7rem; color:#9a3412; font-size:.78rem; font-weight:700; }
.period-chip svg { width:15px; height:15px; stroke:currentColor; stroke-width:1.7; stroke-linecap:round; stroke-linejoin:round; }
.actions,.filter-actions { display:flex; gap:.55rem; align-items:center; flex-wrap:wrap; }
.btn { display:inline-flex; align-items:center; justify-content:center; gap:.45rem; min-height:2.65rem; border-radius:9px; padding:.56rem .85rem; text-decoration:none; border:1px solid #d6deea; background:#fff; color:#334155; font-size:.85rem; font-weight:700; cursor:pointer; transition:transform .15s ease, box-shadow .15s ease, background .15s ease; }
.btn:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 5px 13px rgb(15 23 42 / 9%); }
.btn svg { width:17px; height:17px; stroke:currentColor; stroke-width:1.7; stroke-linecap:round; stroke-linejoin:round; }
.btn-primary { background:#f97316; border-color:#f97316; color:#fff; box-shadow:0 3px 8px rgb(249 115 22 / 20%); }
.btn-primary:hover:not(:disabled) { background:#ea580c; }
.btn-reset { color:#475569; }
.btn:disabled { opacity:.65; cursor:wait; }
.button-spinner { width:14px; height:14px; border:2px solid rgb(255 255 255 / 45%); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }
.filters,.panel { background:#fff; border:1px solid var(--line); border-radius:14px; box-shadow:0 2px 6px rgb(15 23 42 / 3%),0 8px 20px rgb(15 23 42 / 2%); }
.filters { padding:1rem 1.15rem; }
.filter-intro { display:flex; align-items:center; gap:.7rem; padding-bottom:.9rem; border-bottom:1px solid #eef1f5; }
.filter-icon,.panel-icon { display:grid; place-items:center; flex:0 0 auto; width:2.4rem; height:2.4rem; border-radius:10px; }
.filter-icon { background:#fff7ed; color:#ea580c; }
.filter-icon svg { width:20px; height:20px; stroke:currentColor; stroke-width:1.7; stroke-linecap:round; stroke-linejoin:round; }
.filter-intro h2,.panel-title h3 { margin:0; color:#1e293b; font-size:.95rem; font-weight:750; }
.filter-intro p,.panel-title p { margin:.15rem 0 0; color:#8490a2; font-size:.78rem; }
.filter-controls { display:flex; align-items:end; gap:.75rem; flex-wrap:wrap; padding-top:.9rem; }
.filter-field { display:grid; gap:.38rem; min-width:145px; color:#596579; font-size:.75rem; font-weight:700; }
.filter-field input,.filter-field select { width:100%; min-height:2.65rem; border:1px solid #d9e0ea; border-radius:8px; padding:.55rem .7rem; background:#fff; color:#273449; font:inherit; font-size:.86rem; outline:none; transition:border-color .15s ease,box-shadow .15s ease; }
.filter-field input:focus,.filter-field select:focus { border-color:#fb923c; box-shadow:0 0 0 3px rgb(249 115 22 / 12%); }
.filter-actions { margin-left:auto; }
.error { margin:-.9rem 0 0; color:#b45309; font-size:.85rem; }
.summary-section,.analytics-section,.records-section { display:grid; gap:.8rem; }
.section-heading { display:flex; align-items:end; justify-content:space-between; gap:1rem; }
.section-kicker { margin:0 0 .1rem; color:#f97316; text-transform:uppercase; letter-spacing:.09em; font-weight:800; font-size:.65rem; }
.section-heading h2 { margin:0; color:#172033; font-size:1.12rem; letter-spacing:-.02em; font-weight:760; }
.section-caption { color:#8792a2; font-size:.76rem; }
.summary-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.8rem; }
.summary-card { --tone:#f97316; --tone-bg:#fff7ed; position:relative; display:grid; align-content:start; gap:.36rem; min-height:142px; overflow:hidden; padding:1rem 1.05rem .95rem; background:#fff; border:1px solid var(--line); border-radius:12px; box-shadow:0 2px 5px rgb(15 23 42 / 2%); transition:transform .16s ease,box-shadow .16s ease; }
.summary-card::before { content:""; position:absolute; inset:0 auto 0 0; width:3px; background:var(--tone); }
.summary-card:hover { transform:translateY(-2px); box-shadow:0 9px 22px rgb(15 23 42 / 7%); }
.summary-card--orange { --tone:#f97316; --tone-bg:#fff7ed; }
.summary-card--blue { --tone:#3b82f6; --tone-bg:#eff6ff; }
.summary-card--green { --tone:#10b981; --tone-bg:#ecfdf5; }
.summary-card--amber { --tone:#f59e0b; --tone-bg:#fffbeb; }
.summary-card--rose { --tone:#f43f5e; --tone-bg:#fff1f2; }
.summary-card--violet { --tone:#8b5cf6; --tone-bg:#f5f3ff; }
.summary-card--slate { --tone:#64748b; --tone-bg:#f1f5f9; }
.card-topline { display:flex; justify-content:space-between; align-items:center; margin-bottom:.1rem; }
.card-icon { display:grid; place-items:center; width:1.9rem; height:1.9rem; border-radius:8px; background:var(--tone-bg); color:var(--tone); font-size:.83rem; font-weight:800; }
.card-period { color:#99a2b1; font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.045em; }
.card-label { color:#64748b; font-size:.79rem; font-weight:650; }
.summary-card strong { overflow:hidden; color:#131d2e; font-size:1.65rem; line-height:1.1; font-weight:800; letter-spacing:-.04em; text-overflow:ellipsis; white-space:nowrap; }
.summary-card small { color:#98a1af; font-size:.7rem; }
.chart-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.9rem; }
.panel { min-width:0; padding:1.1rem; }
.panel-title { display:flex; align-items:center; gap:.7rem; margin-bottom:1rem; }
.panel-icon { width:2.2rem; height:2.2rem; font-weight:800; font-size:.82rem; }
.panel-icon--orange { background:#fff7ed; color:#ea580c; }
.panel-icon--blue { background:#eff6ff; color:#2563eb; }
.panel-icon--green { background:#ecfdf5; color:#059669; }
.panel-icon--violet { background:#f5f3ff; color:#7c3aed; }
.chart-panel { min-height:205px; }
.table-panel { padding-bottom:.25rem; }
.amount-cell { color:#166534; font-weight:750; font-variant-numeric:tabular-nums; }
.primary-cell { color:#273449; font-weight:650; }
.status-badge { display:inline-flex; align-items:center; border-radius:999px; padding:.22rem .55rem; font-size:.69rem; line-height:1.2; font-weight:750; white-space:nowrap; }
.status-badge--green { background:#ecfdf5; color:#047857; }
.status-badge--amber { background:#fffbeb; color:#b45309; }
.status-badge--rose { background:#fff1f2; color:#be123c; }
.status-badge--slate { background:#f1f5f9; color:#475569; }
.monitoring-panel { display:grid; grid-template-columns:minmax(220px,.8fr) 2fr; align-items:center; gap:1rem; }
.monitoring-panel .panel-title { margin:0; }
.mini-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.6rem; }
.monitoring-metric { display:grid; gap:.15rem; border:1px solid #edf0f5; background:#fafbfc; border-radius:9px; padding:.7rem .8rem; }
.monitoring-metric strong { color:#1e293b; font-size:1.15rem; line-height:1.1; }
.monitoring-metric span { color:#7b8797; font-size:.72rem; }
@media (max-width:1100px) { .summary-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
@media (max-width:780px) { .report-header { align-items:flex-start; flex-direction:column; } .actions { width:100%; } .filter-actions { margin-left:0; } .summary-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .chart-grid { grid-template-columns:1fr; } .monitoring-panel { grid-template-columns:1fr; } }
@media (max-width:480px) { .report-page { gap:1.15rem; padding:.9rem; } .actions .btn { flex:1; padding-inline:.55rem; } .summary-grid { gap:.55rem; } .summary-card { min-height:132px; padding:.8rem .85rem; } .summary-card strong { font-size:1.4rem; } .filter-field { min-width:100%; } .filter-actions { width:100%; } .filter-actions .btn { flex:1; } .section-caption { display:none; } .mini-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media print { :global(body) { background:#fff !important; } :global(aside), :global(#sidebar), :global(header), .filters, .actions { display:none !important; } .report-page { max-width:none; padding:0; gap:.6rem; } .summary-grid { grid-template-columns:repeat(5,minmax(0,1fr)); gap:.35rem; } .summary-card,.panel { box-shadow:none; break-inside:avoid; } .summary-card { min-height:90px; padding:.55rem; } .chart-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
</style>
