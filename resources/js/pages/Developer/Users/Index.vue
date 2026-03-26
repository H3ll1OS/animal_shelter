<template>
    <AdminLayout>
        <section class="account-settings">
            <div class="container">
                <h1 class="admin-title">System User Management</h1>
                <div class="table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Update</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="u in users.data" :key="u.id">
                                <td>
                                    <div>{{ u.name }}</div>
                                    <small>{{ u.email }}</small>
                                </td>
                                <td>{{ u.role }}</td>
                                <td>{{ u.status }}</td>
                                <td>
                                    <form class="inline-form" @submit.prevent="updateRole(u)">
                                        <select v-model="roleState[u.id].role">
                                            <option value="user">User</option>
                                            <option value="admin">Admin</option>
                                            <option value="developer">Developer</option>
                                        </select>
                                        <select v-model="roleState[u.id].status">
                                            <option value="Active">Active</option>
                                            <option value="Blocked">Blocked</option>
                                        </select>
                                        <button type="submit" class="mini-btn">Save</button>
                                    </form>
                                </td>
                                <td>
                                    <Link :href="`/developer/users/${u.id}`" method="delete" as="button" class="danger-btn">Delete</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <PaginationLinks :links="users.links" />
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PaginationLinks from '@/components/common/PaginationLinks.vue';

const props = defineProps({ users: { type: Object, required: true } });

const roleState = reactive({});
props.users.data.forEach((u) => {
    roleState[u.id] = { role: u.role, status: u.status };
});

function updateRole(user) {
    router.put(`/developer/users/${user.id}/update-role`, roleState[user.id], { preserveScroll: true });
}
</script>

<style scoped>
.admin-title{margin:0 0 14px;font-size:1.8rem;}
.table-wrap{overflow:auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:14px;}
.admin-table{width:100%;border-collapse:collapse;}
.admin-table th,.admin-table td{padding:10px 12px;border-bottom:1px solid #f0f2f4;text-align:left;vertical-align:top;}
.inline-form{display:flex;gap:8px;align-items:center;}
.inline-form select{border:1px solid #d1d5db;border-radius:8px;padding:6px 8px;}
.mini-btn{border:none;background:#ff6b35;color:#fff;padding:6px 10px;border-radius:8px;cursor:pointer;}
.danger-btn{border:none;background:none;color:#dc2626;font-weight:600;cursor:pointer;}
</style>

