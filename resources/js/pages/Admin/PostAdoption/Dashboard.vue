<template>
    <AdminLayout>
        <main class="post-adoption-dashboard mx-auto max-w-7xl space-y-5 p-4 md:p-6">
            <AdminPageHeader
                eyebrow="Post-adoption monitoring"
                title="Post-Adoption Monitoring"
                description="Review follow-up activity and welfare concerns for adopted pets."
            >
                <template #actions>
                    <Link href="/admin/post-adoption/schedules/create" class="admin-btn admin-btn--primary">
                        Schedule follow-up
                    </Link>
                </template>
            </AdminPageHeader>

            <section aria-label="Monitoring overview" class="dashboard-overview">
                <div class="dashboard-section-heading">
                    <div>
                        <h3>Monitoring overview</h3>
                        <p>Adoption follow-up activity and current attention needs.</p>
                    </div>
                    <form class="dashboard-date-filter" @submit.prevent="applyFilters">
                        <button
                            type="button"
                            class="dashboard-period-toggle"
                            :aria-expanded="periodExpanded"
                            aria-controls="dashboard-period-filters"
                            :aria-label="`Reporting period: ${periodSummary}`"
                            :title="`Reporting period: ${periodSummary}`"
                            @click="periodExpanded = !periodExpanded"
                        >
                            <svg class="dashboard-period-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 3v3m8-3v3M4 9h16M5 5h14a1 1 0 011 1v13H4V6a1 1 0 011-1z" />
                            </svg>
                            <span v-if="activeDateCount" class="dashboard-period-indicator" aria-hidden="true"></span>
                            <span class="sr-only">{{ periodSummary }}</span>
                        </button>

                        <div v-if="periodExpanded" id="dashboard-period-filters" class="dashboard-period-popover">
                            <div class="dashboard-period-fields">
                                <AdminFormField label="From date">
                                    <input v-model="range.from" type="date" class="admin-control">
                                </AdminFormField>
                                <AdminFormField label="To date">
                                    <input v-model="range.to" type="date" class="admin-control">
                                </AdminFormField>
                            </div>
                            <div class="dashboard-period-actions">
                                <Link href="/admin/post-adoption" class="admin-btn admin-btn--secondary">Clear</Link>
                                <button type="submit" class="admin-btn admin-btn--dark">Apply dates</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="dashboard-stat-grid">
                    <AdminStatCard v-for="card in cards" :key="card.key" :label="card.label" :value="stats[card.key]" />
                </div>
            </section>

            <div class="dashboard-activity-grid">
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="dashboard-panel-heading">
                        <div>
                            <p class="dashboard-panel-kicker">Next steps</p>
                            <h3 class="text-base font-bold text-slate-900">Upcoming and pending follow-ups</h3>
                            <p class="mt-0.5 text-sm text-slate-600">Pending visits, ordered by scheduled date.</p>
                        </div>
                        <Link href="/admin/post-adoption/schedules" class="admin-btn admin-btn--secondary admin-btn--small">View schedules</Link>
                    </div>

                    <div v-if="upcomingVisits.length" class="divide-y divide-slate-200">
                        <article v-for="visit in upcomingVisits" :key="visit.id" class="flex flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-5">
                            <div class="min-w-0">
                                <Link :href="`/admin/post-adoption/pets/${visit.pet_id}`" class="font-semibold text-slate-900 hover:text-orange-700">
                                    {{ visit.pet?.name || 'Unknown pet' }}
                                </Link>
                                <p class="mt-0.5 truncate text-sm text-slate-600">
                                    {{ visit.adoption?.full_name || 'Adopter not recorded' }}
                                    <span class="px-1 text-slate-400">·</span>{{ visit.monitoring_method }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 sm:gap-5">
                                <div class="text-sm sm:text-right">
                                    <p class="font-medium text-slate-800">{{ visit.scheduled_date }}</p>
                                    <p class="text-slate-500">{{ visit.assigned_user?.name || 'Unassigned' }}</p>
                                </div>
                                <AdminStatusBadge :status="scheduleLabel(visit)" />
                                <Link :href="`/admin/post-adoption/visits/${visit.id}`" class="admin-btn admin-btn--secondary admin-btn--small">Open</Link>
                            </div>
                        </article>
                    </div>
                    <div v-else class="p-4 sm:p-5">
                        <AdminEmptyState compact title="No follow-ups scheduled" description="Schedules created for eligible adoptions will appear here.">
                            <template #actions>
                                <Link href="/admin/post-adoption/schedules/create" class="admin-btn admin-btn--primary admin-btn--small">Create schedule</Link>
                            </template>
                        </AdminEmptyState>
                    </div>
                </section>

                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="dashboard-panel-heading">
                        <div>
                            <p class="dashboard-panel-kicker">Latest updates</p>
                            <h3 class="text-base font-bold text-slate-900">Recent monitoring activity</h3>
                            <p class="mt-0.5 text-sm text-slate-600">Latest completed welfare checks.</p>
                        </div>
                        <Link href="/admin/post-adoption/history" class="admin-btn admin-btn--secondary admin-btn--small">View history</Link>
                    </div>

                    <div v-if="recentVisits.length" class="divide-y divide-slate-200">
                        <article v-for="visit in recentVisits" :key="visit.id" class="flex flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-5">
                            <div class="min-w-0">
                                <Link :href="`/admin/post-adoption/pets/${visit.pet_id}`" class="font-semibold text-slate-900 hover:text-orange-700">
                                    {{ visit.pet?.name || 'Unknown pet' }}
                                </Link>
                                <p class="mt-0.5 truncate text-sm text-slate-600">
                                    {{ visit.adoption?.full_name || 'Adopter not recorded' }}
                                    <span class="px-1 text-slate-400">·</span>{{ visit.actual_visit_date || visit.scheduled_date }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <AdminStatusBadge :status="visit.pet_status" />
                                <AdminStatusBadge :status="visit.health_condition" />
                                <Link :href="`/admin/post-adoption/visits/${visit.id}`" class="admin-btn admin-btn--secondary admin-btn--small">Details</Link>
                            </div>
                        </article>
                    </div>
                    <div v-else class="p-4 sm:p-5">
                        <AdminEmptyState compact title="No completed visits yet" description="Completed monitoring checks will appear here." />
                    </div>
                </section>
            </div>

            <section class="dashboard-reports">
                <div class="dashboard-section-heading">
                    <div>
                        <h3>Welfare reports</h3>
                        <p>Unique pets reported during the selected monitoring period.</p>
                    </div>
                </div>
                <div class="dashboard-report-grid">
                    <div v-for="item in reports" :key="item.key" class="dashboard-report-item">
                        <p>{{ item.label }}</p>
                        <span>{{ stats[item.key] ?? 0 }}</span>
                    </div>
                </div>
            </section>
        </main>
    </AdminLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminFormField from '@/components/admin/AdminFormField.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminStatCard from '@/components/admin/AdminStatCard.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';

const props = defineProps({
    stats: { type: Object, required: true },
    upcomingVisits: { type: Array, default: () => [] },
    recentVisits: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const range = reactive({ from: props.filters.from || '', to: props.filters.to || '' });
const periodExpanded = ref(Boolean(range.from || range.to));
const activeDateCount = computed(() => Number(Boolean(range.from)) + Number(Boolean(range.to)));
const periodSummary = computed(() => {
    if (range.from && range.to) return `${range.from} – ${range.to}`;
    if (range.from) return `From ${range.from}`;
    if (range.to) return `Through ${range.to}`;
    return 'All dates';
});
const cards = [
    { label: 'Adopted pets', key: 'adoptedPets' },
    { label: 'Pets monitored', key: 'monitoredPets' },
    { label: 'Pending follow-ups', key: 'pending' },
    { label: 'Upcoming follow-ups', key: 'upcoming' },
    { label: 'Overdue follow-ups', key: 'overdue' },
    { label: 'Pets requiring attention', key: 'attention' },
];
const reports = [
    { label: 'Completed visits', key: 'completed' },
    { label: 'Reported alive', key: 'alive' },
    { label: 'Reported deceased', key: 'deceased' },
    { label: 'Reported missing', key: 'missing' },
    { label: 'Medical attention', key: 'medicalAttention' },
];

function applyFilters() {
    periodExpanded.value = false;
    router.get('/admin/post-adoption', range, { preserveState: true, replace: true });
}

function scheduleLabel(visit) {
    return visit.schedule_status === 'Pending' && visit.scheduled_date < new Date().toISOString().slice(0, 10)
        ? 'Overdue'
        : visit.schedule_status === 'Pending' ? 'Upcoming' : visit.schedule_status;
}
</script>

<style scoped>
.dashboard-reports {
    border: 1px solid #e2e8f0;
    border-radius: 0.875rem;
    background: #fff;
    padding: 1rem;
    box-shadow: 0 1px 2px rgb(15 23 42 / 4%);
}

.dashboard-date-filter {
    position: relative;
    z-index: 20;
    display: flex;
    flex: 0 0 auto;
    justify-content: center;
}

.dashboard-period-toggle {
    position: relative;
    display: inline-flex;
    width: 2.75rem;
    height: 2.75rem;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    border-radius: 0.7rem;
    background: #fff;
    color: #475569;
    cursor: pointer;
    transition: border-color 150ms ease, background-color 150ms ease, color 150ms ease, box-shadow 150ms ease;
}

.dashboard-period-toggle:hover,
.dashboard-period-toggle[aria-expanded="true"] {
    border-color: #cbd5e1;
    background: #f8fafc;
    color: #c2410c;
}

.dashboard-period-toggle:focus-visible {
    outline: 3px solid rgb(249 115 22 / 25%);
    outline-offset: 2px;
}

.dashboard-period-icon {
    width: 1.2rem;
    height: 1.2rem;
}

.dashboard-period-indicator {
    position: absolute;
    top: 0.45rem;
    right: 0.45rem;
    width: 0.45rem;
    height: 0.45rem;
    border-radius: 9999px;
    background: #ea580c;
    box-shadow: 0 0 0 2px #fff;
}

.dashboard-period-popover {
    position: absolute;
    top: calc(100% + 0.4rem);
    right: 0;
    z-index: 30;
    width: min(30rem, calc(100vw - 2rem));
    max-width: calc(100vw - 2rem);
    box-sizing: border-box;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    padding: 1rem;
    box-shadow: 0 14px 30px rgb(15 23 42 / 14%);
}

.dashboard-period-fields {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 0.75rem;
}

.dashboard-period-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e2e8f0;
}

.dashboard-section-heading,
.dashboard-panel-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.dashboard-section-heading h3 {
    color: #0f172a;
    font-size: 0.95rem;
    font-weight: 700;
    line-height: 1.35rem;
}

.dashboard-section-heading p {
    margin-top: 0.15rem;
    color: #64748b;
    font-size: 0.8125rem;
    line-height: 1.25rem;
}

.dashboard-section-heading {
    margin-bottom: 0.75rem;
}

.dashboard-stat-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 0.75rem;
}

.dashboard-activity-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 1rem;
}

.dashboard-panel-heading {
    min-height: 6rem;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
    padding: 1rem;
}

.dashboard-panel-kicker {
    margin-bottom: 0.2rem;
    color: #c2410c;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    line-height: 1rem;
    text-transform: uppercase;
}

.dashboard-reports {
    padding: 1rem;
}

.dashboard-report-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 0.65rem;
}

.dashboard-report-item {
    display: flex;
    min-height: 4.25rem;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.625rem;
    background: #f8fafc;
    padding: 0.75rem 0.875rem;
}

.dashboard-report-item p {
    color: #475569;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.045em;
    line-height: 1.1rem;
    text-transform: uppercase;
}

.dashboard-report-item span {
    color: #0f172a;
    font-size: 1.35rem;
    font-weight: 750;
    line-height: 1;
    font-variant-numeric: tabular-nums;
}

@media (min-width: 640px) {
    .dashboard-reports {
        padding: 1.25rem;
    }

    .dashboard-period-fields {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .dashboard-stat-grid,
    .dashboard-report-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .dashboard-panel-heading {
        padding: 1.125rem 1.25rem;
    }
}

@media (min-width: 1024px) {
    .dashboard-stat-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .dashboard-report-grid {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .dashboard-report-item {
        min-height: 5rem;
        flex-direction: column;
        align-items: flex-start;
        justify-content: space-between;
    }
}

@media (min-width: 1280px) {
    .dashboard-activity-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
</style>
