<template>
    <AdminLayout>
        <section class="donation-show-page">
            <div v-if="flash.success || flash.error || flash.warning" class="review-flash" :class="{ 'review-flash--error': flash.error, 'review-flash--warning': flash.warning }" role="status">
                {{ flash.error || flash.warning || flash.success }}
            </div>
            <header class="page-top">
                <div>
                    <Link href="/admin/donations" class="back-link">
                        <svg class="back-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Donation List
                    </Link>
                    <p class="page-kicker">Finance / Donation review</p>
                    <h1 class="page-title">Donation details</h1>
                </div>
                <span class="badge-id">Donation #{{ donation.id }}</span>
            </header>

            <article class="amount-card">
                <div>
                    <p class="label">Donation Amount</p>
                    <p class="amount">{{ formatMoney(donation.amount) }}</p>
                    <p class="meta">Submitted: {{ formatDateTimePretty(donation.created_at) }}</p>
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

            <article class="info-card receipt-card">
                <h3>Payment Receipt</h3>
                <p v-if="donation.has_receipt && !receiptUnavailable" class="receipt-copy">A payment receipt was uploaded with this donation.</p>
                <p v-else-if="receiptUnavailable" class="empty-notes">The receipt file is missing or cannot be accessed.</p>
                <p v-else class="empty-notes">No receipt was provided for this donation.</p>
                <a
                    v-if="donation.has_receipt && !receiptUnavailable"
                    href="#receipt-preview"
                    class="receipt-link"
                    @click.prevent="showReceipt = true"
                >
                    View receipt
                </a>
            </article>

            <article class="info-card verification-card">
                <h3>Donation Verification</h3>
                <p>Current status: <strong>{{ donation.status || 'Pending' }}</strong></p>
                <p v-if="donation.rejection_reason" class="notes">Rejection reason: {{ donation.rejection_reason }}</p>
                <p v-if="donation.verified_at" class="notes">
                    Verified<span v-if="donation.verifier"> by {{ donation.verifier.name }}</span> on {{ formatDateTimePretty(donation.verified_at) }}
                </p>
                <p v-if="donation.rejected_at" class="notes">
                    Rejected<span v-if="donation.rejecter"> by {{ donation.rejecter.name }}</span> on {{ formatDateTimePretty(donation.rejected_at) }}
                </p>
                <div v-if="(donation.status || 'Pending') === 'Pending'" class="verification-actions">
                    <button type="button" class="verify-btn" @click="showVerify = true">Verify donation</button>
                    <button type="button" class="reject-btn" @click="showReject = true">Reject donation</button>
                </div>
            </article>

            <section v-if="(donation.status || 'Pending') === 'Pending'" class="danger-zone">
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

            <div v-if="showVerify" class="review-modal-overlay" @click.self="showVerify = false">
                <section class="review-modal" role="dialog" aria-modal="true" aria-labelledby="verify-title">
                    <h2 id="verify-title">Confirm Donation Verification</h2>
                    <p>Are you sure you want to verify this donation?</p>
                    <p>This will mark the donation as verified and include its amount in the verified donation reports.</p>
                    <div class="modal-actions">
                        <button type="button" class="modal-cancel" @click="showVerify = false">Cancel</button>
                        <button type="button" class="verify-btn" :disabled="verificationForm.processing" @click="confirmVerification">
                            {{ verificationForm.processing ? 'Verifying...' : 'Confirm Verification' }}
                        </button>
                    </div>
                </section>
            </div>

            <div v-if="showReject" class="review-modal-overlay" @click.self="showReject = false">
                <form class="review-modal" @submit.prevent="confirmRejection">
                    <h2>Reject Donation</h2>
                    <p>Confirm that this donation cannot be accepted or verified. The donor's submission and receipt will remain on record.</p>
                    <label for="rejection_reason">Rejection reason <span aria-hidden="true">*</span></label>
                    <textarea id="rejection_reason" v-model="rejectionForm.rejection_reason" maxlength="1000" required></textarea>
                    <p v-if="rejectionForm.errors.rejection_reason" class="form-error">{{ rejectionForm.errors.rejection_reason }}</p>
                    <div class="modal-actions">
                        <button type="button" class="modal-cancel" @click="showReject = false">Cancel</button>
                        <button type="submit" class="reject-btn" :disabled="rejectionForm.processing">
                            {{ rejectionForm.processing ? 'Rejecting...' : 'Confirm Rejection' }}
                        </button>
                    </div>
                </form>
            </div>

            <div v-if="showReceipt" class="review-modal-overlay receipt-overlay" @click.self="showReceipt = false">
                <section class="receipt-modal" role="dialog" aria-modal="true" aria-label="Payment receipt">
                    <button type="button" class="receipt-close" aria-label="Close receipt" @click="showReceipt = false">&times;</button>
                    <img
                        :src="`/admin/donations/${donation.id}/receipt`"
                        :alt="`Payment receipt for donation ${donation.id}`"
                        @error="receiptUnavailable = true; showReceipt = false"
                    >
                </section>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    donation: {
        type: Object,
        required: true,
    },
});

const showReject = ref(false);
const showVerify = ref(false);
const showReceipt = ref(false);
const receiptUnavailable = ref(false);
const page = usePage();
const flash = computed(() => page.props.flash || {});
const verificationForm = useForm({});
const rejectionForm = useForm({ rejection_reason: '' });

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
    if (status === 'Verified') return 'status-completed';
    if (status === 'Pending') return 'status-pending';
    return 'status-failed';
}

function confirmVerification() {
    verificationForm.post(`/admin/donations/${props.donation.id}/verify`, {
        onSuccess: () => {
            showVerify.value = false;
        },
    });
}

function confirmRejection() {
    rejectionForm.post(`/admin/donations/${props.donation.id}/reject`, {
        onSuccess: () => {
            showReject.value = false;
            rejectionForm.reset();
        },
    });
}

function confirmDelete(visit) {
    if (!window.confirm('WARNING: Are you sure you want to permanently delete this donation record? This cannot be undone.')) {
        visit.cancel();
    }
}
</script>

<style scoped>
.donation-show-page {
    max-width: 1280px;
    margin: 0 auto;
    padding: 24px clamp(16px, 2.5vw, 32px) 36px;
}

.page-top {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 20px;
}

.back-link {
    color: #64748b;
    text-decoration: none;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 0.84rem;
}

.back-link:hover { color: #ea580c; }

.back-icon { width: 16px; height: 16px; }

.page-kicker {
    margin: 16px 0 4px;
    color: #ea580c;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.11em;
    text-transform: uppercase;
}

.page-title {
    margin: 0;
    color: #172033;
    font-size: clamp(1.7rem, 3vw, 2.15rem);
    letter-spacing: -0.035em;
    line-height: 1.12;
}

.badge-id {
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 999px;
    min-height: 36px;
    padding: 0 12px;
    font-size: 0.8rem;
    font-weight: 700;
    color: #475569;
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
}

.amount-card {
    border: 1px solid #e8edf4;
    border-radius: 18px;
    background: linear-gradient(120deg, #fff 0%, #f8fafc 100%);
    padding: clamp(20px, 3vw, 28px);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.045);
}

.label {
    margin: 0;
    color: #64748b;
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0.09em;
    text-transform: uppercase;
}

.amount {
    margin: 8px 0 0;
    font-size: clamp(2rem, 4vw, 2.75rem);
    line-height: 1.05;
    font-weight: 800;
    letter-spacing: -0.04em;
    color: #172033;
}

.meta {
    margin: 9px 0 0;
    color: #64748b;
    font-size: 0.88rem;
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
    min-height: 34px;
    padding: 0 12px;
    display: inline-flex;
    align-items: center;
    font-size: 0.75rem;
    font-weight: 700;
}

.txn-pill {
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #334155;
}

.status-completed { background: #ecfdf3; color: #15803d; }
.status-pending { background: #fff7df; color: #a16207; }
.status-failed { background: #fff1f2; color: #be123c; }

.review-flash {
    margin-bottom: 16px;
    padding: 13px 16px;
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    background: #f0fdf4;
    color: #166534;
    font-weight: 700;
}

.review-flash--error {
    border-color: #fecaca;
    background: #fef2f2;
    color: #991b1b;
}

.review-flash--warning {
    border-color: #fde68a;
    background: #fffbeb;
    color: #92400e;
}

.info-grid {
    margin-top: 18px;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.info-card {
    min-width: 0;
    border: 1px solid #e8edf4;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    padding: 20px;
}

.info-card h3 {
    margin: 0 0 16px;
    color: #172033;
    font-size: 1.02rem;
    font-weight: 750;
    letter-spacing: -0.01em;
}

.info-list {
    margin: 0;
    display: grid;
    gap: 10px;
}

.info-list div {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 14px;
    border-bottom: 1px solid #edf1f6;
    padding: 0 0 10px;
}

.info-list div:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}

.info-list dt { color: #64748b; font-size: 0.84rem; font-weight: 600; }
.info-list dd { margin: 0; color: #172033; font-size: 0.84rem; font-weight: 700; text-align: right; overflow-wrap: anywhere; }

.accent { color: #c2410c !important; }
.capitalize { text-transform: capitalize; }

.notes { margin: 0; color: #0f172a; line-height: 1.65; }
.empty-notes { margin: 0; color: #64748b; font-style: italic; }
.receipt-card { display: flex; flex-direction: column; align-items: flex-start; gap: 10px; margin-top: 16px; }
.receipt-copy { margin: 0; color: #475569; }
.receipt-link { display: inline-flex; min-height: 40px; align-items: center; border-radius: 10px; background: #ea580c; padding: 0 16px; color: #fff; font-size: 0.84rem; font-weight: 700; text-decoration: none; transition: background 0.18s ease, transform 0.18s ease; }
.receipt-link:hover { background: #c2410c; transform: translateY(-1px); }

.verification-card {
    margin-top: 16px;
    background: linear-gradient(180deg, #fff 0%, #fcfcfd 100%);
}

.verification-card > p {
    margin: 0 0 10px;
    color: #475569;
    line-height: 1.55;
}

.verification-card > p strong {
    color: #172033;
}

.verification-actions,
.modal-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 18px;
}

.verify-btn,
.reject-btn,
.modal-cancel {
    min-height: 40px;
    border-radius: 10px;
    padding: 0 16px;
    font: inherit;
    font-weight: 800;
    cursor: pointer;
    transition: background 0.18s ease, border-color 0.18s ease, transform 0.18s ease;
}

.verify-btn {
    border: 1px solid #16a34a;
    background: #16a34a;
    color: #fff;
}

.verify-btn:hover:not(:disabled) {
    border-color: #15803d;
    background: #15803d;
    transform: translateY(-1px);
}

.reject-btn {
    border: 1px solid #dc2626;
    background: #dc2626;
    color: #fff;
}

.reject-btn:hover:not(:disabled) {
    border-color: #b91c1c;
    background: #b91c1c;
    transform: translateY(-1px);
}

.modal-cancel {
    border: 1px solid #dbe3ee;
    background: #fff;
    color: #334155;
}

.verify-btn:disabled,
.reject-btn:disabled {
    cursor: wait;
    opacity: 0.65;
}

.review-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 1100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px;
    background: rgba(15, 23, 42, 0.62);
}

.review-modal {
    width: min(100%, 480px);
    max-height: 90vh;
    overflow-y: auto;
    margin: 0;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 22px;
    background: #fff;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
}

.review-modal h2 {
    margin: 0 0 12px;
    color: #0f172a;
    font-size: 1.35rem;
}

.review-modal p {
    color: #475569;
    line-height: 1.55;
}

.review-modal label {
    display: block;
    margin: 14px 0 6px;
    color: #334155;
    font-weight: 700;
}

.review-modal textarea {
    display: block;
    width: 100%;
    min-height: 110px;
    resize: vertical;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    padding: 10px;
    font: inherit;
}

.review-modal textarea:focus {
    outline: 2px solid #fed7aa;
    border-color: #f97316;
}

.form-error {
    margin-bottom: 0;
    color: #b91c1c !important;
    font-size: 0.9rem;
}

.modal-actions {
    justify-content: flex-end;
    margin-top: 18px;
}

.receipt-overlay {
    z-index: 1110;
}

.receipt-modal {
    position: relative;
    max-width: min(100%, 1000px);
    max-height: 90vh;
    padding: 42px 12px 12px;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.3);
}

.receipt-modal img {
    display: block;
    max-width: 100%;
    max-height: calc(90vh - 60px);
    object-fit: contain;
}

.receipt-close {
    position: absolute;
    top: 7px;
    right: 10px;
    width: 32px;
    height: 32px;
    border: 0;
    border-radius: 50%;
    background: #f1f5f9;
    color: #0f172a;
    font-size: 1.5rem;
    cursor: pointer;
}

.danger-zone {
    margin-top: 16px;
    border: 1px solid #fee2e2;
    border-radius: 16px;
    background: #fff;
    padding: 18px 20px;
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
    min-height: 40px;
    border: 1px solid #fee2e2;
    border-radius: 10px;
    padding: 0 14px;
    background: #fff;
    color: #b91c1c;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.18s ease, border-color 0.18s ease;
}

.delete-btn:hover {
    border-color: #fecaca;
    background: #fff1f2;
}

.delete-btn svg { width: 15px; height: 15px; }

@media (max-width: 1020px) {
    .info-grid { grid-template-columns: 1fr; }
}

@media (max-width: 760px) {
    .donation-show-page { padding: 20px 14px 28px; }
    .page-top { align-items: flex-start; }
    .amount-card, .danger-zone { flex-direction: column; align-items: flex-start; }
    .amount-side { justify-content: flex-start; }
    .info-card { padding: 17px; }
    .danger-zone { width: 100%; }
}

@media (max-width: 420px) {
    .page-top { flex-wrap: wrap; }
    .page-kicker { margin-top: 12px; }
    .amount-side { justify-content: flex-start; }
    .verification-actions > button { flex: 1 1 100%; }
}
</style>
