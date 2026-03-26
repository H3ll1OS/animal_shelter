<template>
    <AdminLayout>
        <section class="vaccination-page">
            <header class="vaccination-head">
                <div>
                    <p class="head-kicker">Health Records</p>
                    <h2>Vaccination Schedule</h2>
                </div>
                <Link href="/admin/health/vaccination-schedule/create" class="add-schedule-btn">
                    <span aria-hidden="true">+</span>
                    Add New Schedule
                </Link>
            </header>

            <div v-if="flash.success" class="flash-success">{{ flash.success }}</div>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="vaccination-table">
                        <thead>
                            <tr>
                                <th>Pet Name</th>
                                <th>Vaccine Type</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="schedule in schedules.data" :key="schedule.id">
                                <td class="pet-name">{{ schedule.pet?.name || 'N/A' }}</td>
                                <td>{{ schedule.vaccine_type }}</td>
                                <td>
                                    <span :class="isPast(schedule.next_due_date) ? 'due-pill due-overdue' : 'due-pill due-upcoming'">
                                        {{ formatDate(schedule.next_due_date) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="state-pill" :class="schedule.is_completed ? 'state-complete' : 'state-pending'">
                                        {{ schedule.is_completed ? 'Completed' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="action-wrap">
                                        <Link
                                            v-if="!schedule.is_completed"
                                            :href="`/admin/health/vaccination-schedule/${schedule.id}/complete`"
                                            method="post"
                                            as="button"
                                            class="btn-complete"
                                        >
                                            Mark Complete
                                        </Link>
                                        <span v-else class="done-label">Done</span>

                                        <Link
                                            :href="`/admin/health/vaccination-schedule/${schedule.id}`"
                                            method="delete"
                                            as="button"
                                            class="btn-delete"
                                            @before="confirmDelete"
                                        >
                                            Delete
                                        </Link>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="schedules.data.length === 0">
                                <td colspan="5" class="empty-cell">No urgent vaccinations scheduled for the next 30 days.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <PaginationLinks :links="schedules.links" />
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
    schedules: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

function formatDate(v) {
    return v ? String(v).slice(0, 10) : 'N/A';
}

function isPast(v) {
    return v ? new Date(v) < new Date(new Date().toDateString()) : false;
}

function confirmDelete(visit) {
    if (!window.confirm('Are you sure you want to delete this schedule? This action is reversible.')) {
        visit.cancel();
    }
}
</script>

<style scoped>
.vaccination-page {
    max-width: 1180px;
    margin: 0 auto;
    padding: 24px;
}

.vaccination-head {
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

.vaccination-head h2 {
    margin: 0;
    font-size: clamp(1.6rem, 2.6vw, 2.05rem);
    line-height: 1.15;
    color: #0f172a;
}

.add-schedule-btn {
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

.vaccination-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    min-width: 860px;
}

.vaccination-table thead {
    background: #f8fafc;
}

.vaccination-table th,
.vaccination-table td {
    padding: 12px 14px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
    vertical-align: middle;
}

.vaccination-table th {
    font-size: 0.76rem;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #64748b;
}

.vaccination-table td {
    font-size: 0.95rem;
    color: #334155;
}

.pet-name {
    font-weight: 700;
    color: #0f172a;
}

.due-pill,
.state-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 0.24rem 0.62rem;
    font-size: 0.77rem;
    font-weight: 700;
}

.due-upcoming {
    background: #ffedd5;
    color: #9a3412;
}

.due-overdue {
    background: #fee2e2;
    color: #991b1b;
}

.state-complete {
    background: #dcfce7;
    color: #166534;
}

.state-pending {
    background: #fef3c7;
    color: #92400e;
}

.action-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-complete,
.btn-delete {
    min-height: 34px;
    padding: 0 11px;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 700;
    border: 1px solid transparent;
}

.btn-complete {
    background: #ecfdf3;
    border-color: #bbf7d0;
    color: #166534;
}

.btn-complete:hover {
    background: #dcfce7;
}

.btn-delete {
    background: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}

.btn-delete:hover {
    background: #fee2e2;
}

.done-label {
    color: #166534;
    font-weight: 700;
    font-size: 0.84rem;
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
    .vaccination-page {
        padding: 16px;
    }

    .vaccination-head {
        flex-direction: column;
        align-items: flex-start;
    }

    .add-schedule-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
