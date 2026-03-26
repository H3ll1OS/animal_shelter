<template>
    <AdminLayout>
        <div class="admin-page-container max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-red-50 via-white to-red-100">
                    <h1 class="text-2xl font-bold text-gray-900">Log Euthanasia</h1>
                    <p class="text-sm text-gray-600 mt-1">Record the details for a pet euthanasia.</p>
                </div>

                <form class="p-6 space-y-6" @submit.prevent="submit">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pet <span class="text-red-500">*</span></label>
                        <select v-model="form.pet_id" class="form-select w-full" :class="{ 'border-red-500': form.errors.pet_id }" required>
                            <option value="">Select a pet</option>
                            <option v-for="pet in pets" :key="pet.id" :value="String(pet.id)">{{ pet.name }} ({{ pet.status || 'N/A' }})</option>
                        </select>
                        <p v-if="form.errors.pet_id" class="text-red-600 text-sm mt-1">{{ form.errors.pet_id }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-red-500">*</span></label><input v-model="form.reason" type="text" required class="form-input w-full" :class="{ 'border-red-500': form.errors.reason }" placeholder="e.g., Severe illness"><p v-if="form.errors.reason" class="text-red-600 text-sm mt-1">{{ form.errors.reason }}</p></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Veterinarian <span class="text-red-500">*</span></label><input v-model="form.veterinarian" type="text" required class="form-input w-full" :class="{ 'border-red-500': form.errors.veterinarian }" placeholder="Dr. Smith"><p v-if="form.errors.veterinarian" class="text-red-600 text-sm mt-1">{{ form.errors.veterinarian }}</p></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Euthanasia Date <span class="text-red-500">*</span></label><input v-model="form.euthanasia_date" type="date" required class="form-input w-full" :class="{ 'border-red-500': form.errors.euthanasia_date }"><p v-if="form.errors.euthanasia_date" class="text-red-600 text-sm mt-1">{{ form.errors.euthanasia_date }}</p></div>
                    </div>

                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Notes</label><textarea v-model="form.notes" rows="3" class="form-textarea w-full" :class="{ 'border-red-500': form.errors.notes }" placeholder="Additional context, procedure notes, or follow-up."></textarea><p v-if="form.errors.notes" class="text-red-600 text-sm mt-1">{{ form.errors.notes }}</p></div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200"><Link href="/admin/health/euthanasia" class="text-sm text-gray-600 hover:text-indigo-600 font-semibold">Cancel</Link><button type="submit" class="inline-flex items-center px-5 py-2.5 rounded-full bg-red-600 text-white font-semibold shadow hover:bg-red-700 transition" :disabled="form.processing">Save Record</button></div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({ pets: { type: Array, default: () => [] } });
const form = useForm({ pet_id: '', reason: '', veterinarian: '', euthanasia_date: '', notes: '' });
function submit() { form.post('/admin/health/euthanasia'); }
</script>


