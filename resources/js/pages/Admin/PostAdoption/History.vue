<template>
    <AdminLayout>
        <main class="mx-auto max-w-7xl space-y-6 p-4 md:p-6">
            <AdminPageHeader
                eyebrow="Post-adoption monitoring"
                title="Monitoring history"
                description="Review completed welfare checks, findings, and ownership updates."
            />

            <form class="history-filter-form rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" @submit.prevent="filter">
                <div class="history-filter-toolbar">
                    <AdminFormField label="Search by adopter" class="history-search-field">
                        <input v-model="form.adopter" type="search" class="admin-control" placeholder="Enter an adopter name" autocomplete="off">
                    </AdminFormField>
                    <button
                        type="button"
                        class="admin-btn admin-btn--secondary history-filter-toggle"
                        :aria-expanded="filtersExpanded"
                        aria-controls="history-filters"
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
                    <button type="submit" class="admin-btn admin-btn--dark history-search-button">Search</button>
                </div>

                <div v-if="filtersExpanded" id="history-filters" class="history-filter-popover">
                    <p class="history-filter-title">Filter monitoring history</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <AdminFormField label="Pet">
                            <select v-model="form.pet" class="admin-control"><option value="">All pets</option><option v-for="pet in pets" :key="pet.id" :value="pet.id">{{ pet.name }}</option></select>
                        </AdminFormField>
                        <AdminFormField label="Pet status">
                            <select v-model="form.status" class="admin-control"><option value="">All statuses</option><option>Alive</option><option>Deceased</option><option>Missing</option><option>Unknown / Unable to Verify</option></select>
                        </AdminFormField>
                        <AdminFormField label="Health condition">
                            <select v-model="form.health" class="admin-control"><option value="">All conditions</option><option>Healthy</option><option>Needs Medical Attention</option><option>Critical Condition</option><option>Not Assessed</option></select>
                        </AdminFormField>
                        <AdminFormField label="Ownership">
                            <select v-model="form.ownership" class="admin-control"><option value="">All ownership statuses</option><option>With Original Adopter</option><option>Rehomed / Transferred</option><option>Returned to Shelter</option><option>Cannot Be Verified</option></select>
                        </AdminFormField>
                        <AdminFormField label="From date">
                            <input v-model="form.from" type="date" class="admin-control">
                        </AdminFormField>
                        <AdminFormField label="To date">
                            <input v-model="form.to" type="date" class="admin-control">
                        </AdminFormField>
                    </div>
                    <div class="history-filter-actions">
                        <Link href="/admin/post-adoption/history" class="admin-btn admin-btn--secondary">Clear</Link>
                        <button type="submit" class="admin-btn admin-btn--dark">Apply filters</button>
                    </div>
                </div>
            </form>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="history-table-wrap overflow-x-auto">
                    <table class="admin-table min-w-[980px]">
                        <thead><tr><th>Pet</th><th>Adopter</th><th>Follow-up</th><th>Pet and health</th><th>Living and ownership</th><th>Staff</th><th><span class="sr-only">Details</span></th></tr></thead>
                        <tbody>
                            <tr v-for="visit in visits.data" :key="visit.id">
                                <td><Link :href="`/admin/post-adoption/pets/${visit.pet_id}`" class="font-semibold text-slate-900 hover:text-orange-700">{{ visit.pet?.name || 'Unknown pet' }}</Link></td>
                                <td>{{ visit.adoption?.full_name || 'Not recorded' }}</td>
                                <td class="whitespace-nowrap">{{ visit.actual_visit_date }}</td>
                                <td><div class="flex flex-wrap gap-1.5"><AdminStatusBadge :status="visit.pet_status" /><AdminStatusBadge :status="visit.health_condition" /></div></td>
                                <td><p>{{ visit.living_condition || 'Not assessed' }}</p><p class="mt-0.5 text-xs text-slate-500">{{ visit.ownership_status || 'Not recorded' }}</p></td>
                                <td>{{ visit.assigned_user?.name || 'Unassigned' }}</td>
                                <td><Link :href="`/admin/post-adoption/visits/${visit.id}`" class="admin-btn admin-btn--secondary admin-btn--small">Details</Link></td>
                            </tr>
                            <tr v-if="!visits.data.length"><td colspan="7" class="p-4"><AdminEmptyState title="No monitoring records found" description="Completed visits will appear here. Adjust the filters to broaden your search." /></td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="history-cards divide-y divide-slate-200">
                    <article v-for="visit in visits.data" :key="visit.id" class="space-y-3 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <Link :href="`/admin/post-adoption/pets/${visit.pet_id}`" class="font-semibold text-slate-900">{{ visit.pet?.name || 'Unknown pet' }}</Link>
                                <p class="mt-0.5 truncate text-sm text-slate-600">{{ visit.adoption?.full_name || 'Not recorded' }}</p>
                            </div>
                            <Link :href="`/admin/post-adoption/visits/${visit.id}`" class="admin-btn admin-btn--secondary admin-btn--small">Details</Link>
                        </div>
                        <div class="flex flex-wrap gap-1.5"><AdminStatusBadge :status="visit.pet_status" /><AdminStatusBadge :status="visit.health_condition" /></div>
                        <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-sm">
                            <div><dt class="text-xs font-medium text-slate-500">Follow-up</dt><dd class="mt-0.5">{{ visit.actual_visit_date }}</dd></div>
                            <div><dt class="text-xs font-medium text-slate-500">Staff</dt><dd class="mt-0.5">{{ visit.assigned_user?.name || 'Unassigned' }}</dd></div>
                            <div><dt class="text-xs font-medium text-slate-500">Living condition</dt><dd class="mt-0.5">{{ visit.living_condition || 'Not assessed' }}</dd></div>
                            <div><dt class="text-xs font-medium text-slate-500">Ownership</dt><dd class="mt-0.5">{{ visit.ownership_status || 'Not recorded' }}</dd></div>
                        </dl>
                    </article>
                    <div v-if="!visits.data.length" class="p-4">
                        <AdminEmptyState compact title="No monitoring records found" description="Completed visits will appear here. Adjust the filters to broaden your search.">
                            <template #actions>
                                <Link href="/admin/post-adoption/history" class="admin-btn admin-btn--secondary admin-btn--small">Clear filters</Link>
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
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminFormField from '@/components/admin/AdminFormField.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import PaginationLinks from '@/components/common/PaginationLinks.vue';

const props = defineProps({ visits: { type: Object, required: true }, filters: { type: Object, default: () => ({}) }, pets: { type: Array, default: () => [] } });
const form = reactive({
    pet: props.filters.pet || '',
    adopter: props.filters.adopter || '',
    status: props.filters.status || '',
    health: props.filters.health || '',
    ownership: props.filters.ownership || '',
    from: props.filters.from || '',
    to: props.filters.to || '',
});
const filtersExpanded = ref(Boolean(form.pet || form.status || form.health || form.ownership || form.from || form.to));
const activeFilterCount = computed(() => [
    form.pet,
    form.adopter.trim(),
    form.status,
    form.health,
    form.ownership,
    form.from,
    form.to,
].filter(Boolean).length);

function filter() {
    filtersExpanded.value = false;
    router.get('/admin/post-adoption/history', form, { preserveState: true, replace: true });
}
</script>

<style scoped>
.history-filter-form {
    position: relative;
    z-index: 10;
}

.history-filter-toolbar {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 0.75rem;
    align-items: end;
}

.history-search-field {
    min-width: 0;
}

.history-filter-toggle,
.history-search-button {
    width: 100%;
}

.history-filter-toggle {
    justify-content: space-between;
    background: #f8fafc;
}

.history-filter-popover {
    position: absolute;
    top: calc(100% - 0.25rem);
    right: 1.25rem;
    z-index: 30;
    width: min(38rem, calc(100% - 2.5rem));
    padding: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    box-shadow: 0 14px 30px rgb(15 23 42 / 14%);
}

.history-filter-title {
    margin-bottom: 0.75rem;
    color: #0f172a;
    font-size: 0.9rem;
    font-weight: 700;
}

.history-filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e2e8f0;
}

.history-table-wrap {
    display: none;
}

.history-cards {
    display: block;
}

@media (min-width: 640px) {
    .history-filter-toolbar {
        grid-template-columns: minmax(0, 1fr) auto auto;
    }

    .history-filter-toggle,
    .history-search-button {
        width: auto;
    }
}

@media (min-width: 1024px) {
    .history-table-wrap {
        display: block;
    }

    .history-cards {
        display: none;
    }
}
</style>
