<template>
    <AdminLayout>
        <section class="donations-page">
            <header class="donations-head">
                <div>
                    <p class="head-kicker">Finance</p>
                    <h2>Donation History</h2>
                    <p class="head-description">Review incoming contributions and track their verification status.</p>
                </div>
            </header>

            <div v-if="flash.success || flash.warning || flash.error" class="flash-stack">
                <div v-if="flash.success" class="flash flash-success">{{ flash.success }}</div>
                <div v-if="flash.warning" class="flash flash-warning">{{ flash.warning }}</div>
                <div v-if="flash.error" class="flash flash-error">{{ flash.error }}</div>
            </div>

            <div class="summary-grid">
                <article class="summary-card summary-pending">
                    <span>Pending donations</span>
                    <strong>{{ summary.pending }}</strong>
                </article>
                <article class="summary-card summary-verified">
                    <span>Verified donations</span>
                    <strong>{{ summary.verified }}</strong>
                </article>
                <article class="summary-card summary-rejected">
                    <span>Rejected donations</span>
                    <strong>{{ summary.rejected }}</strong>
                </article>
                <article class="summary-card summary-amount">
                    <span>Verified amount</span>
                    <strong>{{ formatMoney(summary.verified_amount) }}</strong>
                </article>
            </div>

            <section class="filter-card">
                <div class="filter-toolbar">
                    <div class="filter-toolbar-copy">
                        <span class="filter-toolbar-title">Donation filters</span>
                        <span v-if="activeFilterCount" class="active-filter-count">
                            {{ activeFilterCount }} active
                        </span>
                        <span v-else class="filter-toolbar-hint">Narrow results by status, date, or donor</span>
                    </div>
                    <button
                        type="button"
                        class="filter-toggle"
                        :aria-expanded="filtersOpen"
                        aria-controls="donation-filter-panel"
                        @click="filtersOpen = !filtersOpen"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10m-7 6h4" />
                        </svg>
                        {{ filtersOpen ? 'Hide filters' : 'Filters' }}
                        <svg class="filter-chevron" :class="{ 'filter-chevron-open': filtersOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                        </svg>
                    </button>
                </div>

                <p v-if="filterError" class="filter-error" role="alert">{{ filterError }}</p>
                <form v-if="filtersOpen" id="donation-filter-panel" class="filter-panel" @submit.prevent="applyFilters">
                    <div class="filter-grid">
                        <label>
                            <span>Search donor or payment</span>
                            <input v-model="filters.search" type="search" maxlength="255" placeholder="Name, email, or payment method">
                        </label>
                        <label>
                            <span>Status</span>
                            <select v-model="filters.status">
                                <option value="">All statuses</option>
                                <option value="Pending">Pending</option>
                                <option value="Verified">Verified</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </label>
                        <label>
                            <span>Submitted from</span>
                            <input v-model="filters.start_date" type="date">
                        </label>
                        <label>
                            <span>Submitted through</span>
                            <input v-model="filters.end_date" type="date">
                        </label>
                        <label>
                            <span>Sort by</span>
                            <select v-model="filters.sort_by">
                                <option value="created_at">Submission date</option>
                                <option value="amount">Amount</option>
                                <option value="status">Status</option>
                            </select>
                        </label>
                        <label>
                            <span>Order</span>
                            <select v-model="filters.sort_direction">
                                <option value="desc">Descending</option>
                                <option value="asc">Ascending</option>
                            </select>
                        </label>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="filter-submit">Apply filters</button>
                        <button type="button" class="filter-clear" @click="clearFilters">Clear all</button>
                    </div>
                </form>
            </section>

            <div class="table-card">
                <div class="table-heading">
                    <div>
                        <h3>Donation submissions</h3>
                        <p>Review payment details and receipt records.</p>
                    </div>
                    <span class="results-count">{{ donations.total }} total</span>
                </div>
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
                            <tr v-for="donation in donations.data" :key="donation.id" :class="{ 'pending-row': (donation.status || 'Pending') === 'Pending' }">
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
                                    <span class="status-pill" :class="statusClass(donation.status || 'Pending')">{{ donation.status || 'Pending' }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="action-wrap">
                                        <Link :href="`/admin/donations/${donation.id}`" class="view-link">View</Link>
                                        <a
                                            v-if="donation.has_receipt"
                                            :href="`/admin/donations/${donation.id}/receipt`"
                                            class="view-link"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            Receipt
                                        </a>
                                        <Link
                                            v-if="(donation.status || 'Pending') === 'Pending'"
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
import { computed, reactive, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PaginationLinks from '@/components/common/PaginationLinks.vue';

const props = defineProps({
    donations: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});
const filterError = computed(() => page.props.errors?.end_date || page.props.errors?.start_date || '');
const filters = reactive({ ...props.filters });
const filtersOpen = ref(false);
const activeFilterCount = computed(() => [
    filters.search,
    filters.status,
    filters.start_date,
    filters.end_date,
    filters.sort_by !== 'created_at',
    filters.sort_direction !== 'desc',
].filter(Boolean).length);

function applyFilters() {
    router.get('/admin/donations', { ...filters }, {
        preserveState: true,
        replace: true,
        onSuccess: () => {
            filtersOpen.value = false;
        },
        onError: () => {
            filtersOpen.value = true;
        },
    });
}

function clearFilters() {
    Object.assign(filters, {
        status: '',
        search: '',
        start_date: '',
        end_date: '',
        sort_by: 'created_at',
        sort_direction: 'desc',
    });
    applyFilters();
}

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
    if (status === 'Verified') return 'status-completed';
    if (status === 'Pending') return 'status-pending';
    if (status === 'Rejected') return 'status-failed';
    return 'status-pending';
}

function confirmDelete(visit, donation) {
    if (!window.confirm(`Are you sure you want to permanently delete the donation record (${formatMoney(donation.amount)}) from ${donorName(donation)}?`)) {
        visit.cancel();
    }
}
</script>

<style scoped>
.donations-page {
    max-width: 1320px;
    margin: 0 auto;
    padding: 28px clamp(16px, 2.5vw, 32px) 36px;
}

.donations-head {
    margin-bottom: 22px;
}

.head-kicker {
    margin: 0 0 6px;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #ea580c;
}

.donations-head h2 {
    margin: 0;
    font-size: clamp(1.8rem, 3vw, 2.25rem);
    line-height: 1.12;
    letter-spacing: -0.035em;
    color: #172033;
}

.head-description {
    margin: 8px 0 0;
    color: #64748b;
    font-size: 0.94rem;
}

.flash-stack {
    display: grid;
    gap: 10px;
    margin-bottom: 18px;
}

.flash {
    border-radius: 12px;
    padding: 12px 15px;
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

.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}

.summary-card {
    display: grid;
    align-content: center;
    gap: 9px;
    min-height: 104px;
    padding: 18px 20px;
    border: 1px solid #e8edf4;
    border-left: 4px solid transparent;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.045);
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
}

.summary-card span {
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 650;
}

.summary-card strong {
    color: #172033;
    font-size: clamp(1.35rem, 2vw, 1.7rem);
    line-height: 1.2;
    letter-spacing: -0.025em;
}

.summary-pending { border-left-color: #f59e0b; }
.summary-verified { border-left-color: #16a34a; }
.summary-rejected { border-left-color: #ef4444; }
.summary-amount { border-left-color: #ea580c; }

.filter-card {
    margin-bottom: 20px;
    padding: 0 20px;
    border: 1px solid #e8edf4;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.035);
}

.filter-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    min-height: 72px;
}

.filter-toolbar-copy {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.filter-toolbar-title {
    color: #172033;
    font-size: 0.94rem;
    font-weight: 750;
}

.filter-toolbar-hint {
    color: #64748b;
    font-size: 0.83rem;
}

.active-filter-count {
    border-radius: 999px;
    padding: 4px 9px;
    background: #fff7ed;
    color: #c2410c;
    font-size: 0.72rem;
    font-weight: 750;
}

.filter-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 38px;
    flex: 0 0 auto;
    border: 1px solid #dbe3ee;
    border-radius: 10px;
    padding: 0 12px;
    background: #fff;
    color: #334155;
    font: inherit;
    font-size: 0.84rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
}

.filter-toggle:hover,
.filter-toggle[aria-expanded="true"] {
    border-color: #fdba74;
    background: #fff7ed;
    color: #c2410c;
}

.filter-toggle > svg:first-child {
    width: 17px;
    height: 17px;
}

.filter-chevron {
    width: 15px;
    height: 15px;
    transition: transform 0.18s ease;
}

.filter-chevron-open {
    transform: rotate(180deg);
}

.filter-panel {
    padding: 18px 0 20px;
    border-top: 1px solid #edf1f6;
}

.filter-grid {
    display: grid;
    grid-template-columns: minmax(220px, 2fr) repeat(5, minmax(125px, 1fr));
    gap: 14px;
}

.filter-grid label {
    display: grid;
    gap: 7px;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 700;
}

.filter-error {
    margin: 0;
    padding: 12px 0;
    color: #b91c1c;
    font-size: 0.88rem;
    font-weight: 700;
}

.filter-grid input,
.filter-grid select {
    width: 100%;
    min-height: 42px;
    border: 1px solid #dbe3ee;
    border-radius: 10px;
    padding: 8px 11px;
    background: #fff;
    color: #172033;
    font: inherit;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.filter-grid input:focus,
.filter-grid select:focus {
    outline: none;
    border-color: #fb923c;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.13);
}

.filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 16px;
}

.filter-submit,
.filter-clear {
    min-height: 40px;
    border-radius: 10px;
    padding: 0 16px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.18s ease, border-color 0.18s ease, transform 0.18s ease;
}

.filter-submit {
    border: 1px solid #ea580c;
    background: #ea580c;
    color: #fff;
}

.filter-submit:hover {
    border-color: #c2410c;
    background: #c2410c;
    transform: translateY(-1px);
}

.filter-clear {
    border: 1px solid #dbe3ee;
    background: #fff;
    color: #334155;
}

.filter-clear:hover {
    border-color: #94a3b8;
    background: #f8fafc;
}

.table-card {
    border: 1px solid #e8edf4;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.045);
    overflow: hidden;
}

.table-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 20px;
    border-bottom: 1px solid #edf1f6;
}

.table-heading h3 {
    margin: 0;
    color: #172033;
    font-size: 1rem;
    font-weight: 750;
}

.table-heading p {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 0.84rem;
}

.results-count {
    flex: 0 0 auto;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    padding: 6px 11px;
    background: #f8fafc;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 700;
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
    padding: 14px 18px;
    border-bottom: 1px solid #edf1f6;
    text-align: left;
    vertical-align: middle;
}

.pending-row {
    background: #fffdf7;
}

.donations-table tbody tr {
    transition: background 0.16s ease;
}

.donations-table tbody tr:hover {
    background: #f8fafc;
}

.donations-table tbody tr.pending-row:hover {
    background: #fff8e8;
}

.donations-table tbody tr:last-child td {
    border-bottom: 0;
}

.donations-table th {
    padding-top: 12px;
    padding-bottom: 12px;
    font-size: 0.7rem;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #718096;
}

.donations-table td {
    font-size: 0.9rem;
    color: #475569;
}

.donor-cell {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
}

.donor-name {
    font-weight: 750;
    color: #172033;
}

.donor-tag {
    font-size: 0.68rem;
    border-radius: 999px;
    padding: 0.24rem 0.58rem;
    background: #f1f5f9;
    color: #64748b;
    font-weight: 700;
}

.amount-cell {
    font-weight: 750;
    color: #172033 !important;
    white-space: nowrap;
}

.method-cell {
    text-transform: capitalize;
}

.muted {
    color: #64748b;
    white-space: nowrap;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 0.32rem 0.72rem;
    font-size: 0.74rem;
    font-weight: 700;
}

.status-completed {
    background: #ecfdf3;
    color: #15803d;
}

.status-pending {
    background: #fff7df;
    color: #a16207;
}

.status-failed {
    background: #fff1f2;
    color: #be123c;
}

.action-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.view-link {
    min-height: 35px;
    padding: 0 13px;
    border-radius: 9px;
    border: 1px solid #dbe3ee;
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
    border-color: #fdba74;
    background: #fff7ed;
    color: #c2410c;
}

.delete-icon-btn {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    border: 1px solid #fee2e2;
    background: #fff;
    color: #ef4444;
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
    background: #fff1f2;
    border-color: #fecdd3;
    color: #be123c;
}

.empty-cell {
    padding: 36px 22px !important;
    text-align: center;
    color: #64748b;
}

.table-footer {
    padding: 14px 18px;
    border-top: 1px solid #edf1f6;
}

@media (max-width: 1100px) {
    .filter-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .filter-grid label:first-child {
        grid-column: span 2;
    }
}

@media (max-width: 720px) {
    .donations-page {
        padding: 20px 14px 28px;
    }

    .summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .summary-card {
        min-height: 92px;
        padding: 14px;
    }

    .filter-grid {
        grid-template-columns: 1fr;
    }

    .filter-grid label:first-child {
        grid-column: auto;
    }

    .filter-card {
        padding: 0 16px;
    }

    .filter-toolbar {
        min-height: 66px;
    }

    .table-heading {
        align-items: flex-start;
        padding: 16px;
    }

    .donations-table th,
    .donations-table td {
        padding-right: 14px;
        padding-left: 14px;
    }
}

@media (max-width: 420px) {
    .filter-toolbar-hint {
        flex-basis: 100%;
    }

    .filter-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .filter-submit,
    .filter-clear {
        padding: 0 10px;
    }
}
</style>
