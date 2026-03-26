<template>
    <AdminLayout>
        <div class="admin-page-container p-4 sm:p-6 lg:p-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-semibold">Euthanasia Record History</h2>
                <Link href="/admin/health/euthanasia/create" class="inline-flex items-center px-4 py-2 rounded-full bg-red-600 text-white font-semibold shadow hover:bg-red-700 transition"><span class="text-lg mr-2">+</span> Add Record</Link>
            </div>

            <div v-if="flash.success" class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800">{{ flash.success }}</div>

            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pet Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Veterinarian</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="log in logs.data" :key="log.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatDate(log.euthanasia_date) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ log.pet?.name || 'N/A (Record Missing)' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-700 font-medium">{{ log.reason }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ log.veterinarian || 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex items-center justify-center gap-2">
                                    <Link :href="`/admin/health/euthanasia/${log.id}`" class="px-3 py-1.5 rounded-full text-xs font-semibold border border-gray-200 hover:bg-gray-50">View</Link>
                                    <Link :href="`/admin/health/euthanasia/${log.id}/edit`" class="px-3 py-1.5 rounded-full text-xs font-semibold border border-indigo-200 text-indigo-700 hover:bg-indigo-50">Edit</Link>
                                    <Link :href="`/admin/health/euthanasia/${log.id}`" method="delete" as="button" class="px-3 py-1.5 rounded-full text-xs font-semibold border border-red-200 text-red-700 hover:bg-red-50" @before="confirmDelete">Delete</Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="logs.data.length === 0"><td colspan="5" class="px-6 py-4 text-center text-gray-500">No euthanasia records found.</td></tr>
                    </tbody>
                </table>
                <div class="p-4"><PaginationLinks :links="logs.links" /></div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PaginationLinks from '@/components/common/PaginationLinks.vue';

defineProps({ logs: { type: Object, required: true } });
const page = usePage();
const flash = computed(() => page.props.flash || {});

function formatDate(v) { return v ? String(v).slice(0, 10) : 'N/A'; }
function confirmDelete(visit) { if (!window.confirm('Delete this record?')) visit.cancel(); }
</script>


