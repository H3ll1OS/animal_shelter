<template>
    <AdminLayout>
        <section class="monitoring-page">
            <header class="monitoring-head">
                <div>
                    <p class="head-kicker">Health Monitoring</p>
                    <h2>Pets Requiring Attention</h2>
                </div>
                <Link href="/admin/health/monitoring/create" class="add-alert-btn">
                    <span aria-hidden="true">+</span>
                    Add Alert
                </Link>
            </header>

            <div v-if="flash.success" class="flash-success">{{ flash.success }}</div>

            <div v-if="alerts.data.length" class="alerts-grid">
                <article v-for="alert in alerts.data" :key="alert.id" class="alert-card">
                    <div class="alert-main">
                        <div class="alert-top">
                            <h4>{{ alert.pet?.name || 'Unknown Pet' }} <span class="pet-status">({{ alert.pet?.status || 'N/A' }})</span></h4>
                            <span class="severity-badge" :class="alert.is_critical ? 'is-critical' : 'is-normal'">
                                {{ alert.is_critical ? 'Critical' : 'Normal' }}
                            </span>
                        </div>

                        <p class="meta-row">
                            <span><strong>Diagnosis:</strong> {{ alert.diagnosis || alert.description || 'N/A' }}</span>
                            <span class="dot" aria-hidden="true">&middot;</span>
                            <span><strong>Reported:</strong> {{ formatDate(alert.created_at) }}</span>
                        </p>

                        <p v-if="alert.veterinarian" class="vet-row"><strong>Veterinarian:</strong> {{ alert.veterinarian }}</p>
                    </div>

                    <div class="alert-actions" aria-label="Alert actions">
                        <Link :href="`/admin/health/monitoring/${alert.id}`" class="view-text-btn" title="View" aria-label="View">
                            View
                        </Link>

                        <Link :href="`/admin/health/monitoring/${alert.id}/edit`" class="icon-btn icon-btn-edit" title="Edit" aria-label="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4 12.5-12.5z" />
                            </svg>
                        </Link>

                        <Link
                            :href="`/admin/health/monitoring/${alert.id}`"
                            method="delete"
                            as="button"
                            class="icon-btn icon-btn-delete"
                            title="Delete"
                            aria-label="Delete"
                            @before="confirmDelete"
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
                </article>
            </div>

            <div v-else class="empty-state">
                <p>All health statuses are nominal. No alerts logged.</p>
            </div>

            <div class="pagination-wrap">
                <PaginationLinks :links="alerts.links" />
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PaginationLinks from '@/components/common/PaginationLinks.vue';

defineProps({ alerts: { type: Object, required: true } });

const page = usePage();
const flash = computed(() => page.props.flash || {});

function formatDate(v) {
    return v ? String(v).slice(0, 10) : 'N/A';
}

function confirmDelete(visit) {
    if (!window.confirm('Delete this alert?')) visit.cancel();
}
</script>

<style scoped>
.monitoring-page {
    max-width: 1180px;
    margin: 0 auto;
    padding: 24px;
}

.monitoring-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
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

.monitoring-head h2 {
    margin: 0;
    font-size: clamp(1.55rem, 2.5vw, 2rem);
    color: #0f172a;
    line-height: 1.15;
}

.add-alert-btn {
    min-height: 42px;
    padding: 0 16px;
    border-radius: 11px;
    background: linear-gradient(135deg, #fb923c 0%, #f97316 70%, #ea580c 100%);
    color: #fff;
    font-weight: 800;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 10px 24px rgba(249, 115, 22, 0.25);
}

.flash-success {
    margin-bottom: 14px;
    border: 1px solid #bbf7d0;
    background: #dcfce7;
    color: #166534;
    border-radius: 11px;
    padding: 10px 12px;
    font-weight: 700;
}

.alerts-grid {
    display: grid;
    gap: 12px;
}

.alert-card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #ffffff;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    padding: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.alert-main {
    min-width: 0;
}

.alert-top {
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-top h4 {
    margin: 0;
    font-size: 1.35rem;
    line-height: 1.2;
    color: #0f172a;
}

.pet-status {
    color: #475569;
    font-size: 1.05rem;
    font-weight: 700;
}

.severity-badge {
    font-size: 0.73rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 4px 8px;
    border-radius: 999px;
    border: 1px solid transparent;
}

.is-normal {
    background: #dcfce7;
    color: #166534;
    border-color: #bbf7d0;
}

.is-critical {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fecaca;
}

.meta-row,
.vet-row {
    margin: 8px 0 0;
    color: #334155;
    line-height: 1.55;
}

.dot {
    color: #94a3b8;
    margin: 0 8px;
}

.alert-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.icon-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1px solid #d1d5db;
    background: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #475569;
    transition: all 0.18s ease;
    text-decoration: none;
}

.icon-btn svg {
    width: 17px;
    height: 17px;
}

.icon-btn-view:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    color: #0f172a;
}

.view-text-btn {
    min-height: 36px;
    border-radius: 10px;
    border: 1px solid #d1d5db;
    background: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 12px;
    color: #334155;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 700;
    transition: all 0.18s ease;
}

.view-text-btn:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    color: #0f172a;
}

.icon-btn-edit {
    color: #1d4ed8;
    border-color: #bfdbfe;
    background: #eff6ff;
}

.icon-btn-edit:hover {
    background: #dbeafe;
    border-color: #93c5fd;
}

.icon-btn-delete {
    color: #dc2626;
    border-color: #fecaca;
    background: #fef2f2;
    cursor: pointer;
}

.icon-btn-delete:hover {
    background: #fee2e2;
    border-color: #fca5a5;
}

.empty-state {
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    background: #f8fafc;
    padding: 18px;
    color: #475569;
    font-weight: 600;
}

.pagination-wrap {
    margin-top: 16px;
}

@media (max-width: 920px) {
    .alert-card {
        flex-direction: column;
        align-items: flex-start;
    }

    .alert-actions {
        width: 100%;
        justify-content: flex-end;
    }
}

@media (max-width: 680px) {
    .monitoring-page {
        padding: 16px;
    }

    .monitoring-head {
        flex-direction: column;
        align-items: flex-start;
    }

    .add-alert-btn {
        width: 100%;
        justify-content: center;
    }

    .alert-top {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }

    .meta-row {
        display: block;
    }

    .dot {
        display: none;
    }
}
</style>
