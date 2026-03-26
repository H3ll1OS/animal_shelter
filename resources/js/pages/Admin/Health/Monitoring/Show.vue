<template>
    <AdminLayout>
        <div class="admin-page-container max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
            <div v-if="flash.success" class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800">{{ flash.success }}</div>

            <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-6 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Pet</p>
                        <h2 class="text-2xl font-bold text-gray-900">{{ alert.pet?.name || 'Unknown Pet' }}</h2>
                        <p class="text-sm text-gray-600 mt-1">Reported: {{ formatDateLong(alert.created_at) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold border" :class="alert.is_critical ? 'bg-red-100 text-red-700 border-red-200' : 'bg-gray-100 text-gray-700 border-gray-200'">{{ alert.is_critical ? 'Critical' : 'Info' }}</span>
                        <Link :href="`/admin/health/monitoring/${alert.id}/edit`" class="inline-flex items-center px-3 py-2 rounded-full bg-indigo-600 text-white text-sm font-semibold shadow hover:bg-indigo-700 transition">Edit</Link>
                        <Link :href="`/admin/health/monitoring/${alert.id}`" method="delete" as="button" class="inline-flex items-center px-3 py-2 rounded-full bg-red-600 text-white text-sm font-semibold shadow hover:bg-red-700 transition" @before="confirmDelete">Delete</Link>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div><p class="text-sm text-gray-500">Diagnosis</p><p class="text-lg font-semibold text-gray-900">{{ alert.diagnosis }}</p></div>
                    <div v-if="alert.description"><p class="text-sm text-gray-500">Description</p><p class="text-gray-800 leading-relaxed">{{ alert.description }}</p></div>
                    <div v-if="alert.veterinarian"><p class="text-sm text-gray-500">Veterinarian</p><p class="text-gray-800">{{ alert.veterinarian }}</p></div>
                    <div v-if="alert.notes"><p class="text-sm text-gray-500">Notes</p><p class="text-gray-800 leading-relaxed">{{ alert.notes }}</p></div>
                </div>
            </div>

            <div class="mt-4 text-center"><Link href="/admin/health/monitoring" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">? Back to monitoring</Link></div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({ alert: { type: Object, required: true } });
const page = usePage();
const flash = computed(() => page.props.flash || {});

function formatDateLong(value) { return value ? new Date(value).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : 'N/A'; }
function confirmDelete(visit) { if (!window.confirm('Delete this alert?')) visit.cancel(); }
</script>


