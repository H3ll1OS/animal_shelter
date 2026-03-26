<template>
    <AdminLayout>
        <section class="monitoring-show-page">
            <div v-if="flash.success" class="flash-success">{{ flash.success }}</div>

            <article class="alert-show-card">
                <header class="alert-show-head">
                    <div>
                        <p class="head-kicker">Health Alert</p>
                        <h2>{{ alert.pet?.name || 'Unknown Pet' }}</h2>
                        <p class="head-sub">Reported: {{ formatDateLong(alert.created_at) }}</p>
                    </div>

                    <div class="head-actions">
                        <span class="severity-badge" :class="alert.is_critical ? 'is-critical' : 'is-normal'">
                            {{ alert.is_critical ? 'Critical' : 'Info' }}
                        </span>

                        <Link :href="`/admin/health/monitoring/${alert.id}/edit`" class="action-btn action-btn-edit" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4 12.5-12.5z" />
                            </svg>
                            <span>Edit</span>
                        </Link>

                        <Link
                            :href="`/admin/health/monitoring/${alert.id}`"
                            method="delete"
                            as="button"
                            class="action-btn action-btn-delete"
                            @before="confirmDelete"
                            title="Delete"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline stroke-linecap="round" stroke-linejoin="round" points="3 6 5 6 21 6" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4a1 1 0 011-1h6a1 1 0 011 1v2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14a1 1 0 01-1 1H7a1 1 0 01-1-1L5 6" />
                                <line x1="10" y1="11" x2="10" y2="17" />
                                <line x1="14" y1="11" x2="14" y2="17" />
                            </svg>
                            <span>Delete</span>
                        </Link>
                    </div>
                </header>

                <div class="alert-show-body">
                    <div class="info-block">
                        <p class="label">Diagnosis</p>
                        <p class="value strong">{{ alert.diagnosis || 'N/A' }}</p>
                    </div>

                    <div class="info-block" v-if="alert.description">
                        <p class="label">Description</p>
                        <p class="value">{{ alert.description }}</p>
                    </div>

                    <div class="info-grid">
                        <div class="info-block" v-if="alert.veterinarian">
                            <p class="label">Veterinarian</p>
                            <p class="value">{{ alert.veterinarian }}</p>
                        </div>

                        <div class="info-block" v-if="alert.notes">
                            <p class="label">Notes</p>
                            <p class="value">{{ alert.notes }}</p>
                        </div>
                    </div>
                </div>
            </article>

            <div class="back-link-wrap">
                <Link href="/admin/health/monitoring" class="back-link">
                    <svg class="back-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to monitoring
                </Link>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    alert: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

function formatDateLong(value) {
    return value
        ? new Date(value).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
        : 'N/A';
}

function confirmDelete(visit) {
    if (!window.confirm('Delete this alert?')) visit.cancel();
}
</script>

<style scoped>
.monitoring-show-page {
    max-width: 980px;
    margin: 0 auto;
    padding: 16px 20px 22px;
}

.flash-success {
    margin-bottom: 12px;
    border: 1px solid #bbf7d0;
    background: #dcfce7;
    color: #166534;
    border-radius: 11px;
    padding: 10px 12px;
    font-weight: 700;
}

.alert-show-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    overflow: hidden;
}

.alert-show-head {
    padding: 16px 18px;
    border-bottom: 1px solid #e2e8f0;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.head-kicker {
    margin: 0 0 4px;
    font-size: 0.76rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #64748b;
}

.alert-show-head h2 {
    margin: 0;
    color: #0f172a;
    font-size: clamp(1.7rem, 2.8vw, 2.15rem);
    line-height: 1.1;
}

.head-sub {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 0.9rem;
}

.head-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.severity-badge {
    font-size: 0.73rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 5px 9px;
    border-radius: 999px;
    border: 1px solid transparent;
}

.is-normal {
    background: #e2e8f0;
    color: #334155;
    border-color: #cbd5e1;
}

.is-critical {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fecaca;
}

.action-btn {
    min-height: 36px;
    border-radius: 10px;
    border: 1px solid #d1d5db;
    background: #fff;
    color: #334155;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0 10px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 700;
}

.action-btn svg {
    width: 15px;
    height: 15px;
}

.action-btn-edit {
    border-color: #bfdbfe;
    background: #eff6ff;
    color: #1d4ed8;
}

.action-btn-delete {
    border-color: #fecaca;
    background: #fef2f2;
    color: #dc2626;
    cursor: pointer;
}

.alert-show-body {
    padding: 16px 18px;
    display: grid;
    gap: 14px;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.info-block {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #f8fafc;
    padding: 11px 12px;
}

.label {
    margin: 0;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.value {
    margin: 6px 0 0;
    color: #0f172a;
    line-height: 1.55;
}

.value.strong {
    font-size: 1.2rem;
    font-weight: 800;
}

.back-link-wrap {
    margin-top: 12px;
    display: flex;
    justify-content: center;
}

.back-link {
    color: #475569;
    text-decoration: none;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.back-link:hover {
    color: #0f172a;
}

.back-icon {
    width: 17px;
    height: 17px;
}

@media (max-width: 840px) {
    .alert-show-head {
        flex-direction: column;
    }

    .head-actions {
        justify-content: flex-start;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 680px) {
    .monitoring-show-page {
        padding: 16px;
    }
}
</style>
