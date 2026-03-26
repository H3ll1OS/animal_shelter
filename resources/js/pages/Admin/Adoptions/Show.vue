<template>
    <AdminLayout>
        <div class="admin-page-container max-w-6xl mx-auto p-6">
            <div class="bg-white shadow-lg rounded-t-lg p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-extrabold text-gray-800">Application #{{ adoption.id }}</h1>
                    <div class="text-right">
                        <p class="text-lg font-bold" :class="statusTextClass(adoption.status)">Status: {{ adoption.status }}</p>
                        <p class="text-sm text-gray-500">Submitted: {{ formatLongDate(adoption.created_at) }}</p>
                    </div>
                </div>
            </div>

            <div
                v-if="isPending"
                class="bg-gradient-to-r from-amber-50 via-white to-amber-100 border border-amber-200 p-5 mb-6 shadow-lg rounded-lg flex flex-col md:flex-row md:items-center md:justify-between gap-4"
            >
                <div>
                    <p class="text-sm font-semibold text-amber-700 uppercase tracking-wide">Decision Needed</p>
                    <p class="text-lg font-bold text-gray-800">Review and finalize this application.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        :href="`/admin/adoptions/${adoption.id}/approve`"
                        method="post"
                        as="button"
                        class="flex items-center gap-2 bg-emerald-600 text-white px-5 py-2.5 rounded-full shadow-md hover:bg-emerald-700 transition font-semibold"
                        @before="confirmApprove"
                    >
                        <span class="text-lg">?</span> Approve
                    </Link>
                    <Link
                        :href="`/admin/adoptions/${adoption.id}/reject`"
                        method="post"
                        as="button"
                        class="flex items-center gap-2 bg-red-600 text-white px-5 py-2.5 rounded-full shadow-md hover:bg-red-700 transition font-semibold"
                        @before="confirmReject"
                    >
                        <span class="text-lg">?</span> Reject
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
                <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-indigo-500">
                    <h2 class="text-xl font-bold mb-4 text-indigo-700 border-b pb-2">Applicant Details</h2>
                    <dl class="space-y-3 text-base">
                        <div class="flex justify-between items-center"><dt class="font-medium text-gray-600">Name:</dt><dd class="text-gray-900 font-semibold">{{ adoption.applicant_name || adoption.full_name || 'N/A' }}</dd></div>
                        <div class="flex justify-between items-center"><dt class="font-medium text-gray-600">Email:</dt><dd class="text-gray-900 break-all">{{ adoption.email || 'N/A' }}</dd></div>
                        <div class="flex justify-between items-center"><dt class="font-medium text-gray-600">Phone:</dt><dd class="text-gray-900">{{ adoption.phone || 'N/A' }}</dd></div>
                        <div class="flex justify-between items-center"><dt class="font-medium text-gray-600">Address:</dt><dd class="text-gray-900">{{ adoption.address || 'N/A' }}</dd></div>
                        <div class="flex justify-between items-start">
                            <dt class="font-medium text-gray-600">Supporting Documents:</dt>
                            <dd class="text-gray-900 space-y-1">
                                <div v-for="(doc, idx) in documents" :key="idx">
                                    <a :href="`/storage/${doc}`" class="text-indigo-600 hover:text-indigo-800 font-semibold" target="_blank" rel="noopener">
                                        View / Download {{ documents.length > 1 ? `Document ${idx + 1}` : '' }}
                                    </a>
                                </div>
                                <span v-if="documents.length === 0" class="text-gray-500">No document uploaded</span>
                            </dd>
                        </div>
                        <div v-if="adoption.user" class="flex justify-between items-center border-t pt-3 mt-3">
                            <dt class="font-medium text-gray-600">Registered User:</dt>
                            <dd class="text-indigo-600 hover:text-indigo-800">{{ adoption.user.name }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-teal-500">
                    <h2 class="text-xl font-bold mb-4 text-teal-700 border-b pb-2">Pet Applied For</h2>
                    <dl v-if="adoption.pet" class="space-y-3 text-base">
                        <div class="flex justify-between items-center">
                            <dt class="font-medium text-gray-600">Pet Name:</dt>
                            <dd class="text-gray-900 font-semibold">
                                <Link :href="`/admin/pets/${adoption.pet.id}`" class="text-teal-600 hover:text-teal-800">{{ adoption.pet.name }}</Link>
                            </dd>
                        </div>
                        <div class="flex justify-between items-center"><dt class="font-medium text-gray-600">Species:</dt><dd class="text-gray-900">{{ adoption.pet.species }}</dd></div>
                        <div class="flex justify-between items-center"><dt class="font-medium text-gray-600">Breed:</dt><dd class="text-gray-900">{{ adoption.pet.breed || 'Unknown' }}</dd></div>
                        <div class="flex justify-between items-center"><dt class="font-medium text-gray-600">Age:</dt><dd class="text-gray-900">{{ adoption.pet.age }} years</dd></div>
                    </dl>
                    <p v-else class="text-red-500 font-semibold mt-4">Pet record not found or was deleted.</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-yellow-500">
                    <h2 class="text-xl font-bold mb-4 text-yellow-700 border-b pb-2">Application Answers</h2>
                    <div class="space-y-4 text-sm">
                        <div class="bg-gray-50 p-3 rounded-md border">
                            <dt class="font-semibold text-gray-800">Living Situation (Apartment/House/Yard):</dt>
                            <dd class="text-gray-900 mt-1">{{ adoption.housing_type || 'No Answer Provided' }}</dd>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-md border">
                            <dt class="font-semibold text-gray-800">Prior Pet Experience:</dt>
                            <dd class="text-gray-900 mt-1">{{ adoption.pet_experience || 'No Answer Provided' }}</dd>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-md border">
                            <dt class="font-semibold text-gray-800">Reason for Adopting This Pet:</dt>
                            <dd class="text-gray-900 mt-1">{{ adoption.reason_for_adoption || 'No Answer Provided' }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 text-center">
                <Link href="/admin/adoptions" class="text-lg text-gray-600 hover:text-indigo-600 font-medium transition flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Return to Applications List
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    adoption: {
        type: Object,
        required: true,
    },
});

const isPending = computed(() => props.adoption.status === 'Pending Review' || props.adoption.status === 'Pending');

const documents = computed(() => {
    const value = props.adoption.document_path;
    if (!value) return [];
    try {
        const parsed = typeof value === 'string' ? JSON.parse(value) : value;
        if (Array.isArray(parsed)) return parsed;
        if (typeof parsed === 'string') return [parsed];
        return [];
    } catch {
        return [value];
    }
});

function statusTextClass(status) {
    if (status === 'Pending Review') return 'text-yellow-600';
    if (status === 'Approved') return 'text-green-600';
    return 'text-red-600';
}

function formatLongDate(value) {
    if (!value) return 'N/A';
    return new Date(value).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
}

function confirmApprove(visit) {
    if (!window.confirm("Confirm approval? This will mark the application as approved and may change the pet's status.")) {
        visit.cancel();
    }
}

function confirmReject(visit) {
    if (!window.confirm('Confirm rejection? This will mark the application as denied.')) {
        visit.cancel();
    }
}
</script>


