<template>
    <AdminLayout>
        <div class="admin-page-container max-w-5xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-4">
                <Link href="/admin/donations" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-semibold">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Back to Donation List
                </Link>
                <span class="px-4 py-2 text-sm font-semibold rounded-full border border-gray-200 bg-white shadow-sm">Donation #{{ donation.id }}</span>
            </div>

            <div class="bg-white border border-gray-200 shadow-lg rounded-xl overflow-hidden mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between p-6 bg-gradient-to-r from-indigo-50 via-white to-emerald-50">
                    <div>
                        <p class="text-sm text-gray-500">Donation Amount</p>
                        <p class="text-4xl font-black text-emerald-600">{{ formatMoney(donation.amount) }}</p>
                        <p class="text-sm text-gray-600 mt-2">Received: {{ formatDateTimePretty(donation.created_at) }}</p>
                    </div>
                    <div class="flex items-center gap-3 mt-4 md:mt-0">
                        <span class="px-4 py-2 rounded-full text-sm font-semibold" :class="statusClass(donation.status)">{{ donation.status }}</span>
                        <span v-if="donation.transaction_id" class="px-4 py-2 rounded-full text-xs font-semibold bg-white border border-gray-200 text-gray-700 shadow-sm">TXN: {{ donation.transaction_id }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5">
                    <h3 class="text-lg font-bold text-indigo-700 border-b border-gray-200 pb-3 mb-4">Donor Details</h3>
                    <dl class="space-y-3 text-sm text-gray-800">
                        <div class="flex justify-between"><dt class="text-gray-500">Name</dt><dd class="font-semibold text-right">{{ donorName }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="font-semibold text-right">{{ donation.anonymous ? 'Hidden (anonymous)' : (donation.email || 'N/A') }}</dd></div>
                        <div v-if="donation.user" class="flex justify-between">
                            <dt class="text-gray-500">Registered User</dt>
                            <dd class="font-semibold text-indigo-600 text-right">
                                <Link :href="`/developer/users?search=${encodeURIComponent(donation.user.email || '')}`" class="hover:text-indigo-800">{{ donation.user.name }} (ID: {{ donation.user.id }})</Link>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5">
                    <h3 class="text-lg font-bold text-emerald-700 border-b border-gray-200 pb-3 mb-4">Payment Info</h3>
                    <dl class="space-y-3 text-sm text-gray-800">
                        <div class="flex justify-between"><dt class="text-gray-500">Method</dt><dd class="font-semibold text-right">{{ donation.payment_method || 'Unknown' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Date</dt><dd class="font-semibold text-right">{{ formatDatePretty(donation.created_at) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Time</dt><dd class="font-semibold text-right">{{ formatTime(donation.created_at) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd class="font-semibold text-right">{{ donation.status }}</dd></div>
                    </dl>
                </div>

                <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5">
                    <h3 class="text-lg font-bold text-amber-700 border-b border-gray-200 pb-3 mb-4">Notes</h3>
                    <p v-if="donation.notes" class="text-sm text-gray-800 leading-relaxed">{{ donation.notes }}</p>
                    <p v-else class="text-sm text-gray-500 italic">No notes provided.</p>
                </div>
            </div>

            <div class="mt-6 bg-white border border-red-200 rounded-xl p-5 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-red-600 uppercase">Danger Zone</p>
                        <p class="text-sm text-gray-700">Deleting this donation is permanent and cannot be undone.</p>
                    </div>
                    <Link :href="`/admin/donations/${donation.id}`" method="delete" as="button" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full text-white bg-red-600 hover:bg-red-700 shadow-md transition" @before="confirmDelete">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Delete Record
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({ donation: { type: Object, required: true } });

const donorName = computed(() => {
    if (props.donation.anonymous) return 'Anonymous';
    return `${props.donation.name || ''} ${props.donation.last_name || ''}`.trim() || 'Anonymous';
});

function formatMoney(value) { return `\u20b1${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`; }
function formatDatePretty(value) { return value ? new Date(value).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : 'N/A'; }
function formatTime(value) { return value ? new Date(value).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : 'N/A'; }
function formatDateTimePretty(value) { return `${formatDatePretty(value)} • ${formatTime(value)}`; }
function statusClass(status) {
    if (status === 'Completed') return 'bg-green-100 text-green-800 border border-green-200';
    if (status === 'Pending') return 'bg-yellow-100 text-yellow-800 border border-yellow-200';
    return 'bg-red-100 text-red-800 border border-red-200';
}
function confirmDelete(visit) {
    if (!window.confirm('WARNING: Are you sure you want to permanently delete this donation record? This cannot be undone.')) {
        visit.cancel();
    }
}
</script>


