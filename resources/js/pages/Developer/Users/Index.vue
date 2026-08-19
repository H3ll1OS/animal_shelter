<template>
    <AdminLayout>
        <section class="admin-page-container users-page">
            <header class="users-header">
                <div>
                    <p class="users-kicker">Developer Console</p>
                    <h1>System Users</h1>
                    <p class="users-subtitle">Manage access levels and account availability across the shelter system.</p>
                </div>

                <div class="users-metrics" aria-label="User summary">
                    <div class="metric">
                        <span class="metric-value">{{ totalUsers }}</span>
                        <span class="metric-label">Total</span>
                    </div>
                    <div class="metric">
                        <span class="metric-value">{{ activeUsers }}</span>
                        <span class="metric-label">Active</span>
                    </div>
                    <div class="metric">
                        <span class="metric-value">{{ adminUsers }}</span>
                        <span class="metric-label">Admins</span>
                    </div>
                </div>
            </header>

            <div class="users-card">
                <div class="table-toolbar">
                    <div>
                        <h2>User Directory</h2>
                        <p>{{ showingLabel }}</p>
                    </div>
                </div>

                <div class="table-scroll">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Current Role</th>
                                <th>Status</th>
                                <th>Update Access</th>
                                <th class="text-right">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="u in users.data" :key="u.id">
                                <td>
                                    <div class="user-cell">
                                        <span class="avatar">{{ initials(u.name) }}</span>
                                        <span class="user-copy">
                                            <span class="user-name">{{ u.name }}</span>
                                            <span class="user-email">{{ u.email }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-pill" :class="roleClass(u.role)">{{ formatRole(u.role) }}</span>
                                </td>
                                <td>
                                    <span class="status-pill" :class="statusClass(u.status)">{{ u.status }}</span>
                                </td>
                                <td>
                                    <form class="inline-form" @submit.prevent="updateRole(u)">
                                        <select v-model="roleState[u.id].role" aria-label="Role">
                                            <option value="user">User</option>
                                            <option value="admin">Admin</option>
                                            <option value="developer">Developer</option>
                                        </select>
                                        <select v-model="roleState[u.id].status" aria-label="Status">
                                            <option value="Active">Active</option>
                                            <option value="Blocked">Blocked</option>
                                        </select>
                                        <button type="submit" class="save-btn">Save</button>
                                    </form>
                                </td>
                                <td class="text-right">
                                    <Link
                                        :href="`/developer/users/${u.id}`"
                                        method="delete"
                                        as="button"
                                        class="delete-btn"
                                        @before="(visit) => confirmDelete(visit, u.name)"
                                    >
                                        Delete
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="empty-cell">No users found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <PaginationLinks :links="users.links" />
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { computed, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PaginationLinks from '@/components/common/PaginationLinks.vue';

const props = defineProps({ users: { type: Object, required: true } });

const roleState = reactive({});
props.users.data.forEach((u) => {
    roleState[u.id] = { role: u.role, status: u.status };
});

const totalUsers = computed(() => props.users.total ?? props.users.data.length);
const activeUsers = computed(() => props.users.data.filter((u) => u.status === 'Active').length);
const adminUsers = computed(() => props.users.data.filter((u) => ['admin', 'developer'].includes(u.role)).length);
const showingLabel = computed(() => {
    const from = props.users.from ?? (props.users.data.length ? 1 : 0);
    const to = props.users.to ?? props.users.data.length;
    const total = props.users.total ?? props.users.data.length;
    return `Showing ${from}-${to} of ${total} users`;
});

function updateRole(user) {
    router.put(`/developer/users/${user.id}/update-role`, roleState[user.id], { preserveScroll: true });
}

function initials(name) {
    return String(name || 'U')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
}

function formatRole(role) {
    if (!role) return 'User';
    return role.charAt(0).toUpperCase() + role.slice(1);
}

function roleClass(role) {
    if (role === 'developer') return 'role-pill--developer';
    if (role === 'admin') return 'role-pill--admin';
    return 'role-pill--user';
}

function statusClass(status) {
    return status === 'Active' ? 'status-pill--active' : 'status-pill--blocked';
}

function confirmDelete(visit, name) {
    if (!window.confirm(`Delete ${name}? This action cannot be undone.`)) {
        visit.cancel();
    }
}
</script>

<style scoped>
.users-page {
    display: grid;
    gap: 18px;
}

.users-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
}

.users-kicker {
    margin: 0 0 6px;
    font-size: 0.76rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #64748b;
}

.users-header h1 {
    margin: 0;
    font-size: 2rem;
    line-height: 1.12;
    font-weight: 800;
    color: #0f172a;
}

.users-subtitle {
    margin: 8px 0 0;
    max-width: 620px;
    color: #64748b;
    font-size: 1rem;
}

.users-metrics {
    display: grid;
    grid-template-columns: repeat(3, minmax(92px, 1fr));
    gap: 10px;
    min-width: 320px;
}

.metric {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #ffffff;
    padding: 12px 14px;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
}

.metric-value {
    display: block;
    color: #0f172a;
    font-size: 1.35rem;
    line-height: 1;
    font-weight: 800;
}

.metric-label {
    display: block;
    margin-top: 5px;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.users-card {
    overflow: hidden;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
}

.table-toolbar {
    min-height: 70px;
    padding: 16px 18px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
}

.table-toolbar h2 {
    margin: 0;
    color: #0f172a;
    font-size: 1.05rem;
    line-height: 1.2;
    font-weight: 800;
}

.table-toolbar p {
    margin: 5px 0 0;
    color: #64748b;
    font-size: 0.9rem;
}

.table-scroll {
    overflow-x: auto;
}

.users-table {
    width: 100%;
    min-width: 960px;
    border-collapse: separate;
    border-spacing: 0;
}

.users-table thead {
    background: #f8fafc;
}

.users-table th,
.users-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
    vertical-align: middle;
}

.users-table th {
    color: #64748b;
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0.07em;
    text-transform: uppercase;
}

.users-table td {
    color: #334155;
    font-size: 0.95rem;
}

.users-table tbody tr {
    transition: background-color 0.18s ease;
}

.users-table tbody tr:hover {
    background: #f8fafc;
}

.users-table tbody tr:last-child td {
    border-bottom: 0;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 250px;
}

.avatar {
    width: 38px;
    height: 38px;
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: #fff7ed;
    color: #c2410c;
    font-size: 0.84rem;
    font-weight: 800;
    border: 1px solid #fed7aa;
}

.user-copy {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}

.user-name {
    color: #0f172a;
    font-weight: 750;
    line-height: 1.2;
}

.user-email {
    color: #64748b;
    font-size: 0.84rem;
    line-height: 1.2;
}

.role-pill,
.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 28px;
    border-radius: 999px;
    padding: 0 10px;
    font-size: 0.78rem;
    font-weight: 800;
}

.role-pill--developer {
    background: #eef2ff;
    color: #3730a3;
}

.role-pill--admin {
    background: #fff7ed;
    color: #9a3412;
}

.role-pill--user {
    background: #f1f5f9;
    color: #475569;
}

.status-pill--active {
    background: #dcfce7;
    color: #166534;
}

.status-pill--blocked {
    background: #fee2e2;
    color: #991b1b;
}

.inline-form {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.inline-form select {
    height: 38px;
    min-width: 116px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #ffffff;
    color: #0f172a;
    padding: 0 34px 0 10px;
    font-size: 0.92rem;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.inline-form select:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.16);
}

.save-btn {
    height: 38px;
    border: 0;
    border-radius: 10px;
    background: #f97316;
    color: #ffffff;
    padding: 0 14px;
    font-weight: 800;
    cursor: pointer;
    transition: background-color 0.18s ease, transform 0.18s ease;
}

.save-btn:hover {
    background: #ea580c;
}

.save-btn:active {
    transform: translateY(1px);
}

.delete-btn {
    border: 1px solid #fecaca;
    border-radius: 10px;
    background: #ffffff;
    color: #dc2626;
    min-height: 36px;
    padding: 0 12px;
    font-weight: 800;
    cursor: pointer;
    transition: background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
}

.delete-btn:hover {
    background: #fef2f2;
    border-color: #fca5a5;
    color: #b91c1c;
}

.text-right {
    text-align: right !important;
}

.empty-cell {
    padding: 28px !important;
    text-align: center !important;
    color: #64748b;
}

.table-footer {
    padding: 12px 16px;
    border-top: 1px solid #e2e8f0;
    background: #ffffff;
}

@media (max-width: 900px) {
    .users-header {
        align-items: stretch;
        flex-direction: column;
    }

    .users-metrics {
        min-width: 0;
    }
}

@media (max-width: 640px) {
    .users-header h1 {
        font-size: 1.65rem;
    }

    .users-metrics {
        grid-template-columns: 1fr;
    }
}
</style>
