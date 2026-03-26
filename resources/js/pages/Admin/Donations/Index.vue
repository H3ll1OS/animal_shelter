<template>
    <AdminLayout>
        <div class="admin-page-container max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div v-if="flash.success || flash.warning || flash.error" class="mb-6">
                <div v-if="flash.success" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert"><strong class="font-bold">Success!</strong> <span class="block sm:inline">{{ flash.success }}</span></div>
                <div v-if="flash.warning" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded" role="alert"><strong class="font-bold">Warning!</strong> <span class="block sm:inline">{{ flash.warning }}</span></div>
                <div v-if="flash.error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert"><span class="block sm:inline">{{ flash.error }}</span></div>
            </div>

            <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-for="donation in donations.data" :key="donation.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><template v-if="donation.anonymous">Anonymous</template><template v-else>{{ donorName(donation) }}</template><span v-if="donation.user_id" class="ml-1 text-xs text-indigo-500">(Registered User)</span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">{{ formatMoney(donation.amount) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :title="formatDateTime(donation.created_at)">{{ formatDate(donation.created_at) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ donation.payment_method || 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClass(donation.status)">{{ donation.status }}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"><Link :href="`/admin/donations/${donation.id}`" class="text-indigo-600 hover:text-indigo-900 transition mr-3">View Details</Link><Link :href="`/admin/donations/${donation.id}`" method="delete" as="button" class="text-red-600 hover:text-red-900 font-medium transition inline-block" @before="(visit) => confirmDelete(visit, donation)">Delete</Link></td>
                        </tr>
                        <tr v-if="donations.data.length === 0"><td colspan="6" class="px-6 py-8 text-center text-gray-500 text-lg">No donation records found.</td></tr>
                    </tbody>
                </table>
                <div class="p-4 bg-gray-50 border-t border-gray-200"><PaginationLinks :links="donations.links" /></div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PaginationLinks from '@/components/common/PaginationLinks.vue';

defineProps({ donations: { type: Object, required: true } });
const page = usePage();
const flash = computed(() => page.props.flash || {});

function donorName(d) { return `${d.name || ''} ${d.last_name || ''}`.trim() || 'Anonymous'; }
function formatMoney(value) { return `\u20b1${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`; }
function formatDate(value) { return value ? String(value).slice(0, 10) : 'N/A'; }
function formatDateTime(value) { return value ? new Date(value).toLocaleString() : ''; }
function statusClass(status) { if (status === 'Completed') return 'bg-green-100 text-green-800'; if (status === 'Pending') return 'bg-yellow-100 text-yellow-800'; return 'bg-red-100 text-red-800'; }
function confirmDelete(visit, donation) { if (!window.confirm(`Are you sure you want to permanently delete the \u20b1${Number(donation.amount || 0).toFixed(2)} donation record from ${donorName(donation)}?`)) visit.cancel(); }
</script>


