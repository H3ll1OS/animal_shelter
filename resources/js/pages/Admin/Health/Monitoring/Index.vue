<template>
    <AdminLayout>
        <div class="admin-page-container p-4 sm:p-6 lg:p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold">Pets Requiring Attention</h2>
                <Link href="/admin/health/monitoring/create" class="inline-flex items-center px-4 py-2 rounded-full bg-red-600 text-white font-semibold shadow hover:bg-red-700 transition"><span class="text-lg mr-2">+</span> Add Alert</Link>
            </div>

            <div v-if="flash.success" class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800">{{ flash.success }}</div>

            <template v-if="alerts.data.length">
                <div v-for="alert in alerts.data" :key="alert.id" class="bg-white border border-gray-200 p-4 mb-4 rounded-xl shadow-sm flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <h4 class="font-bold text-gray-900">{{ alert.pet?.name || 'Unknown Pet' }} ({{ alert.pet?.status || 'N/A' }})</h4>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="alert.is_critical ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'">{{ alert.is_critical ? 'Critical' : 'Normal' }}</span>
                        </div>
                        <p class="text-sm text-gray-700 mt-1">Diagnosis: {{ alert.diagnosis || alert.description }} <span class="mx-2 text-gray-400">â€¢</span> Reported: {{ formatDate(alert.created_at) }}</p>
                        <p v-if="alert.veterinarian" class="text-sm text-gray-600">Veterinarian: {{ alert.veterinarian }}</p>
                    </div>
                    <div class="flex items-center gap-2 mt-3 md:mt-0">
                        <Link :href="`/admin/health/monitoring/${alert.id}`" class="px-3 py-1.5 rounded-full text-sm font-semibold border border-gray-200 hover:bg-gray-50">View</Link>
                        <Link :href="`/admin/health/monitoring/${alert.id}/edit`" class="px-3 py-1.5 rounded-full text-sm font-semibold border border-indigo-200 text-indigo-700 hover:bg-indigo-50">Edit</Link>
                        <Link :href="`/admin/health/monitoring/${alert.id}`" method="delete" as="button" class="px-3 py-1.5 rounded-full text-sm font-semibold border border-red-200 text-red-700 hover:bg-red-50" @before="confirmDelete">Delete</Link>
                    </div>
                </div>
            </template>
            <div v-else class="bg-green-50 border-l-4 border-green-400 p-4 mb-4 rounded-lg shadow-sm"><p class="text-sm text-green-700">All health statuses are nominal. No alerts logged.</p></div>

            <div class="mt-8"><PaginationLinks :links="alerts.links" /></div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PaginationLinks from '@/components/common/PaginationLinks.vue';

defineProps({ alerts: { type: Object, required: true } });
const page = usePage();
const flash = computed(() => page.props.flash || {});

function formatDate(v) { return v ? String(v).slice(0, 10) : 'N/A'; }
function confirmDelete(visit) { if (!window.confirm('Delete this alert?')) visit.cancel(); }
</script>


