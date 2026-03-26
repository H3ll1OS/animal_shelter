<template>
    <AdminLayout>
        <section class="dashboard-wrap admin-page-container">
            <div class="dashboard-top">
                <div>
                    <p class="dashboard-kicker">Overview</p>
                    <h2 class="dashboard-title">Welcome back, Admin</h2>
                    <p class="dashboard-subtitle">Track adoptions, pet inventory, and donations in one place.</p>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card stat-indigo">
                    <p class="stat-label">Total Pets</p>
                    <p class="stat-value">{{ totalPets }}</p>
                </div>
                <div class="stat-card stat-green">
                    <p class="stat-label">Ready for Adoption</p>
                    <p class="stat-value">{{ availablePets }}</p>
                </div>
                <div class="stat-card stat-amber">
                    <p class="stat-label">New Adoption Apps</p>
                    <p class="stat-value">{{ newApplications }}</p>
                </div>
                <div class="stat-card stat-cyan">
                    <p class="stat-label">Monthly Donations</p>
                    <p class="stat-value">{{ formatMoney(monthlyDonations) }}</p>
                </div>
            </div>

            <section class="panel panel--table">
                <div class="panel-head">
                    <h3>Recent Adoption Applications</h3>
                    <Link href="/admin/adoptions" class="panel-link">See all</Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="dashboard-table">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="pl-8 pr-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Applicant</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pet Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <tr
                                v-for="application in recentApplications"
                                :key="application.id"
                                class="hover:bg-slate-50 cursor-pointer"
                                @click="router.visit(`/admin/adoptions/${application.id}`)"
                            >
                                <td class="pl-8 pr-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ application.applicant_name || application.full_name || 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ application.pet?.name || 'N/A' }} ({{ application.pet?.species || 'N/A' }})</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ formatDate(application.created_at) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClass(application.status)">{{ application.status }}</span>
                                </td>
                            </tr>
                            <tr v-if="recentApplications.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">No recent applications found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel panel--actions">
                <div class="panel-head panel-head--no-border">
                    <h3>Quick Actions</h3>
                </div>
                <div class="actions-grid">
                    <Link href="/admin/pets/create" class="action-btn action-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        <span>Add New Pet</span>
                    </Link>
                    <Link href="/admin/adoptions" class="action-btn action-neutral">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <span>View All Applications</span>
                    </Link>
                </div>
            </section>
        </section>
    </AdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    totalPets: Number,
    availablePets: Number,
    newApplications: Number,
    monthlyDonations: [Number, String],
    recentApplications: { type: Array, default: () => [] },
});

function formatMoney(value) {
    return `\u20b1${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatDate(value) {
    if (!value) return 'N/A';
    return String(value).slice(0, 10);
}

function statusClass(status) {
    if (status === 'Pending Review') return 'bg-amber-100 text-amber-800';
    if (status === 'Approved') return 'bg-emerald-100 text-emerald-800';
    return 'bg-rose-100 text-rose-800';
}
</script>

<style scoped>
.dashboard-wrap {
    padding: clamp(1rem, 2vw, 1.75rem);
    display: grid;
    gap: 1.25rem;
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 18px;
    border: 1px solid #e2e8f0;
}

.dashboard-kicker {
    font-size: 0.75rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #64748b;
    font-weight: 700;
}

.dashboard-title {
    margin-top: 6px;
    font-size: clamp(1.7rem, 2vw, 2rem);
    font-weight: 800;
    color: #0f172a;
}

.dashboard-subtitle {
    margin-top: 6px;
    color: #64748b;
    font-size: 1.06rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1rem;
}

@media (min-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

.stat-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    border-left-width: 5px;
    min-height: 124px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.stat-indigo { border-left-color: #4f46e5; }
.stat-green { border-left-color: #16a34a; }
.stat-amber { border-left-color: #d97706; }
.stat-cyan { border-left-color: #0891b2; }

.stat-label {
    font-size: 0.82rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 700;
}

.stat-value {
    margin-top: 8px;
    font-size: 2rem;
    line-height: 1.1;
    font-weight: 800;
    color: #0f172a;
}

.panel {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    overflow: hidden;
}

.panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px 16px;
    border-bottom: 1px solid #e2e8f0;
}

.panel-head h3 {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
}

.panel-link {
    color: #ea580c;
    font-weight: 700;
    font-size: 0.9rem;
}

.panel--actions {
    padding: 0 0 22px;
}

.panel-head--no-border {
    border-bottom: 0;
    padding-bottom: 10px;
}

.actions-grid {
    padding: 0 22px;
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 12px;
}

@media (min-width: 640px) {
    .actions-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

.dashboard-table {
    min-width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.dashboard-table tbody tr {
    transition: background-color 0.15s ease;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 16px;
    border-radius: 12px;
    font-weight: 700;
    transition: all 0.2s ease;
}

.action-primary {
    background: #f97316;
    color: #ffffff;
}

.action-primary:hover {
    background: #ea580c;
}

.action-neutral {
    background: #e2e8f0;
    color: #1e293b;
}

.action-neutral:hover {
    background: #cbd5e1;
}
</style>


