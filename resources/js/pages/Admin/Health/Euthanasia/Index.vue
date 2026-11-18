<template>
    <AdminLayout>
        <main class="euthanasia-page mx-auto max-w-7xl p-4 md:p-6">
            <AdminPageHeader
                eyebrow="Health records"
                title="Euthanasia logs"
                description="Review previously recorded euthanasia events."
            >
                <template #actions>
                    <Link href="/admin/health/euthanasia/create" class="admin-btn admin-btn--primary">
                        <span aria-hidden="true">+</span>
                        Add record
                    </Link>
                </template>
            </AdminPageHeader>

            <p v-if="flash.success" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">
                {{ flash.success }}
            </p>
            <p v-if="flash.error" class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800" role="alert">
                {{ flash.error }}
            </p>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <header class="flex items-center justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-5">
                    <div>
                        <h3 class="font-semibold text-slate-900">Euthanasia history</h3>
                        <p class="mt-1 text-sm text-slate-600">Previously recorded euthanasia events.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        {{ logs.total }} {{ logs.total === 1 ? 'record' : 'records' }}
                    </span>
                </header>

                <div class="overflow-x-auto">
                    <table class="admin-table min-w-[820px]">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Pet</th>
                                <th>Reason</th>
                                <th>Veterinarian</th>
                                <th><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in logs.data" :key="log.id">
                                <td class="whitespace-nowrap font-medium text-slate-800">{{ formatDate(log.euthanasia_date) }}</td>
                                <td class="font-semibold text-slate-900">{{ log.pet?.name || 'Record missing' }}</td>
                                <td class="max-w-sm text-slate-700">{{ log.reason }}</td>
                                <td>{{ log.veterinarian || 'Not recorded' }}</td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <Link :href="`/admin/health/euthanasia/${log.id}`" class="admin-btn admin-btn--secondary admin-btn--small">View</Link>
                                        <Link :href="`/admin/health/euthanasia/${log.id}/edit`" class="admin-btn admin-btn--secondary admin-btn--small">Edit</Link>
                                        <Link
                                            :href="`/admin/health/euthanasia/${log.id}`"
                                            method="delete"
                                            as="button"
                                            class="admin-btn admin-btn--danger admin-btn--small"
                                            @before="confirmDelete"
                                        >
                                            Delete
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!logs.data.length">
                                <td colspan="5" class="p-4">
                                    <AdminEmptyState compact title="No euthanasia records yet" description="Records you add will appear here." />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <span>Showing {{ logs.from || 0 }}–{{ logs.to || 0 }} of {{ logs.total }} records</span>
                    <PaginationLinks :links="logs.links" />
                </div>
            </section>
        </main>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import PaginationLinks from '@/components/common/PaginationLinks.vue';

defineProps({ logs: { type: Object, required: true } });

const page = usePage();
const flash = computed(() => page.props.flash || {});

function formatDate(value) {
    return value ? String(value).slice(0, 10) : 'N/A';
}

function confirmDelete(visit) {
    if (!window.confirm('Delete this record?')) visit.cancel();
}
</script>

<style scoped>
.euthanasia-page {
    display: flex;
    width: 100%;
    flex-direction: column;
    gap: 1.25rem;
}
</style>
