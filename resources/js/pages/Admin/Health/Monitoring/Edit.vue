<template>
    <AdminLayout>
        <div class="admin-page-container max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-indigo-50 via-white to-red-50">
                    <h1 class="text-2xl font-bold text-gray-900">Edit Alert</h1>
                    <p class="text-sm text-gray-600 mt-1">Update details for {{ alert.pet?.name || 'this pet' }}.</p>
                </div>

                <form class="p-6 space-y-6" @submit.prevent="submit">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pet <span class="text-red-500">*</span></label>
                        <select v-model="form.pet_id" class="form-select w-full" :class="{ 'border-red-500': form.errors.pet_id }" required>
                            <option v-for="pet in pets" :key="pet.id" :value="String(pet.id)">{{ pet.name }} ({{ pet.status || 'N/A' }})</option>
                        </select>
                        <p v-if="form.errors.pet_id" class="text-red-600 text-sm mt-1">{{ form.errors.pet_id }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Diagnosis <span class="text-red-500">*</span></label><input v-model="form.diagnosis" type="text" required class="form-input w-full" :class="{ 'border-red-500': form.errors.diagnosis }"><p v-if="form.errors.diagnosis" class="text-red-600 text-sm mt-1">{{ form.errors.diagnosis }}</p></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Veterinarian <span class="text-red-500">*</span></label><input v-model="form.veterinarian" type="text" required class="form-input w-full" :class="{ 'border-red-500': form.errors.veterinarian }"><p v-if="form.errors.veterinarian" class="text-red-600 text-sm mt-1">{{ form.errors.veterinarian }}</p></div>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Description</label><textarea v-model="form.description" rows="3" class="form-textarea w-full" :class="{ 'border-red-500': form.errors.description }"></textarea><p v-if="form.errors.description" class="text-red-600 text-sm mt-1">{{ form.errors.description }}</p></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Notes</label><textarea v-model="form.notes" rows="2" class="form-textarea w-full" :class="{ 'border-red-500': form.errors.notes }"></textarea><p v-if="form.errors.notes" class="text-red-600 text-sm mt-1">{{ form.errors.notes }}</p></div>
                    <div class="flex items-center gap-2"><input id="is_critical" v-model="form.is_critical" type="checkbox"><label for="is_critical" class="text-sm text-gray-700 font-medium">Mark as critical</label></div>
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200"><Link :href="`/admin/health/monitoring/${alert.id}`" class="text-sm text-gray-600 hover:text-indigo-600 font-semibold">Cancel</Link><button type="submit" class="inline-flex items-center px-5 py-2.5 rounded-full bg-indigo-600 text-white font-semibold shadow hover:bg-indigo-700 transition" :disabled="form.processing">Save Changes</button></div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({ alert: { type: Object, required: true }, pets: { type: Array, default: () => [] } });

const form = useForm({
    pet_id: String(props.alert.pet_id || ''), diagnosis: props.alert.diagnosis || '', description: props.alert.description || '',
    veterinarian: props.alert.veterinarian || '', notes: props.alert.notes || '', is_critical: !!props.alert.is_critical,
});

function submit() { form.put(`/admin/health/monitoring/${props.alert.id}`); }
</script>


