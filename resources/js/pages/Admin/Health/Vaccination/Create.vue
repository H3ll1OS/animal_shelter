<template>
    <AdminLayout>
        <div class="admin-page-container p-4 sm:p-6 lg:p-8">
            <div class="bg-white shadow-xl rounded-xl p-6 lg:p-8 max-w-2xl mx-auto">
                <form @submit.prevent="submit">
                    <div class="space-y-6">
                        <div>
                            <label for="pet_id" class="block text-sm font-medium text-gray-700">Select Pet</label>
                            <select id="pet_id" v-model="form.pet_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">-- Select an Animal --</option>
                                <option v-for="pet in pets" :key="pet.id" :value="String(pet.id)">{{ pet.name }}</option>
                            </select>
                            <p v-if="form.errors.pet_id" class="text-red-500 text-xs mt-1">{{ form.errors.pet_id }}</p>
                        </div>

                        <div>
                            <label for="vaccine_type" class="block text-sm font-medium text-gray-700">Vaccine Type</label>
                            <input id="vaccine_type" v-model="form.vaccine_type" type="text" required placeholder="e.g., Rabies Booster" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            <p v-if="form.errors.vaccine_type" class="text-red-500 text-xs mt-1">{{ form.errors.vaccine_type }}</p>
                        </div>

                        <div>
                            <label for="next_due_date" class="block text-sm font-medium text-gray-700">Next Due Date</label>
                            <input id="next_due_date" v-model="form.next_due_date" type="date" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            <p v-if="form.errors.next_due_date" class="text-red-500 text-xs mt-1">{{ form.errors.next_due_date }}</p>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                            <textarea id="notes" v-model="form.notes" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                            <p v-if="form.errors.notes" class="text-red-500 text-xs mt-1">{{ form.errors.notes }}</p>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700" :disabled="form.processing">Schedule Vaccination</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({ pets: { type: Array, default: () => [] } });

const form = useForm({ pet_id: '', vaccine_type: '', next_due_date: '', notes: '' });
function submit() { form.post('/admin/health/vaccination-schedule'); }
</script>


