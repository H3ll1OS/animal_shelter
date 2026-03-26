<template>
    <AdminLayout>
        <section class="application-show">
            <header class="page-head">
                <div>
                    <p class="page-kicker">Adoption Review</p>
                    <h1>Application #{{ adoption.id }}</h1>
                </div>
                <div class="head-status">
                    <p class="status-line">Status: <span :class="statusTextClass(adoption.status)">{{ adoption.status }}</span></p>
                    <p class="submitted-line">Submitted: {{ formatLongDate(adoption.created_at) }}</p>
                </div>
            </header>

            <div v-if="isPending" class="decision-panel">
                <div class="decision-copy">
                    <p class="decision-kicker">Decision Needed</p>
                    <p>Review and finalize this application.</p>
                </div>
                <div class="decision-actions">
                    <Link
                        :href="`/admin/adoptions/${adoption.id}/approve`"
                        method="post"
                        as="button"
                        class="decision-btn decision-btn-approve"
                        @before="confirmApprove"
                    >
                        <span aria-hidden="true">&#10003;</span> Approve
                    </Link>
                    <Link
                        :href="`/admin/adoptions/${adoption.id}/reject`"
                        method="post"
                        as="button"
                        class="decision-btn decision-btn-reject"
                        @before="confirmReject"
                    >
                        <span aria-hidden="true">&#10005;</span> Reject
                    </Link>
                </div>
            </div>

            <div class="details-grid">
                <article class="panel panel-applicant">
                    <h2>Applicant Details</h2>
                    <dl class="info-list">
                        <div><dt>Name</dt><dd>{{ adoption.applicant_name || adoption.full_name || 'N/A' }}</dd></div>
                        <div><dt>Email</dt><dd class="break">{{ adoption.email || 'N/A' }}</dd></div>
                        <div><dt>Phone</dt><dd>{{ adoption.phone || 'N/A' }}</dd></div>
                        <div><dt>Address</dt><dd>{{ adoption.address || 'N/A' }}</dd></div>
                        <div class="info-docs">
                            <dt>Supporting Documents</dt>
                            <dd>
                                <div v-for="(doc, idx) in documents" :key="idx" class="doc-link-wrap">
                                    <a :href="`/storage/${doc}`" class="doc-link" target="_blank" rel="noopener">
                                        View / Download {{ documents.length > 1 ? `Document ${idx + 1}` : '' }}
                                    </a>
                                </div>
                                <span v-if="documents.length === 0" class="muted">No document uploaded</span>
                            </dd>
                        </div>
                        <div v-if="adoption.user">
                            <dt>Registered User</dt>
                            <dd>{{ adoption.user.name }}</dd>
                        </div>
                    </dl>
                </article>

                <article class="panel panel-pet">
                    <h2>Pet Applied For</h2>
                    <dl v-if="adoption.pet" class="info-list">
                        <div>
                            <dt>Pet Name</dt>
                            <dd>
                                <Link :href="`/admin/pets/${adoption.pet.id}`" class="pet-link">{{ adoption.pet.name }}</Link>
                            </dd>
                        </div>
                        <div><dt>Species</dt><dd>{{ adoption.pet.species }}</dd></div>
                        <div><dt>Breed</dt><dd>{{ adoption.pet.breed || 'Unknown' }}</dd></div>
                        <div><dt>Age</dt><dd>{{ adoption.pet.age }} years</dd></div>
                    </dl>
                    <p v-else class="muted danger">Pet record not found or was deleted.</p>
                </article>

                <article class="panel panel-answers">
                    <h2>Application Answers</h2>
                    <div class="answer-grid">
                        <div class="answer-card">
                            <dt>Living Situation (Apartment/House/Yard)</dt>
                            <dd>{{ adoption.housing_type || 'No Answer Provided' }}</dd>
                        </div>
                        <div class="answer-card">
                            <dt>Prior Pet Experience</dt>
                            <dd>{{ adoption.pet_experience || 'No Answer Provided' }}</dd>
                        </div>
                        <div class="answer-card">
                            <dt>Reason for Adopting This Pet</dt>
                            <dd>{{ adoption.reason_for_adoption || 'No Answer Provided' }}</dd>
                        </div>
                    </div>
                </article>
            </div>

            <div class="back-link-wrap">
                <Link href="/admin/adoptions" class="back-link">
                    <svg class="back-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Return to Applications List
                </Link>
            </div>
        </section>
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
    if (status === 'Pending Review' || status === 'Pending') return 'status-pending';
    if (status === 'Approved') return 'status-approved';
    return 'status-rejected';
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

<style scoped>
.application-show {
    max-width: 1180px;
    margin: 0 auto;
    padding: 24px;
}

.page-head {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
    padding: 20px 22px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
}

.page-kicker {
    margin: 0 0 6px;
    font-size: 0.76rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #64748b;
}

.page-head h1 {
    margin: 0;
    font-size: clamp(1.85rem, 3vw, 2.35rem);
    line-height: 1.1;
    color: #0f172a;
}

.head-status {
    text-align: right;
}

.status-line {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #334155;
}

.status-pending { color: #ca8a04; }
.status-approved { color: #16a34a; }
.status-rejected { color: #dc2626; }

.submitted-line {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 0.86rem;
}

.decision-panel {
    margin-top: 16px;
    border: 1px solid #fed7aa;
    border-radius: 16px;
    background: linear-gradient(135deg, #fff7ed 0%, #ffffff 48%, #ffedd5 100%);
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.decision-kicker {
    margin: 0;
    color: #c2410c;
    font-size: 0.76rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.decision-copy p:last-child {
    margin: 3px 0 0;
    font-size: 1.1rem;
    color: #0f172a;
    font-weight: 700;
}

.decision-actions {
    display: flex;
    gap: 10px;
}

.decision-btn {
    min-height: 40px;
    border-radius: 999px;
    padding: 0 16px;
    border: 0;
    color: #fff;
    font-size: 0.92rem;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.decision-btn-approve {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
}

.decision-btn-reject {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.details-grid {
    margin-top: 16px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.panel {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
    padding: 18px;
}

.panel h2 {
    margin: 0 0 14px;
    font-size: 1.5rem;
    line-height: 1.15;
    color: #0f172a;
}

.panel-applicant,
.panel-pet {
    min-height: 320px;
}

.panel-answers {
    grid-column: 1 / -1;
}

.info-list {
    margin: 0;
    display: grid;
    gap: 10px;
}

.info-list div {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 11px;
    border: 1px solid #e2e8f0;
    border-radius: 11px;
    background: #f8fafc;
}

.info-list dt {
    color: #475569;
    font-weight: 700;
}

.info-list dd {
    margin: 0;
    text-align: right;
    color: #0f172a;
    font-weight: 700;
}

.info-list .break {
    overflow-wrap: anywhere;
}

.info-docs {
    align-items: flex-start;
}

.info-docs dd {
    text-align: right;
}

.doc-link-wrap + .doc-link-wrap {
    margin-top: 4px;
}

.doc-link {
    color: #2563eb;
    font-weight: 700;
    text-decoration: none;
}

.doc-link:hover {
    text-decoration: underline;
}

.muted {
    color: #64748b;
    font-weight: 600;
}

.danger {
    color: #dc2626;
}

.pet-link {
    color: #0f766e;
    text-decoration: none;
}

.pet-link:hover {
    text-decoration: underline;
}

.answer-grid {
    display: grid;
    gap: 10px;
}

.answer-card {
    border: 1px solid #e2e8f0;
    border-radius: 11px;
    background: #f8fafc;
    padding: 11px 12px;
}

.answer-card dt {
    color: #334155;
    font-weight: 700;
}

.answer-card dd {
    margin: 5px 0 0;
    color: #0f172a;
}

.back-link-wrap {
    margin-top: 18px;
    display: flex;
    justify-content: center;
}

.back-link {
    color: #475569;
    text-decoration: none;
    font-weight: 700;
    font-size: 1.05rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.back-link:hover {
    color: #0f172a;
}

.back-icon {
    width: 18px;
    height: 18px;
}

@media (max-width: 1080px) {
    .details-grid {
        grid-template-columns: 1fr;
    }

    .panel-applicant,
    .panel-pet {
        min-height: 0;
    }
}

@media (max-width: 760px) {
    .application-show {
        padding: 16px;
    }

    .page-head {
        flex-direction: column;
    }

    .head-status {
        text-align: left;
    }

    .decision-panel {
        flex-direction: column;
        align-items: flex-start;
    }

    .decision-actions {
        width: 100%;
        flex-direction: column;
    }

    .decision-btn {
        width: 100%;
        justify-content: center;
    }

    .info-list div {
        flex-direction: column;
        align-items: flex-start;
    }

    .info-list dd,
    .info-docs dd {
        text-align: left;
    }
}
</style>
