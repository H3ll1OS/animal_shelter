<template>
    <AdminLayout>
        <section class="donation-show-page">
            <header class="page-top">
                <Link href="/admin/donations" class="back-link">
                    <svg class="back-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Donation List
                </Link>
                <span class="badge-id">Donation #{{ donation.id }}</span>
            </header>

            <article class="amount-card">
                <div>
                    <p class="label">Donation Amount</p>
                    <p class="amount">{{ formatMoney(donation.amount) }}</p>
                    <p class="meta">Received: {{ formatDateTimePretty(donation.created_at) }}</p>
                </div>
                <div class="amount-side">
                    <span class="status-pill" :class="statusClass(donation.status)">{{ donation.status }}</span>
                    <span v-if="donation.transaction_id" class="txn-pill">TXN: {{ donation.transaction_id }}</span>
                </div>
            </article>

            <div class="info-grid">
                <article class="info-card">
                    <h3>Donor Details</h3>
                    <dl class="info-list">
                        <div><dt>Name</dt><dd>{{ donorName }}</dd></div>
                        <div><dt>Email</dt><dd>{{ donation.anonymous ? 'Hidden (anonymous)' : (donation.email || 'N/A') }}</dd></div>
                        <div v-if="donation.user"><dt>Registered User</dt><dd class="accent">{{ donation.user.name }} (ID: {{ donation.user.id }})</dd></div>
                    </dl>
                </article>

                <article class="info-card">
                    <h3>Payment Info</h3>
                    <dl class="info-list">
                        <div><dt>Method</dt><dd class="capitalize">{{ donation.payment_method || 'Unknown' }}</dd></div>
                        <div><dt>Date</dt><dd>{{ formatDatePretty(donation.created_at) }}</dd></div>
                        <div><dt>Time</dt><dd>{{ formatTime(donation.created_at) }}</dd></div>
                        <div><dt>Status</dt><dd>{{ donation.status }}</dd></div>
                    </dl>
                </article>

                <article class="info-card">
                    <h3>Notes</h3>
                    <p v-if="donation.notes" class="notes">{{ donation.notes }}</p>
                    <p v-else class="empty-notes">No notes provided.</p>
                </article>
            </div>

            <section class="danger-zone">
                <div>
                    <p class="danger-kicker">Danger Zone</p>
                    <p class="danger-copy">Deleting this donation is permanent and cannot be undone.</p>
                </div>
                <Link
                    :href="`/admin/donations/${donation.id}`"
                    method="delete"
                    as="button"
                    class="delete-btn"
                    @before="confirmDelete"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" />
                        <line x1="10" y1="11" x2="10" y2="17" />
                        <line x1="14" y1="11" x2="14" y2="17" />
                    </svg>
                    Delete Record
                </Link>
            </section>
        </section>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    donation: {
        type: Object,
        required: true,
    },
});

const donorName = computed(() => {
    if (props.donation.anonymous) return 'Anonymous';
    return `${props.donation.name || ''} ${props.donation.last_name || ''}`.trim() || 'Anonymous';
});

function formatMoney(value) {
    return `PHP ${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatDatePretty(value) {
    return value ? new Date(value).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : 'N/A';
}

function formatTime(value) {
    return value ? new Date(value).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : 'N/A';
}

function formatDateTimePretty(value) {
    return `${formatDatePretty(value)} | ${formatTime(value)}`;
}

function statusClass(status) {
    if (status === 'Completed') return 'status-completed';
    if (status === 'Pending') return 'status-pending';
    return 'status-failed';
}

function confirmDelete(visit) {
    if (!window.confirm('WARNING: Are you sure you want to permanently delete this donation record? This cannot be undone.')) {
        visit.cancel();
    }
}
</script>

<style scoped>
.donation-show-page {
    max-width: 1120px;
    margin: 0 auto;
    padding: 20px 24px 24px;
}

.page-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}

.back-link {
    color: #2563eb;
    text-decoration: none;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

.back-link:hover { color: #1d4ed8; }

.back-icon { width: 16px; height: 16px; }

.badge-id {
    border: 1px solid #d1d5db;
    background: #fff;
    border-radius: 999px;
    min-height: 34px;
    padding: 0 12px;
    font-size: 0.85rem;
    font-weight: 700;
    color: #334155;
    display: inline-flex;
    align-items: center;
}

.amount-card {
    border: 1px solid #d1fae5;
    border-radius: 16px;
    background: linear-gradient(180deg, #f0fdf4 0%, #ecfdf3 100%);
    padding: 18px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
}

.label {
    margin: 0;
    color: #64748b;
    font-size: 0.84rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.amount {
    margin: 6px 0 0;
    font-size: clamp(2rem, 4vw, 2.5rem);
    line-height: 1;
    font-weight: 900;
    color: #059669;
}

.meta {
    margin: 8px 0 0;
    color: #475569;
}

.amount-side {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.status-pill,
.txn-pill {
    border-radius: 999px;
    min-height: 32px;
    padding: 0 12px;
    display: inline-flex;
    align-items: center;
    font-size: 0.76rem;
    font-weight: 700;
}

.txn-pill {
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #334155;
}

.status-completed { background: #dcfce7; color: #166534; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-failed { background: #fee2e2; color: #991b1b; }

.info-grid {
    margin-top: 12px;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.info-card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    padding: 14px;
}

.info-card h3 {
    margin: 0 0 12px;
    color: #0f172a;
    font-size: 1.4rem;
    line-height: 1.1;
}

.info-list {
    margin: 0;
    display: grid;
    gap: 8px;
}

.info-list div {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
    padding: 8px 10px;
}

.info-list dt { color: #64748b; font-weight: 700; }
.info-list dd { margin: 0; color: #0f172a; font-weight: 700; text-align: right; }

.accent { color: #2563eb !important; }
.capitalize { text-transform: capitalize; }

.notes { margin: 0; color: #0f172a; line-height: 1.65; }
.empty-notes { margin: 0; color: #64748b; font-style: italic; }

.danger-zone {
    margin-top: 12px;
    border: 1px solid #fecaca;
    border-radius: 14px;
    background: #fff;
    padding: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.danger-kicker {
    margin: 0;
    color: #dc2626;
    font-size: 0.76rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-weight: 800;
}

.danger-copy { margin: 4px 0 0; color: #475569; }

.delete-btn {
    min-height: 38px;
    border: 0;
    border-radius: 10px;
    padding: 0 14px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #fff;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    text-decoration: none;
}

.delete-btn svg { width: 15px; height: 15px; }

@media (max-width: 1020px) {
    .info-grid { grid-template-columns: 1fr; }
}

@media (max-width: 760px) {
    .donation-show-page { padding: 16px; }
    .page-top, .amount-card, .danger-zone { flex-direction: column; align-items: flex-start; }
    .amount-side { justify-content: flex-start; }
}
</style>
