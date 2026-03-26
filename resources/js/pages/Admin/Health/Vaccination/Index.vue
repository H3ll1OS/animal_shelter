<template>
    <AdminLayout>
        <div>
            <div v-if="flash.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert"><span class="block sm:inline">{{ flash.success }}</span></div>

            <div class="admin-page-container p-4 sm:p-6 lg:p-8">
                <div class="flex justify-end mb-1 px-8">
                    <Link href="/admin/health/vaccination-schedule/create" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">+ Add New Schedule</Link>
                </div>

                <div class="p-4 sm:p-6 lg:p-8">
                    <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pet Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vaccine Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="schedule in schedules.data" :key="schedule.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ schedule.pet?.name || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ schedule.vaccine_type }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="isPast(schedule.next_due_date) ? 'text-red-600 font-bold' : 'text-orange-500'">{{ formatDate(schedule.next_due_date) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <template v-if="!schedule.is_completed">
                                            <Link :href="`/admin/health/vaccination-schedule/${schedule.id}/complete`" method="post" as="button" class="text-green-600 hover:text-green-800 font-medium transition-colors">Mark Complete</Link>
                                        </template>
                                        <template v-else>
                                            <span class="text-green-600 font-medium">Completed</span>
                                        </template>

                                        <Link :href="`/admin/health/vaccination-schedule/${schedule.id}`" method="delete" as="button" class="text-red-600 hover:text-red-900 font-medium transition-colors ml-3" @before="confirmDelete">Delete</Link>
                                    </td>
                                </tr>
                                <tr v-if="schedules.data.length === 0"><td colspan="4" class="px-6 py-4 text-center text-gray-500">No urgent vaccinations scheduled for the next 30 days.</td></tr>
                            </tbody>
                        </table>
                        <div class="p-4"><PaginationLinks :links="schedules.links" /></div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PaginationLinks from '@/components/common/PaginationLinks.vue';

defineProps({ schedules: { type: Object, required: true } });
const page = usePage();
const flash = computed(() => page.props.flash || {});

function formatDate(v) { return v ? String(v).slice(0, 10) : 'N/A'; }
function isPast(v) { return v ? new Date(v) < new Date(new Date().toDateString()) : false; }
function confirmDelete(visit) { if (!window.confirm('Are you sure you want to delete this schedule? This action is reversible.')) visit.cancel(); }
</script>


