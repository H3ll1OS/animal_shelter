<template>
    <AdminLayout>
        <section class="donations-page">
            <header class="donations-head">
                <div>
                    <p class="head-kicker">Finance</p>
                    <h2>Donation History</h2>
                </div>
            </header>

            <div v-if="flash.success || flash.warning || flash.error" class="flash-stack">
                <div v-if="flash.success" class="flash flash-success">{{ flash.success }}</div>
                <div v-if="flash.warning" class="flash flash-warning">{{ flash.warning }}</div>
                <div v-if="flash.error" class="flash flash-error">{{ flash.error }}</div>
            </div>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="donations-table">
                        <thead>
                            <tr>
                                <th>Donor</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="donation in donations.data" :key="donation.id">
                                <td>
                                    <div class="donor-cell">
                                        <span class="donor-name">{{ donation.anonymous ? 'Anonymous' : donorName(donation) }}</span>
                                        <span v-if="donation.user_id" class="donor-tag">Registered User</span>
                                    </div>
                                </td>
                                <td class="amount-cell">{{ formatMoney(donation.amount) }}</td>
                                <td class="muted" :title="formatDateTime(donation.created_at)">{{ formatDate(donation.created_at) }}</td>
                                <td class="method-cell">{{ donation.payment_method || 'N/A' }}</td>
                                <td>
                                    <span class="status-pill" :class="statusClass(donation.status)">{{ donation.status }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="action-wrap">
                                        <Link :href="`/admin/donations/${donation.id}`" class="view-link">View</Link>
                                        <Link
                                            :href="`/admin/donations/${donation.id}`"
                                            method="delete"
                                            as="button"
                                            class="delete-icon-btn"
                                            @before="(visit) => confirmDelete(visit, donation)"
                                            title="Delete"
                                            aria-label="Delete"
                                        >
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline stroke-linecap="round" stroke-linejoin="round" points="3 6 5 6 21 6" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4a1 1 0 011-1h6a1 1 0 011 1v2" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14a1 1 0 01-1 1H7a1 1 0 01-1-1L5 6" />
                                                <line x1="10" y1="11" x2="10" y2="17" />
                                                <line x1="14" y1="11" x2="14" y2="17" />
                                            </svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="donations.data.length === 0">
                                <td colspan="6" class="empty-cell">No donation records found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <PaginationLinks :links="donations.links" />
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PaginationLinks from '@/components/common/PaginationLinks.vue';

defineProps({
    donations: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

function donorName(d) {
    return `${d.name || ''} ${d.last_name || ''}`.trim() || 'Anonymous';
}

function formatMoney(value) {
    return `PHP ${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatDate(value) {
    return value ? String(value).slice(0, 10) : 'N/A';
}

function formatDateTime(value) {
    return value ? new Date(value).toLocaleString() : '';
}

function statusClass(status) {
    if (status === 'Completed') return 'status-completed';
    if (status === 'Pending') return 'status-pending';
    return 'status-failed';
}

function confirmDelete(visit, donation) {
    if (!window.confirm(`Are you sure you want to permanently delete the donation record (${formatMoney(donation.amount)}) from ${donorName(donation)}?`)) {
        visit.cancel();
    }
}
</script>

<style scoped>
.donations-page {
    max-width: 1180px;
    margin: 0 auto;
    padding: 24px;
}

.donations-head {
    margin-bottom: 14px;
}

.head-kicker {
    margin: 0 0 4px;
    font-size: 0.76rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #64748b;
}

.donations-head h2 {
    margin: 0;
    font-size: clamp(1.6rem, 2.6vw, 2.05rem);
    line-height: 1.15;
    color: #0f172a;
}

.flash-stack {
    display: grid;
    gap: 10px;
    margin-bottom: 12px;
}

.flash {
    border-radius: 11px;
    padding: 10px 12px;
    font-weight: 700;
    border: 1px solid transparent;
}

.flash-success {
    background: #dcfce7;
    border-color: #bbf7d0;
    color: #166534;
}

.flash-warning {
    background: #fef3c7;
    border-color: #fde68a;
    color: #92400e;
}

.flash-error {
    background: #fee2e2;
    border-color: #fecaca;
    color: #991b1b;
}

.table-card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
    overflow: hidden;
}

.table-scroll {
    overflow-x: auto;
}

.donations-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    min-width: 960px;
}

.donations-table thead {
    background: #f8fafc;
}

.donations-table th,
.donations-table td {
    padding: 12px 14px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
    vertical-align: middle;
}

.donations-table th {
    font-size: 0.76rem;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #64748b;
}

.donations-table td {
    font-size: 0.95rem;
    color: #334155;
}

.donor-cell {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.donor-name {
    font-weight: 700;
    color: #0f172a;
}

.donor-tag {
    font-size: 0.72rem;
    border-radius: 999px;
    padding: 0.2rem 0.55rem;
    background: #eff6ff;
    color: #1d4ed8;
    font-weight: 700;
}

.amount-cell {
    font-weight: 800;
    color: #166534;
}

.method-cell {
    text-transform: capitalize;
}

.muted {
    color: #64748b;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 0.24rem 0.62rem;
    font-size: 0.77rem;
    font-weight: 700;
}

.status-completed {
    background: #dcfce7;
    color: #166534;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-failed {
    background: #fee2e2;
    color: #991b1b;
}

.action-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.view-link {
    min-height: 34px;
    padding: 0 12px;
    border-radius: 10px;
    border: 1px solid #d1d5db;
    background: #fff;
    color: #334155;
    text-decoration: none;
    font-size: 0.84rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.18s ease;
}

.view-link:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    color: #0f172a;
}

.delete-icon-btn {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #dc2626;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.18s ease;
}

.delete-icon-btn svg {
    width: 16px;
    height: 16px;
}

.delete-icon-btn:hover {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #b91c1c;
}

.empty-cell {
    padding: 22px !important;
    text-align: center;
    color: #64748b;
}

.table-footer {
    padding: 12px 14px;
}

@media (max-width: 720px) {
    .donations-page {
        padding: 16px;
    }
}
</style>
