<template>
    <AdminLayout>
        <section class="euthanasia-page">
            <header class="euthanasia-head">
                <div>
                    <p class="head-kicker">Health Records</p>
                    <h2>Euthanasia Record History</h2>
                </div>
                <Link href="/admin/health/euthanasia/create" class="add-record-btn">
                    <span aria-hidden="true">+</span>
                    Add Record
                </Link>
            </header>

            <div v-if="flash.success" class="flash-success">{{ flash.success }}</div>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="euthanasia-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Pet Name</th>
                                <th>Reason</th>
                                <th>Veterinarian</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="log in logs.data" :key="log.id">
                                <td class="date-cell">{{ formatDate(log.euthanasia_date) }}</td>
                                <td class="pet-cell">{{ log.pet?.name || 'N/A (Record Missing)' }}</td>
                                <td class="reason-cell">{{ log.reason }}</td>
                                <td>{{ log.veterinarian || 'N/A' }}</td>
                                <td class="text-center">
                                    <div class="action-wrap">
                                        <Link :href="`/admin/health/euthanasia/${log.id}`" class="view-link">View</Link>

                                        <Link
                                            :href="`/admin/health/euthanasia/${log.id}/edit`"
                                            class="icon-btn icon-btn-edit"
                                            title="Edit"
                                            aria-label="Edit"
                                        >
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4 12.5-12.5z" />
                                            </svg>
                                        </Link>

                                        <Link
                                            :href="`/admin/health/euthanasia/${log.id}`"
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
                                </td>
                            </tr>

                            <tr v-if="logs.data.length === 0">
                                <td colspan="5" class="empty-cell">No euthanasia records found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <PaginationLinks :links="logs.links" />
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
    logs: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

function formatDate(v) {
    return v ? String(v).slice(0, 10) : 'N/A';
}

function confirmDelete(visit) {
    if (!window.confirm('Delete this record?')) visit.cancel();
}
</script>

<style scoped>
.euthanasia-page {
    max-width: 1180px;
    margin: 0 auto;
    padding: 24px;
}

.euthanasia-head {
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

.euthanasia-head h2 {
    margin: 0;
    font-size: clamp(1.6rem, 2.6vw, 2.05rem);
    line-height: 1.15;
    color: #0f172a;
}

.add-record-btn {
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

.euthanasia-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    min-width: 920px;
}

.euthanasia-table thead {
    background: #f8fafc;
}

.euthanasia-table th,
.euthanasia-table td {
    padding: 12px 14px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
    vertical-align: middle;
}

.euthanasia-table th {
    font-size: 0.76rem;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #64748b;
}

.euthanasia-table td {
    font-size: 0.95rem;
    color: #334155;
}

.date-cell {
    font-weight: 700;
    color: #0f172a;
}

.pet-cell {
    font-weight: 700;
    color: #0f172a;
}

.reason-cell {
    color: #991b1b;
    font-weight: 600;
}

.action-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
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

.icon-btn {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    border: 1px solid transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
}

.icon-btn svg {
    width: 16px;
    height: 16px;
}

.icon-btn-edit {
    color: #1d4ed8;
    border-color: #bfdbfe;
    background: #eff6ff;
}

.icon-btn-delete {
    color: #dc2626;
    border-color: #fecaca;
    background: #fef2f2;
    cursor: pointer;
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
    .euthanasia-page {
        padding: 16px;
    }

    .euthanasia-head {
        flex-direction: column;
        align-items: flex-start;
    }

    .add-record-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
