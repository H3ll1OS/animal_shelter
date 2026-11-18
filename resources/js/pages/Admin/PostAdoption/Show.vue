<template>
    <AdminLayout>
        <main class="mx-auto max-w-5xl space-y-6 p-4 md:p-6">
            <AdminPageHeader
                eyebrow="Monitoring activity"
                :title="visit.pet?.name || `Visit #${visit.id}`"
                :description="`Activity #${visit.id} · Scheduled ${visit.scheduled_date}`"
            >
                <template #actions>
                    <AdminStatusBadge :status="visit.schedule_status" />
                    <Link :href="`/admin/post-adoption/pets/${visit.pet_id}`" class="admin-btn admin-btn--secondary">Pet profile</Link>
                </template>
            </AdminPageHeader>

            <p v-if="success" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">{{ success }}</p>

            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <h3 class="text-base font-bold text-slate-900">Adoption and schedule</h3>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Adopter</dt><dd class="mt-1 font-medium text-slate-900">{{ visit.adoption?.full_name || 'Not recorded' }}</dd><dd class="mt-0.5 break-words text-sm text-slate-600">{{ visit.adoption?.phone }}<span v-if="visit.adoption?.email"> · {{ visit.adoption.email }}</span></dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Adoption reference</dt><dd class="mt-1 font-medium text-slate-900">Application #{{ visit.adoption_id }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Follow-up date</dt><dd class="mt-1 font-medium text-slate-900">{{ visit.actual_visit_date || visit.scheduled_date }}</dd><dd v-if="visit.actual_visit_date" class="mt-0.5 text-sm text-slate-600">Scheduled {{ visit.scheduled_date }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Method</dt><dd class="mt-1 text-slate-800">{{ visit.monitoring_method }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Assigned staff</dt><dd class="mt-1 text-slate-800">{{ visit.assigned_user?.name || 'Unassigned' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Completed by</dt><dd class="mt-1 text-slate-800">{{ visit.completer?.name || 'Not completed' }}</dd></div>
                </dl>
            </section>

            <section v-if="visit.schedule_status === 'Completed'" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <h3 class="text-base font-bold text-slate-900">Welfare assessment</h3>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pet status</dt><dd class="mt-1"><AdminStatusBadge :status="visit.pet_status" /></dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Health</dt><dd class="mt-1"><AdminStatusBadge :status="visit.health_condition" /></dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Living condition</dt><dd class="mt-1"><AdminStatusBadge :status="visit.living_condition" /></dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ownership</dt><dd class="mt-1"><AdminStatusBadge :status="visit.ownership_status" /></dd></div>
                </dl>
                <div class="mt-5 grid gap-4 border-t border-slate-200 pt-4 sm:grid-cols-2">
                    <div class="sm:col-span-2"><h4 class="text-sm font-semibold text-slate-700">Findings</h4><p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-700">{{ visit.findings || 'No findings recorded.' }}</p></div>
                    <div><h4 class="text-sm font-semibold text-slate-700">Recommendations</h4><p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-700">{{ visit.recommendations || 'None recorded.' }}</p></div>
                    <div><h4 class="text-sm font-semibold text-slate-700">Additional remarks</h4><p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-700">{{ visit.notes || 'None recorded.' }}</p></div>
                    <div v-if="visit.next_follow_up_date" class="sm:col-span-2"><h4 class="text-sm font-semibold text-slate-700">Next follow-up</h4><p class="mt-1 text-sm text-slate-700">{{ visit.next_follow_up_date }}</p></div>
                </div>
            </section>

            <section v-if="visit.attachments?.length" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="mb-4"><h3 class="text-base font-bold text-slate-900">Supporting evidence</h3><p class="mt-0.5 text-sm text-slate-600">Private photos attached to this monitoring record.</p></div>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <a v-for="attachment in visit.attachments" :key="attachment.id" :href="`/admin/post-adoption/evidence/${attachment.id}`" target="_blank" rel="noopener" class="group overflow-hidden rounded-lg border border-slate-200 bg-white hover:border-orange-300">
                        <img :src="`/admin/post-adoption/evidence/${attachment.id}`" :alt="attachment.original_filename" class="aspect-[4/3] w-full object-cover">
                        <span class="block truncate px-3 py-2 text-sm font-medium text-slate-700 group-hover:text-orange-700">{{ attachment.original_filename }}</span>
                    </a>
                </div>
            </section>

            <section v-if="visit.schedule_status === 'Pending'" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="mb-5 border-b border-slate-200 pb-4">
                    <h3 class="text-base font-bold text-slate-900">Complete monitoring record</h3>
                    <p class="mt-0.5 text-sm text-slate-600">Record what was observed. A new follow-up will be saved as a separate schedule.</p>
                </div>

                <form class="space-y-5" @submit.prevent="complete">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <AdminFormField label="Actual follow-up date" required :error="form.errors.actual_visit_date">
                            <input v-model="form.actual_visit_date" type="date" class="admin-control" required>
                        </AdminFormField>
                        <AdminFormField label="Monitoring method" required :error="form.errors.monitoring_method">
                            <select v-model="form.monitoring_method" class="admin-control" required><option>Home Visit</option><option>Phone Call</option><option>Video Call</option><option>Other</option></select>
                        </AdminFormField>
                        <AdminFormField label="Pet status" required :error="form.errors.pet_status">
                            <select v-model="form.pet_status" class="admin-control" required><option>Alive</option><option>Deceased</option><option>Missing</option><option>Unknown / Unable to Verify</option></select>
                        </AdminFormField>
                        <AdminFormField label="Health condition" required :error="form.errors.health_condition">
                            <select v-model="form.health_condition" class="admin-control" required><option>Healthy</option><option>Needs Medical Attention</option><option>Critical Condition</option><option>Not Assessed</option></select>
                        </AdminFormField>
                        <AdminFormField label="Living condition" required :error="form.errors.living_condition">
                            <select v-model="form.living_condition" class="admin-control" required><option>Good</option><option>Acceptable</option><option>Poor</option><option>Not Assessed</option></select>
                        </AdminFormField>
                        <AdminFormField label="Ownership status" required :error="form.errors.ownership_status">
                            <select v-model="form.ownership_status" class="admin-control" required><option>With Original Adopter</option><option>Rehomed / Transferred</option><option>Returned to Shelter</option><option>Cannot Be Verified</option></select>
                        </AdminFormField>
                        <AdminFormField label="Findings and observations" required class="sm:col-span-2" :error="form.errors.findings">
                            <textarea v-model="form.findings" class="admin-control admin-control--textarea" rows="4" required placeholder="Describe the pet's welfare and living environment." />
                        </AdminFormField>
                        <AdminFormField label="Recommendations or required actions" class="sm:col-span-2" :error="form.errors.recommendations">
                            <textarea v-model="form.recommendations" class="admin-control admin-control--textarea" rows="3" placeholder="Optional next steps for the adopter or shelter." />
                        </AdminFormField>
                        <AdminFormField label="Additional remarks" class="sm:col-span-2" :error="form.errors.notes">
                            <textarea v-model="form.notes" class="admin-control admin-control--textarea" rows="3" placeholder="Optional context for the monitoring history." />
                        </AdminFormField>
                        <AdminFormField v-if="canScheduleNext" label="Next follow-up date" :error="form.errors.next_follow_up_date">
                            <input v-model="form.next_follow_up_date" type="date" class="admin-control">
                        </AdminFormField>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <AdminFormField label="Evidence photos" hint="JPG or PNG, up to 5 MB each, maximum 10 files." :error="evidenceError">
                            <input type="file" accept="image/jpeg,image/png" multiple class="admin-control admin-control--file" @change="setEvidence">
                        </AdminFormField>
                    </div>

                    <div class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                        <Link href="/admin/post-adoption/schedules" class="admin-btn admin-btn--secondary">Cancel</Link>
                        <button type="submit" class="admin-btn admin-btn--primary" :disabled="form.processing">
                            {{ form.processing ? 'Saving…' : 'Complete monitoring record' }}
                        </button>
                    </div>
                </form>
            </section>
        </main>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AdminFormField from '@/components/admin/AdminFormField.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';

const props = defineProps({ visit: { type: Object, required: true } });
const page = usePage();
const success = computed(() => page.props.flash?.success);
const canScheduleNext = computed(() => !['Deceased', 'Missing'].includes(form.pet_status) && form.ownership_status !== 'Returned to Shelter');
const evidenceError = computed(() => form.errors['evidence.0'] || form.errors.evidence);
const form = useForm({
    actual_visit_date: new Date().toISOString().slice(0, 10),
    monitoring_method: props.visit.monitoring_method,
    pet_status: 'Alive',
    health_condition: 'Healthy',
    living_condition: 'Good',
    ownership_status: 'With Original Adopter',
    findings: '',
    recommendations: '',
    notes: '',
    next_follow_up_date: '',
    evidence: [],
});

function setEvidence(event) {
    form.evidence = Array.from(event.target.files || []);
}

function complete() {
    form.post(`/admin/post-adoption/visits/${props.visit.id}/complete`, { forceFormData: true });
}
</script>
