<template>
    <AdminLayout>
        <main class="mx-auto max-w-5xl space-y-6 p-4 md:p-6">
            <AdminPageHeader
                eyebrow="Pet monitoring profile"
                :title="adoption.pet?.name || 'Adopted pet'"
                :description="`${adoption.pet?.species || 'Species not recorded'} · ${adoption.pet?.breed || 'Breed not recorded'} · Adoption application #${adoption.id}`"
            >
                <template #actions>
                    <Link href="/admin/post-adoption/schedules/create" class="admin-btn admin-btn--primary">Schedule follow-up</Link>
                </template>
            </AdminPageHeader>

            <section class="grid gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 sm:p-5">
                <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Original adopter</p><p class="mt-1 font-semibold text-slate-900">{{ adoption.full_name }}</p><p class="mt-0.5 break-words text-sm text-slate-600">{{ adoption.phone }} · {{ adoption.email }}</p></div>
                <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Adoption status</p><p class="mt-1"><AdminStatusBadge :status="adoption.status" /></p></div>
                <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Latest recorded welfare</p><div v-if="latestCompleted" class="mt-1 flex flex-wrap gap-1.5"><AdminStatusBadge :status="latestCompleted.pet_status" /><AdminStatusBadge :status="latestCompleted.health_condition" /></div><p v-else class="mt-1 text-sm text-slate-600">No completed monitoring record yet.</p></div>
                <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Next scheduled follow-up</p><p v-if="nextVisit" class="mt-1 font-medium text-slate-900">{{ nextVisit.scheduled_date }} <span class="text-sm font-normal text-slate-600">· {{ nextVisit.assigned_user?.name || 'Unassigned' }}</span></p><p v-else class="mt-1 text-sm text-slate-600">No upcoming follow-up scheduled.</p></div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="mb-5 border-b border-slate-200 pb-4">
                    <h3 class="text-base font-bold text-slate-900">Monitoring history</h3>
                    <p class="mt-0.5 text-sm text-slate-600">Activities shown from newest to oldest.</p>
                </div>

                <div v-if="visits.length" class="space-y-0">
                    <article v-for="visit in visits" :key="visit.id" class="relative border-l border-slate-200 pb-6 pl-5 last:border-transparent last:pb-0 sm:pl-6">
                        <span class="absolute -left-[5px] top-1 h-2.5 w-2.5 rounded-full border-2 border-white bg-orange-500 ring-1 ring-orange-200" aria-hidden="true" />
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <Link :href="`/admin/post-adoption/visits/${visit.id}`" class="font-semibold text-slate-900 hover:text-orange-700">
                                    {{ visit.actual_visit_date || visit.scheduled_date }}
                                </Link>
                                <p class="mt-0.5 text-sm text-slate-600">{{ visit.monitoring_method }} · {{ visit.assigned_user?.name || 'Unassigned' }}</p>
                            </div>
                            <AdminStatusBadge :status="visit.schedule_status" />
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <AdminStatusBadge v-if="visit.pet_status" :status="visit.pet_status" />
                            <AdminStatusBadge v-if="visit.health_condition" :status="visit.health_condition" />
                            <AdminStatusBadge v-if="visit.ownership_status" :status="visit.ownership_status" />
                        </div>
                        <p v-if="visit.findings || visit.purpose" class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ visit.findings || visit.purpose }}</p>
                        <div v-if="visit.attachments?.length" class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <a v-for="attachment in visit.attachments" :key="attachment.id" :href="`/admin/post-adoption/evidence/${attachment.id}`" target="_blank" rel="noopener" class="overflow-hidden rounded-lg border border-slate-200">
                                <img :src="`/admin/post-adoption/evidence/${attachment.id}`" :alt="attachment.original_filename" class="aspect-[4/3] w-full object-cover">
                                <span class="block truncate px-2 py-1.5 text-xs text-slate-600">{{ attachment.original_filename }}</span>
                            </a>
                        </div>
                    </article>
                </div>
                <AdminEmptyState v-else title="No monitoring history" description="Schedule the first follow-up to start this pet's post-adoption record." />
            </section>
        </main>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';

const props = defineProps({ adoption: { type: Object, required: true }, visits: { type: Array, default: () => [] } });
const latestCompleted = computed(() => props.visits.find((visit) => visit.schedule_status === 'Completed'));
const nextVisit = computed(() => [...props.visits]
    .filter((visit) => visit.schedule_status === 'Pending' && visit.scheduled_date >= new Date().toISOString().slice(0, 10))
    .sort((a, b) => a.scheduled_date.localeCompare(b.scheduled_date))[0]);
</script>
