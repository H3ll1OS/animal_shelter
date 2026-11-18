<template>
    <AdminLayout>
        <main class="mx-auto max-w-7xl space-y-6 p-4 md:p-6">
            <AdminPageHeader
                eyebrow="Post-adoption monitoring"
                title="Monitoring schedules"
                description="Plan, assign, and record follow-ups for adopted pets."
            >
                <template #actions>
                    <Link href="/admin/post-adoption/schedules/create" class="admin-btn admin-btn--primary">New schedule</Link>
                </template>
            </AdminPageHeader>

            <p v-if="success" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">{{ success }}</p>
            <p v-if="error" class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800" role="alert">{{ error }}</p>

            <form class="schedule-filter-form rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" @submit.prevent="filter">
                <div class="schedule-filter-toolbar">
                    <AdminFormField label="Search pet or adopter" class="schedule-search-field">
                        <input v-model="form.search" type="search" class="admin-control" placeholder="Enter a name" autocomplete="off">
                    </AdminFormField>
                    <button
                        type="button"
                        class="admin-btn admin-btn--secondary schedule-filter-toggle"
                        :aria-expanded="filtersExpanded"
                        aria-controls="schedule-filters"
                        @click="filtersExpanded = !filtersExpanded"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10m-7 6h4" />
                        </svg>
                        Filters
                        <span v-if="activeFilterCount" class="inline-flex min-w-5 items-center justify-center rounded-full bg-orange-100 px-1.5 py-0.5 text-xs font-bold text-orange-800">
                            {{ activeFilterCount }}
                        </span>
                        <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': filtersExpanded }" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6" />
                        </svg>
                    </button>
                    <button type="submit" class="admin-btn admin-btn--dark schedule-search-button">Search</button>
                </div>

                <div v-if="filtersExpanded" id="schedule-filters" class="schedule-filter-popover">
                    <p class="schedule-filter-title">Filter schedules</p>
                    <AdminFormField label="Schedule status">
                        <select v-model="form.status" class="admin-control">
                            <option value="">All statuses</option>
                            <option value="Upcoming">Upcoming</option>
                            <option value="Pending">Pending</option>
                            <option value="Completed">Completed</option>
                            <option value="Overdue">Overdue</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </AdminFormField>
                    <div class="schedule-filter-actions">
                        <Link href="/admin/post-adoption/schedules" class="admin-btn admin-btn--secondary">Clear</Link>
                        <button type="submit" class="admin-btn admin-btn--dark">Apply filters</button>
                    </div>
                </div>
            </form>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="schedule-table-wrap overflow-x-auto">
                    <table class="admin-table min-w-[900px]">
                        <thead><tr><th>Pet and adopter</th><th>Scheduled</th><th>Assigned staff</th><th>Method</th><th>Status</th><th><span class="sr-only">Actions</span></th></tr></thead>
                        <tbody>
                            <tr v-for="visit in visits.data" :key="visit.id">
                                <td>
                                    <Link :href="`/admin/post-adoption/pets/${visit.pet_id}`" class="font-semibold text-slate-900 hover:text-orange-700">{{ visit.pet?.name || 'Unknown pet' }}</Link>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ visit.adoption?.full_name || 'Adopter not recorded' }}</p>
                                </td>
                                <td class="whitespace-nowrap">{{ visit.scheduled_date }}</td>
                                <td>{{ visit.assigned_user?.name || 'Unassigned' }}</td>
                                <td>{{ visit.monitoring_method }}</td>
                                <td><AdminStatusBadge :status="scheduleLabel(visit)" /></td>
                                <td><div class="flex justify-end gap-1.5">
                                    <Link :href="`/admin/post-adoption/visits/${visit.id}`" class="admin-btn admin-btn--secondary admin-btn--small">View</Link>
                                    <Link v-if="visit.schedule_status === 'Pending'" :href="`/admin/post-adoption/schedules/${visit.id}/edit`" class="admin-btn admin-btn--secondary admin-btn--small">Edit</Link>
                                    <button v-if="visit.schedule_status === 'Pending'" type="button" class="admin-btn admin-btn--danger admin-btn--small" @click="cancel(visit.id)">Cancel</button>
                                </div></td>
                            </tr>
                            <tr v-if="!visits.data.length">
                                <td colspan="6" class="p-4">
                                    <AdminEmptyState compact title="No schedules found" description="Try changing the filters or create a follow-up schedule.">
                                        <template #actions>
                                            <Link href="/admin/post-adoption/schedules/create" class="admin-btn admin-btn--primary admin-btn--small">Create schedule</Link>
                                        </template>
                                    </AdminEmptyState>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="schedule-cards divide-y divide-slate-200">
                    <article v-for="visit in visits.data" :key="visit.id" class="space-y-3 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <Link :href="`/admin/post-adoption/pets/${visit.pet_id}`" class="font-semibold text-slate-900">{{ visit.pet?.name || 'Unknown pet' }}</Link>
                                <p class="mt-0.5 truncate text-sm text-slate-600">{{ visit.adoption?.full_name || 'Adopter not recorded' }}</p>
                            </div>
                            <AdminStatusBadge :status="scheduleLabel(visit)" />
                        </div>
                        <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-sm">
                            <div><dt class="text-xs font-medium text-slate-500">Scheduled</dt><dd class="mt-0.5 text-slate-800">{{ visit.scheduled_date }}</dd></div>
                            <div><dt class="text-xs font-medium text-slate-500">Staff</dt><dd class="mt-0.5 text-slate-800">{{ visit.assigned_user?.name || 'Unassigned' }}</dd></div>
                            <div><dt class="text-xs font-medium text-slate-500">Method</dt><dd class="mt-0.5 text-slate-800">{{ visit.monitoring_method }}</dd></div>
                        </dl>
                        <div class="flex flex-wrap gap-2">
                            <Link :href="`/admin/post-adoption/visits/${visit.id}`" class="admin-btn admin-btn--secondary admin-btn--small">View</Link>
                            <Link v-if="visit.schedule_status === 'Pending'" :href="`/admin/post-adoption/schedules/${visit.id}/edit`" class="admin-btn admin-btn--secondary admin-btn--small">Edit</Link>
                            <button v-if="visit.schedule_status === 'Pending'" type="button" class="admin-btn admin-btn--danger admin-btn--small" @click="cancel(visit.id)">Cancel</button>
                        </div>
                    </article>
                    <div v-if="!visits.data.length" class="p-4">
                        <AdminEmptyState compact title="No schedules found" description="Try changing the filters or create a follow-up schedule.">
                            <template #actions>
                                <Link href="/admin/post-adoption/schedules/create" class="admin-btn admin-btn--primary admin-btn--small">Create schedule</Link>
                            </template>
                        </AdminEmptyState>
                    </div>
                </div>
            </section>

            <PaginationLinks :links="visits.links" />
        </main>
    </AdminLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminFormField from '@/components/admin/AdminFormField.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import PaginationLinks from '@/components/common/PaginationLinks.vue';

const props = defineProps({ visits: { type: Object, required: true }, filters: { type: Object, default: () => ({}) } });
const page = usePage();
const success = computed(() => page.props.flash?.success);
const error = computed(() => page.props.flash?.error);
const form = reactive({ search: props.filters.search || '', status: props.filters.status || '' });
const filtersExpanded = ref(Boolean(form.status));
const activeFilterCount = computed(() => Number(Boolean(form.search.trim())) + Number(Boolean(form.status)));

function filter() {
    filtersExpanded.value = false;
    router.get('/admin/post-adoption/schedules', form, { preserveState: true, replace: true });
}

function cancel(id) {
    if (window.confirm('Cancel this follow-up schedule?')) router.post(`/admin/post-adoption/schedules/${id}/cancel`);
}

function scheduleLabel(visit) {
    return visit.schedule_status === 'Pending' && visit.scheduled_date < new Date().toISOString().slice(0, 10)
        ? 'Overdue'
        : visit.schedule_status === 'Pending' ? 'Upcoming' : visit.schedule_status;
}
</script>

<style scoped>
.schedule-filter-form {
    position: relative;
    z-index: 10;
}

.schedule-filter-toolbar {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 0.75rem;
    align-items: end;
}

.schedule-search-field {
    min-width: 0;
}

.schedule-filter-toggle,
.schedule-search-button {
    width: 100%;
}

.schedule-filter-toggle {
    justify-content: space-between;
    background: #f8fafc;
}

.schedule-filter-popover {
    position: absolute;
    top: calc(100% - 0.25rem);
    right: 1.25rem;
    z-index: 30;
    width: min(22rem, calc(100% - 2.5rem));
    padding: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    box-shadow: 0 14px 30px rgb(15 23 42 / 14%);
}

.schedule-filter-title {
    margin-bottom: 0.75rem;
    color: #0f172a;
    font-size: 0.9rem;
    font-weight: 700;
}

.schedule-filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e2e8f0;
}

@media (min-width: 640px) {
    .schedule-filter-toolbar {
        grid-template-columns: minmax(0, 1fr) auto auto;
    }

    .schedule-filter-toggle,
    .schedule-search-button {
        width: auto;
    }
}

.schedule-table-wrap {
    display: none;
}

.schedule-cards {
    display: block;
}

@media (min-width: 1024px) {
    .schedule-table-wrap {
        display: block;
    }

    .schedule-cards {
        display: none;
    }
}
</style>
