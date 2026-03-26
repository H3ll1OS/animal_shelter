<template>
    <AdminLayout>
        <div class="admin-page-container p-4 sm:p-6 lg:p-8">
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-4">Vaccination Schedule Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Pet Information</h3>
                            <p><strong>Name:</strong> {{ schedule.pet?.name || 'N/A' }}</p>
                            <p><strong>ID:</strong> {{ schedule.pet?.id || 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Vaccination Details</h3>
                            <p><strong>Vaccine Type:</strong> {{ schedule.vaccine_type }}</p>
                            <p><strong>Next Due Date:</strong> {{ formatDate(schedule.next_due_date) }}</p>
                            <p><strong>Last Given Date:</strong> {{ schedule.last_given_date ? formatDate(schedule.last_given_date) : 'Not given' }}</p>
                            <p><strong>Status:</strong> <span :class="schedule.is_completed ? 'text-green-600 font-medium' : 'text-orange-600 font-medium'">{{ schedule.is_completed ? 'Completed' : 'Pending' }}</span></p>
                        </div>
                    </div>

                    <div class="mt-6"><h3 class="text-lg font-semibold mb-2">Notes</h3><p>{{ schedule.notes || 'No notes available.' }}</p></div>

                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-2">Actions</h3>
                        <Link v-if="!schedule.is_completed" :href="`/admin/health/vaccination-schedule/${schedule.id}/complete`" method="post" as="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">Mark Complete</Link>
                        <Link href="/admin/health/vaccination-schedule" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 ml-2">Back to List</Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({ schedule: { type: Object, required: true } });
function formatDate(v) { return v ? String(v).slice(0, 10) : 'N/A'; }
</script>


