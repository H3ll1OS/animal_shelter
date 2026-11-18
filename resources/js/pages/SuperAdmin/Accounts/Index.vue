<template>
    <SuperAdminLayout>
        <section class="sa-page">
            <header class="sa-page-heading">
                <div><p class="sa-eyebrow">System administration</p><h1>Account Management</h1><p>Search and manage application access without changing account history.</p></div>
                <Link href="/superadmin/accounts/create" class="sa-button sa-button--primary">Create account</Link>
            </header>

            <section class="sa-card sa-account-card">
                <form class="sa-toolbar sa-account-toolbar" @submit.prevent="applyFilters">
                    <div class="sa-field sa-field--grow">
                        <label for="account-search">Search accounts</label>
                        <input id="account-search" v-model="form.search" class="sa-control" type="search" placeholder="Name or email">
                    </div>
                    <div class="sa-field">
                        <label for="account-role">Role</label>
                        <select id="account-role" v-model="form.role" class="sa-control">
                            <option value="">All roles</option>
                            <option v-for="role in roles" :key="role.key" :value="role.key">{{ role.label }}</option>
                        </select>
                    </div>
                    <div class="sa-field">
                        <label for="account-status">Status</label>
                        <select id="account-status" v-model="form.status" class="sa-control">
                            <option value="">All statuses</option><option value="Active">Active</option><option value="Blocked">Blocked</option>
                        </select>
                    </div>
                    <button class="sa-button sa-button--primary" type="submit">Apply filters</button>
                    <Link href="/superadmin/accounts" class="sa-button">Clear</Link>
                </form>

                <div class="sa-table-summary">
                    <span>Accounts</span>
                    <span>Showing {{ users.from || 0 }}–{{ users.to || 0 }} of {{ users.total }}</span>
                </div>
                <div class="sa-table-wrap">
                    <table class="sa-table sa-account-table">
                        <thead><tr><th>Account</th><th>Role</th><th>Status</th><th>Registered</th><th>Last login</th><th>Actions</th></tr></thead>
                        <tbody>
                            <tr v-for="account in users.data" :key="account.id">
                                <td>
                                    <div class="sa-account-cell">
                                        <span class="sa-account-avatar" aria-hidden="true">{{ initials(account.name) }}</span>
                                        <span class="sa-account-details"><strong>{{ account.name }}</strong><small>{{ account.email }}</small></span>
                                    </div>
                                </td>
                                <td><span class="sa-badge" :class="`sa-badge--${account.role}`">{{ roleLabel(account.role) }}</span></td>
                                <td><span class="sa-badge" :class="account.status === 'Active' ? 'sa-badge--active' : 'sa-badge--blocked'">{{ account.status }}</span></td>
                                <td>{{ formatDate(account.created_at) }}</td>
                                <td>{{ formatDate(account.last_login_at) }}</td>
                                <td>
                                    <div class="sa-table-actions sa-account-actions">
                                        <Link :href="`/superadmin/accounts/${account.id}`" class="sa-inline-link">View</Link>
                                        <template v-if="account.id !== currentUserId">
                                            <Link :href="`/superadmin/accounts/${account.id}/edit`" class="sa-inline-link">Edit</Link>
                                            <Link :href="`/superadmin/accounts/${account.id}`" method="delete" as="button" class="sa-button sa-button--danger" @before="confirmDelete">Delete</Link>
                                        </template>
                                        <span v-else class="sa-badge">Current account</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0"><td colspan="6" class="sa-empty">No accounts match these filters.</td></tr>
                        </tbody>
                    </table>
                </div>
                <nav v-if="users.links.length > 3" class="sa-pagination" aria-label="Account pages">
                    <template v-for="(link, index) in users.links" :key="index">
                        <span v-if="!link.url" class="disabled" v-html="link.label"></span>
                        <Link v-else :href="link.url" :class="{ active: link.active }" v-html="link.label" preserve-scroll />
                    </template>
                </nav>
            </section>
        </section>
    </SuperAdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/layouts/SuperAdminLayout.vue';

const props = defineProps({
    users: { type: Object, required: true },
    roles: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    currentUserId: { type: Number, required: true },
});
const form = reactive({ search: props.filters.search || '', role: props.filters.role || '', status: props.filters.status || '' });
function applyFilters() {
    router.get('/superadmin/accounts', form, { preserveState: true, replace: true });
}
function roleLabel(key) {
    return props.roles.find((role) => role.key === key)?.label || key;
}
function formatDate(value) {
    return value ? new Date(value).toLocaleDateString() : 'Never';
}
function initials(name) {
    return String(name || '')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('') || '?';
}
function confirmDelete(visit) {
    if (!window.confirm('Delete this account? This cannot be undone.')) visit.cancel();
}
</script>
