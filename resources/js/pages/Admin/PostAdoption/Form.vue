<template>
    <AdminLayout>
        <main class="mx-auto max-w-4xl space-y-6 p-4 md:p-6">
            <AdminPageHeader
                eyebrow="Monitoring schedule"
                :title="visit ? 'Edit follow-up schedule' : 'Schedule a follow-up'"
                description="Choose an eligible adoption, then set the date, staff member, and monitoring method."
            >
                <template #actions>
                    <Link href="/admin/post-adoption/schedules" class="admin-btn admin-btn--secondary">Back to schedules</Link>
                </template>
            </AdminPageHeader>

            <form class="space-y-5" @submit.prevent="submit">
                <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Adoption</h3>
                        <p class="mt-0.5 text-sm text-slate-600">Only approved adoptions with pets marked Adopted are available.</p>
                    </div>

                    <AdminFormField label="Adopted pet and adopter" required :error="form.errors.adoption_id">
                        <select v-model="form.adoption_id" class="admin-control" required>
                            <option value="">Select an eligible adoption</option>
                            <option v-for="adoption in adoptions" :key="adoption.id" :value="adoption.id">
                                {{ adoption.pet?.name }} — {{ adoption.full_name }} (application #{{ adoption.id }})
                            </option>
                        </select>
                    </AdminFormField>

                    <div v-if="selected" class="grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2">
                        <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pet</p><p class="mt-1 font-semibold text-slate-900">{{ selected.pet.name }}</p><p class="text-sm text-slate-600">{{ selected.pet.species }} · {{ selected.pet.breed || 'Breed not recorded' }}</p></div>
                        <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Adopter</p><p class="mt-1 font-semibold text-slate-900">{{ selected.full_name }}</p><p class="break-words text-sm text-slate-600">{{ selected.phone }} · {{ selected.email }}</p></div>
                        <div class="sm:col-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Adoption reference</p><p class="mt-1 text-sm text-slate-800">Application #{{ selected.id }}</p></div>
                    </div>
                    <p v-if="!adoptions.length" class="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-900">There are no eligible adopted pets to schedule right now.</p>
                </section>

                <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Follow-up details</h3>
                        <p class="mt-0.5 text-sm text-slate-600">You can update an uncompleted schedule later.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <AdminFormField label="Scheduled date" required :error="form.errors.scheduled_date">
                            <input v-model="form.scheduled_date" type="date" class="admin-control" required>
                        </AdminFormField>
                        <AdminFormField label="Assigned staff" :error="form.errors.assigned_user_id">
                            <select v-model="form.assigned_user_id" class="admin-control">
                                <option value="">Unassigned</option>
                                <option v-for="user in staff" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                        </AdminFormField>
                        <AdminFormField label="Monitoring method" required :error="form.errors.monitoring_method">
                            <select v-model="form.monitoring_method" class="admin-control" required>
                                <option>Home Visit</option><option>Phone Call</option><option>Video Call</option><option>Other</option>
                            </select>
                        </AdminFormField>
                        <AdminFormField label="Purpose" :error="form.errors.purpose">
                            <input v-model="form.purpose" type="text" class="admin-control" maxlength="255" placeholder="Reason for this follow-up">
                        </AdminFormField>
                        <AdminFormField label="Additional notes" class="sm:col-span-2" :error="form.errors.notes">
                            <textarea v-model="form.notes" class="admin-control admin-control--textarea" rows="3" placeholder="Optional context for the assigned staff member" />
                        </AdminFormField>
                    </div>
                </section>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <Link href="/admin/post-adoption/schedules" class="admin-btn admin-btn--secondary">Cancel</Link>
                    <button type="submit" class="admin-btn admin-btn--primary" :disabled="form.processing || !adoptions.length">
                        {{ form.processing ? 'Saving…' : visit ? 'Save changes' : 'Create schedule' }}
                    </button>
                </div>
            </form>
        </main>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AdminFormField from '@/components/admin/AdminFormField.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';

const props = defineProps({ visit: { type: Object, default: null }, adoptions: { type: Array, default: () => [] }, staff: { type: Array, default: () => [] } });
const form = useForm({
    adoption_id: props.visit?.adoption_id || '',
    scheduled_date: props.visit?.scheduled_date || '',
    assigned_user_id: props.visit?.assigned_user_id || '',
    monitoring_method: props.visit?.monitoring_method || 'Home Visit',
    purpose: props.visit?.purpose || '',
    notes: props.visit?.notes || '',
});
const selected = computed(() => props.adoptions.find((adoption) => String(adoption.id) === String(form.adoption_id)));

function submit() {
    if (props.visit) form.put(`/admin/post-adoption/schedules/${props.visit.id}`);
    else form.post('/admin/post-adoption/schedules');
}
</script>
