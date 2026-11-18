<template>
    <SuperAdminLayout>
        <section class="sa-page">
            <header class="sa-page-heading">
                <div>
                    <p class="sa-eyebrow">System administration</p>
                    <h1>Overview</h1>
                    <p>A clear snapshot of system accounts, role distribution, and account status.</p>
                </div>
                <Link href="/superadmin/accounts" class="sa-button sa-button--primary">Manage accounts</Link>
            </header>

            <div class="sa-stats">
                <article v-for="stat in statCards" :key="stat.key" class="sa-stat">
                    <div class="sa-stat-label">{{ stat.label }}</div>
                    <div class="sa-stat-value">{{ stats[stat.key] }}</div>
                    <div class="sa-stat-note">{{ stat.note }}</div>
                </article>
            </div>

            <div class="sa-overview-grid">
                <section class="sa-card">
                    <header class="sa-card-header">
                        <div><h2>Account distribution</h2><p>Accounts by assigned system role</p></div>
                    </header>
                    <div v-if="roleCounts.length" class="sa-card-body sa-distribution">
                        <div v-for="role in roleCounts" :key="role.key" class="sa-distribution-row">
                            <span class="sa-distribution-label">{{ role.label }}</span>
                            <span class="sa-progress"><span :style="{ width: `${percentage(role.accounts_count)}%` }"></span></span>
                            <span class="sa-distribution-count">{{ role.accounts_count }}</span>
                        </div>
                    </div>
                    <div v-else class="sa-empty">No role data is available.</div>
                </section>

                <section class="sa-card">
                    <header class="sa-card-header"><div><h2>Account status</h2><p>Active and blocked accounts</p></div></header>
                    <div class="sa-card-body sa-status-summary">
                        <div class="sa-status-tile"><strong>{{ stats.active }}</strong><span>Active accounts</span></div>
                        <div class="sa-status-tile"><strong>{{ stats.inactive }}</strong><span>Inactive accounts</span></div>
                    </div>
                </section>
            </div>

        </section>
    </SuperAdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import SuperAdminLayout from '@/layouts/SuperAdminLayout.vue';

const props = defineProps({
    stats: { type: Object, required: true },
    roleCounts: { type: Array, default: () => [] },
});
const statCards = [
    { key: 'accounts', label: 'Total accounts', note: 'All registered accounts' },
    { key: 'administrators', label: 'Administrators', note: 'Operational access' },
    { key: 'registered_users', label: 'Registered users', note: 'Public user accounts' },
    { key: 'active', label: 'Active accounts', note: 'Currently enabled' },
    { key: 'inactive', label: 'Inactive accounts', note: 'Blocked or disabled' },
];
function percentage(value) {
    return props.stats.accounts ? Math.min(100, (Number(value) / props.stats.accounts) * 100) : 0;
}
</script>
