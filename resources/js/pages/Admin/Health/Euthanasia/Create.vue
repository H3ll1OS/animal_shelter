<template>
    <AdminLayout>
        <main class="mx-auto flex w-full max-w-5xl flex-col gap-5 p-4 md:p-6">
            <AdminPageHeader
                eyebrow="Health records"
                title="Add euthanasia record"
                description="Choose one or more eligible pets, then enter the shared record details."
            >
                <template #actions>
                    <Link href="/admin/health/euthanasia" class="admin-btn admin-btn--secondary">Back to logs</Link>
                </template>
            </AdminPageHeader>

            <p v-if="flash.success" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">
                {{ flash.success }}
            </p>
            <p v-if="flash.error" class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800" role="alert">
                {{ flash.error }}
            </p>

            <form class="overflow-visible rounded-xl border border-slate-200 bg-white shadow-sm" @submit.prevent="submit">
                <div class="record-form-body p-4 sm:p-6">
                    <div class="relative">
                        <label for="pet-picker" class="mb-1.5 block text-sm font-semibold text-slate-700">
                            Pet <span class="text-rose-600">*</span>
                        </label>
                        <button
                            id="pet-picker"
                            type="button"
                            class="flex min-h-11 w-full items-center justify-between gap-3 rounded-lg border border-slate-300 bg-white px-3 py-2 text-left text-sm text-slate-800 shadow-sm hover:border-slate-400 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100"
                            :aria-expanded="pickerOpen"
                            aria-haspopup="listbox"
                            @click="pickerOpen = !pickerOpen"
                        >
                            <span v-if="selectedPets.length" class="flex min-w-0 flex-wrap gap-1.5">
                                <span
                                    v-for="pet in selectedPets"
                                    :key="pet.id"
                                    class="inline-flex max-w-full items-center gap-1 rounded-md bg-orange-50 px-2 py-1 text-xs font-semibold text-orange-800"
                                >
                                    <span class="truncate">{{ pet.name }}</span>
                                    <span class="sr-only">selected</span>
                                </span>
                            </span>
                            <span v-else class="text-slate-500">Select pet or pets</span>
                            <svg class="h-4 w-4 shrink-0 text-slate-500 transition-transform" :class="{ 'rotate-180': pickerOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6" />
                            </svg>
                        </button>
                        <p v-if="form.errors.pet_id || form.errors.pet_ids" class="mt-1 text-sm text-rose-700">
                            {{ form.errors.pet_id || form.errors.pet_ids }}
                        </p>

                        <section v-if="pickerOpen" class="pet-picker-panel" aria-label="Choose pets">
                            <div class="pet-picker-filters border-b border-slate-200 bg-slate-50 p-3">
                                <label class="pet-filter-field text-xs font-semibold text-slate-600">
                                    Search pets
                                    <input v-model="filtersForm.pet_search" type="search" class="admin-control" placeholder="Pet name or breed" @input="applyPetFilters">
                                </label>
                                <label class="pet-filter-field text-xs font-semibold text-slate-600">
                                    Species
                                    <select v-model="filtersForm.species" class="admin-control" @change="applyPetFilters">
                                        <option value="">All species</option>
                                        <option v-for="species in speciesOptions" :key="species" :value="species">{{ species }}</option>
                                    </select>
                                </label>
                                <label class="pet-page-select">
                                    <input
                                        ref="selectAllInput"
                                        type="checkbox"
                                        class="h-4 w-4 accent-orange-600"
                                        aria-label="Select all eligible pets on this page"
                                        :checked="allCurrentPageSelected"
                                        :disabled="!eligiblePets.data.length"
                                        @change="toggleCurrentPage"
                                    >
                                    Select page
                                </label>
                            </div>

                            <div v-if="eligiblePets.data.length" class="max-h-64 overflow-y-auto" role="listbox" aria-multiselectable="true" aria-label="Eligible pets">
                                <label
                                    v-for="pet in eligiblePets.data"
                                    :key="pet.id"
                                    class="flex cursor-pointer items-center gap-3 border-b border-slate-100 px-4 py-3 text-sm hover:bg-slate-50"
                                    :class="{ 'bg-orange-50/70': selectedPetsMap.has(pet.id) }"
                                >
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 shrink-0 accent-orange-600"
                                        :checked="selectedPetsMap.has(pet.id)"
                                        @change="togglePet(pet, $event.target.checked)"
                                    >
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate font-semibold text-slate-800">{{ pet.name }}</span>
                                        <span class="mt-0.5 block truncate text-xs text-slate-500">
                                            {{ [pet.species, pet.breed, pet.status].filter(Boolean).join(' · ') }}
                                        </span>
                                    </span>
                                </label>
                            </div>
                            <div v-else class="p-4">
                                <AdminEmptyState compact title="No eligible pets found" description="Try changing your search or species filter." />
                            </div>

                            <footer class="flex flex-col gap-3 border-t border-slate-200 px-3 py-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                                <span>{{ eligiblePets.total }} eligible {{ eligiblePets.total === 1 ? 'pet' : 'pets' }}</span>
                                <nav v-if="eligiblePets.links.length > 3" class="flex flex-wrap gap-1.5" aria-label="Eligible pet pages">
                                    <Link
                                        v-for="(link, index) in eligiblePets.links"
                                        :key="index"
                                        :href="link.url || ''"
                                        class="rounded-md border px-2.5 py-1.5 text-xs"
                                        :class="link.active ? 'border-orange-600 bg-orange-600 text-white' : link.url ? 'border-slate-200 bg-white text-slate-700' : 'pointer-events-none border-slate-200 bg-white text-slate-400 opacity-50'"
                                        v-html="link.label"
                                        preserve-state
                                        preserve-scroll
                                    />
                                </nav>
                            </footer>
                            <div class="flex items-center justify-between gap-3 border-t border-slate-100 px-3 py-2.5">
                                <span class="text-xs font-medium text-slate-600">{{ selectedPets.length }} selected</span>
                                <button type="button" class="text-sm font-semibold text-orange-700 hover:text-orange-800" @click="pickerOpen = false">Done</button>
                            </div>
                        </section>
                    </div>

                    <div class="record-fields">
                        <label class="record-field text-sm font-semibold text-slate-700">
                            Reason <span class="text-rose-600">*</span>
                            <input v-model="form.reason" type="text" maxlength="255" required class="admin-control" placeholder="e.g., Severe illness">
                            <small v-if="form.errors.reason" class="font-normal text-rose-700">{{ form.errors.reason }}</small>
                        </label>
                        <label class="record-field text-sm font-semibold text-slate-700">
                            Veterinarian <span class="text-rose-600">*</span>
                            <input v-model="form.veterinarian" type="text" maxlength="255" required class="admin-control" placeholder="Veterinarian name">
                            <small v-if="form.errors.veterinarian" class="font-normal text-rose-700">{{ form.errors.veterinarian }}</small>
                        </label>
                        <label class="record-field text-sm font-semibold text-slate-700">
                            Euthanasia date <span class="text-rose-600">*</span>
                            <input v-model="form.euthanasia_date" type="date" required class="admin-control">
                            <small v-if="form.errors.euthanasia_date" class="font-normal text-rose-700">{{ form.errors.euthanasia_date }}</small>
                        </label>
                        <label class="record-field record-field--full text-sm font-semibold text-slate-700">
                            Notes
                            <textarea v-model="form.notes" rows="3" class="admin-control admin-control--textarea" placeholder="Optional notes"></textarea>
                            <small v-if="form.errors.notes" class="font-normal text-rose-700">{{ form.errors.notes }}</small>
                        </label>
                    </div>
                    <p v-if="form.errors.confirmed" class="text-sm text-rose-700">{{ form.errors.confirmed }}</p>
                </div>

                <footer class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:justify-end sm:px-6">
                    <Link href="/admin/health/euthanasia" class="admin-btn admin-btn--secondary">Cancel</Link>
                    <button type="submit" class="admin-btn admin-btn--primary" :disabled="form.processing || selectedPets.length === 0">
                        {{ form.processing ? 'Saving…' : selectedPets.length > 1 ? 'Review and record selected' : 'Save record' }}
                    </button>
                </footer>
            </form>

            <div v-if="bulkDialogOpen" class="fixed inset-0 z-[80] flex items-center justify-center overflow-y-auto bg-slate-900/55 p-4" @click.self="bulkDialogOpen = false">
                <section class="flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="bulk-modal-title">
                    <header class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-orange-700">Review before recording</p>
                            <h3 id="bulk-modal-title" class="mt-1 text-lg font-bold text-slate-900">Confirm selected pets</h3>
                        </div>
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-xl text-slate-500 hover:bg-slate-50" aria-label="Close dialog" @click="bulkDialogOpen = false">×</button>
                    </header>
                    <div class="overflow-y-auto p-5">
                        <p class="text-sm text-slate-600">Confirm that you want to create a separate euthanasia record for each selected pet using the details in the form.</p>
                        <ul class="mt-3 flex max-h-48 flex-col gap-2 overflow-y-auto p-0">
                            <li v-for="pet in selectedPets" :key="pet.id" class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2">
                                <span class="min-w-0">
                                    <strong class="block truncate text-sm text-slate-800">{{ pet.name }}</strong>
                                    <small class="block truncate text-xs text-slate-500">{{ [pet.species, pet.breed].filter(Boolean).join(' · ') || 'Pet' }}</small>
                                </span>
                                <button type="button" class="shrink-0 rounded-md px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50" :aria-label="`Remove ${pet.name}`" @click="removeSelectedPet(pet.id)">Remove</button>
                            </li>
                        </ul>
                    </div>
                    <footer class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                        <button type="button" class="admin-btn admin-btn--secondary" :disabled="form.processing" @click="bulkDialogOpen = false">Cancel</button>
                        <button type="button" class="admin-btn admin-btn--primary" :disabled="form.processing || selectedPets.length < 2" @click="submitBulk">
                            {{ form.processing ? 'Recording…' : 'Confirm and record' }}
                        </button>
                    </footer>
                </section>
            </div>
        </main>
    </AdminLayout>
</template>

<script setup>
import { computed, reactive, ref, watchEffect } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';

const props = defineProps({
    eligiblePets: { type: Object, required: true },
    speciesOptions: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});
const pickerOpen = ref(false);
const bulkDialogOpen = ref(false);
const selectAllInput = ref(null);
const filtersForm = reactive({
    pet_search: props.filters.pet_search || '',
    species: props.filters.species || '',
});
const selectedPetsMap = reactive(new Map());
const selectedPets = computed(() => Array.from(selectedPetsMap.values()));
const selectedOnCurrentPage = computed(() => props.eligiblePets.data.filter((pet) => selectedPetsMap.has(pet.id)).length);
const allCurrentPageSelected = computed(() => {
    const currentPets = props.eligiblePets.data;
    return currentPets.length > 0 && currentPets.every((pet) => selectedPetsMap.has(pet.id));
});
watchEffect(() => {
    if (selectAllInput.value) {
        selectAllInput.value.indeterminate = selectedOnCurrentPage.value > 0 && !allCurrentPageSelected.value;
    }
});
const form = useForm({
    pet_id: '',
    pet_ids: [],
    reason: '',
    euthanasia_date: '',
    veterinarian: '',
    notes: '',
    confirmed: false,
});
let filterTimeout;

function togglePet(pet, checked) {
    if (checked) selectedPetsMap.set(pet.id, pet);
    else selectedPetsMap.delete(pet.id);
    syncPetFields();
}

function toggleCurrentPage() {
    if (allCurrentPageSelected.value) {
        props.eligiblePets.data.forEach((pet) => selectedPetsMap.delete(pet.id));
    } else {
        props.eligiblePets.data.forEach((pet) => selectedPetsMap.set(pet.id, pet));
    }
    syncPetFields();
}

function removeSelectedPet(petId) {
    selectedPetsMap.delete(petId);
    syncPetFields();
}

function syncPetFields() {
    const ids = selectedPets.value.map((pet) => pet.id);
    form.pet_ids = ids;
    form.pet_id = ids.length === 1 ? ids[0] : '';
}

function applyPetFilters() {
    window.clearTimeout(filterTimeout);
    filterTimeout = window.setTimeout(() => {
        router.get('/admin/health/euthanasia/create', {
            pet_search: filtersForm.pet_search,
            species: filtersForm.species,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 250);
}

function submit() {
    if (selectedPets.value.length > 1) {
        form.clearErrors();
        bulkDialogOpen.value = true;
        return;
    }

    form.post('/admin/health/euthanasia', {
        preserveScroll: true,
    });
}

function submitBulk() {
    form.pet_ids = selectedPets.value.map((pet) => pet.id);
    form.confirmed = true;
    form.post('/admin/health/euthanasia/bulk', {
        preserveScroll: true,
        onSuccess: () => {
            bulkDialogOpen.value = false;
            selectedPetsMap.clear();
            form.reset('pet_id', 'pet_ids', 'reason', 'euthanasia_date', 'veterinarian', 'notes');
            form.confirmed = false;
        },
    });
}
</script>

<style scoped>
.record-form-body {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.record-fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px 20px;
}

.record-field,
.pet-filter-field {
    display: flex;
    min-width: 0;
    flex-direction: column;
    align-items: stretch;
    gap: 7px;
}

.record-field--full {
    grid-column: 1 / -1;
}

.pet-picker-filters {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(150px, 190px) auto;
    align-items: end;
    gap: 12px;
}

.pet-page-select {
    display: inline-flex;
    min-height: 2.75rem;
    align-items: center;
    gap: 8px;
    color: #475569;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.pet-picker-panel {
    position: relative;
    z-index: 1;
    width: 100%;
    margin-top: 8px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    box-shadow: 0 8px 20px rgb(15 23 42 / 7%);
}

@media (max-width: 640px) {
    .record-fields {
        grid-template-columns: minmax(0, 1fr);
    }

    .record-field--full {
        grid-column: auto;
    }

    .pet-picker-filters {
        grid-template-columns: minmax(0, 1fr) minmax(0, 0.8fr);
    }

    .pet-page-select {
        grid-column: 1 / -1;
    }
}

@media (max-width: 420px) {
    .pet-picker-filters {
        grid-template-columns: minmax(0, 1fr);
    }

    .pet-page-select {
        grid-column: auto;
    }
}
</style>
