<template>
    <AppLayout>
        <section class="my-adoptions-page">
            <div class="page-shell">
                <header class="page-head">
                    <div>
                        <p class="page-kicker">My Account</p>
                        <h1>My Adoption Requests</h1>
                        <p class="page-copy">
                            Track every request you submitted and see whether it is still pending, approved, or rejected.
                        </p>
                    </div>
                    <Link href="/adopt" class="browse-link">Browse Pets</Link>
                </header>

                <div v-if="adoptions.length" class="requests-grid">
                    <article v-for="adoption in adoptions" :key="adoption.id" class="request-card">
                        <div class="request-top">
                            <div class="request-pet">
                                <img :src="petImage(adoption.pet?.image_path)" :alt="adoption.pet?.name || 'Pet image'">
                            </div>
                            <div class="request-head-copy">
                                <p class="request-label">Request #{{ adoption.id }}</p>
                                <h2>{{ adoption.pet?.name || 'Pet record unavailable' }}</h2>
                                <p class="request-meta">
                                    {{ adoption.pet?.species || 'Unknown species' }}
                                    <span v-if="adoption.pet?.breed">• {{ adoption.pet.breed }}</span>
                                </p>
                            </div>
                            <span class="status-pill" :class="statusClass(adoption.status)">
                                {{ adoption.status }}
                            </span>
                        </div>

                        <div class="request-body">
                            <div class="info-row">
                                <span class="info-label">Submitted</span>
                                <span class="info-value">{{ formatDate(adoption.created_at) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Updated</span>
                                <span class="info-value">{{ formatDate(adoption.updated_at) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Contact Email</span>
                                <span class="info-value break">{{ adoption.email }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Phone</span>
                                <span class="info-value">{{ adoption.phone || 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="decision-note" :class="statusClass(adoption.status)">
                            <template v-if="isApproved(adoption.status)">
                                Your adoption request has been approved. The shelter should contact you for the next steps.
                            </template>
                            <template v-else-if="isRejected(adoption.status)">
                                Your adoption request was rejected. You can still browse other pets and submit a new request.
                            </template>
                            <template v-else>
                                Your request is still under review. The shelter has not made a final decision yet.
                            </template>
                        </div>

                        <div v-if="adoption.message" class="request-message">
                            <p class="message-label">Your message</p>
                            <p>{{ adoption.message }}</p>
                        </div>
                    </article>
                </div>

                <div v-else class="empty-state">
                    <h2>No adoption requests yet</h2>
                    <p>You have not submitted any adoption applications. Start by browsing available pets.</p>
                    <Link href="/adopt" class="empty-cta">View Available Pets</Link>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps({
    adoptions: {
        type: Array,
        default: () => [],
    },
});

function isApproved(status) {
    return status === 'Approved';
}

function isRejected(status) {
    return status === 'Rejected';
}

function statusClass(status) {
    if (isApproved(status)) return 'is-approved';
    if (isRejected(status)) return 'is-rejected';
    return 'is-pending';
}

function formatDate(value) {
    if (!value) return 'N/A';

    return new Date(value).toLocaleDateString('en-US', {
        month: 'short',
        day: '2-digit',
        year: 'numeric',
    });
}

const petImage = (path) =>
    !path ? '' : /^https?:\/\//.test(path) ? path : `/storage/${path}`;
</script>

<style scoped>
.my-adoptions-page {
    background:
        radial-gradient(circle at top left, rgba(253, 186, 116, 0.24), transparent 30%),
        linear-gradient(180deg, #fffaf5 0%, #fff 40%, #fff8f1 100%);
    min-height: calc(100vh - 84px);
    padding: 38px 16px 60px;
}

.page-shell {
    width: min(100%, 1120px);
    margin: 0 auto;
}

.page-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 22px;
    padding: 22px 24px;
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(251, 146, 60, 0.18);
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.07);
}

.page-kicker {
    margin: 0 0 6px;
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #c2410c;
}

.page-head h1 {
    margin: 0;
    font-size: clamp(2rem, 4vw, 3rem);
    line-height: 1.04;
    color: #111827;
}

.page-copy {
    margin: 10px 0 0;
    max-width: 640px;
    font-size: 1rem;
    line-height: 1.6;
    color: #4b5563;
}

.browse-link,
.empty-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 46px;
    padding: 0 18px;
    border-radius: 999px;
    background: #ea580c;
    color: #fff;
    text-decoration: none;
    font-weight: 800;
    box-shadow: 0 14px 28px rgba(234, 88, 12, 0.22);
}

.requests-grid {
    display: grid;
    gap: 16px;
}

.request-card {
    padding: 18px;
    border-radius: 22px;
    background: #fff;
    border: 1px solid #fed7aa;
    box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
}

.request-top {
    display: grid;
    grid-template-columns: 88px minmax(0, 1fr) auto;
    gap: 16px;
    align-items: center;
}

.request-pet img {
    width: 88px;
    height: 88px;
    object-fit: cover;
    border-radius: 18px;
    display: block;
    border: 1px solid #fed7aa;
}

.request-label {
    margin: 0 0 4px;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #9a3412;
}

.request-head-copy h2 {
    margin: 0;
    font-size: 1.5rem;
    line-height: 1.1;
    color: #111827;
}

.request-meta {
    margin: 8px 0 0;
    color: #6b7280;
    font-weight: 600;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    padding: 0 12px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 800;
    border: 1px solid transparent;
    white-space: nowrap;
}

.request-body {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px 18px;
    margin-top: 16px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 14px;
    background: #fff7ed;
    border: 1px solid #ffedd5;
}

.info-label {
    color: #9a3412;
    font-weight: 700;
}

.info-value {
    color: #111827;
    font-weight: 700;
    text-align: right;
}

.break {
    overflow-wrap: anywhere;
}

.decision-note {
    margin-top: 14px;
    padding: 14px 15px;
    border-radius: 16px;
    font-weight: 700;
    line-height: 1.5;
    border: 1px solid transparent;
}

.request-message {
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #fed7aa;
}

.message-label {
    margin: 0 0 6px;
    font-size: 0.84rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #9a3412;
    font-weight: 800;
}

.request-message p:last-child {
    margin: 0;
    color: #374151;
    line-height: 1.6;
}

.is-pending {
    background: #fef3c7;
    color: #92400e;
    border-color: #fde68a;
}

.is-approved {
    background: #dcfce7;
    color: #166534;
    border-color: #bbf7d0;
}

.is-rejected {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fecaca;
}

.empty-state {
    padding: 44px 24px;
    text-align: center;
    border-radius: 24px;
    background: #fff;
    border: 1px dashed #fdba74;
    box-shadow: 0 18px 36px rgba(15, 23, 42, 0.05);
}

.empty-state h2 {
    margin: 0;
    font-size: 1.8rem;
    color: #111827;
}

.empty-state p {
    margin: 10px auto 20px;
    max-width: 520px;
    color: #6b7280;
    line-height: 1.6;
}

@media (max-width: 760px) {
    .page-head {
        flex-direction: column;
        align-items: flex-start;
    }

    .browse-link,
    .empty-cta {
        width: 100%;
    }

    .request-top {
        grid-template-columns: 1fr;
    }

    .request-pet img {
        width: 100%;
        height: 220px;
    }

    .request-body {
        grid-template-columns: 1fr;
    }

    .info-row {
        flex-direction: column;
    }

    .info-value {
        text-align: left;
    }
}
</style>
