<template>
    <AdminLayout>
        <section class="admin-page-container space-y-5">
            <header class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-orange-700">Account access</p>
                    <h1 class="mt-1 text-2xl font-extrabold text-slate-900">User Management</h1>
                    <p class="mt-1 text-sm text-slate-600">View registered user accounts and control account availability.</p>
                </div>
                <p class="text-sm text-slate-500">{{ users.total }} registered users</p>
            </header>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <form class="flex flex-wrap items-end gap-3 border-b border-slate-200 bg-slate-50 p-4" @submit.prevent="applyFilters">
                    <label class="min-w-[220px] flex-1 text-xs font-semibold text-slate-600">
                        Search name or email
                        <input v-model="form.search" type="search" class="mt-1 block h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100" placeholder="Search registered users">
                    </label>
                    <label class="min-w-[150px] text-xs font-semibold text-slate-600">
                        Status
                        <select v-model="form.status" class="mt-1 block h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 focus:border-orange-500 focus:outline-none">
                            <option value="">All statuses</option><option value="Active">Active</option><option value="Blocked">Blocked</option>
                        </select>
                    </label>
                    <button class="admin-btn admin-btn--primary" type="submit">Filter</button>
                    <Link href="/admin/users" class="admin-btn admin-btn--secondary">Clear</Link>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-[760px] w-full border-collapse text-left">
                        <thead class="bg-slate-100 text-[11px] uppercase tracking-wider text-slate-600">
                            <tr><th class="px-4 py-3">User</th><th class="px-4 py-3">Registered</th><th class="px-4 py-3">Last login</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Account access</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm font-semibold text-slate-800">{{ user.name }}<small class="block font-normal text-slate-500">{{ user.email }}</small></td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ formatDate(user.created_at) }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ formatDate(user.last_login_at) }}</td>
                                <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-bold" :class="user.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">{{ user.status }}</span></td>
                                <td class="px-4 py-3">
                                    <button type="button" class="admin-btn admin-btn--secondary admin-btn--small" @click="toggleStatus(user)">
                                        {{ user.status === 'Active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0"><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">No registered users match these filters.</td></tr>
                        </tbody>
                    </table>
                </div>
                <nav v-if="users.links.length > 3" class="flex flex-wrap gap-2 p-4" aria-label="Registered user pages">
                    <template v-for="(link, index) in users.links" :key="index">
                        <span v-if="!link.url" class="rounded border border-slate-200 px-3 py-1.5 text-sm text-slate-400" v-html="link.label"></span>
                        <Link v-else :href="link.url" class="rounded border px-3 py-1.5 text-sm" :class="link.active ? 'border-orange-500 bg-orange-500 text-white' : 'border-slate-200 bg-white text-slate-700'" v-html="link.label" preserve-scroll />
                    </template>
                </nav>
            </section>
        </section>
    </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    users: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});
const form = reactive({ search: props.filters.search || '', status: props.filters.status || '' });
function applyFilters() {
    router.get('/admin/users', form, { preserveState: true, replace: true });
}
function toggleStatus(user) {
    const status = user.status === 'Active' ? 'Blocked' : 'Active';
    const verb = status === 'Active' ? 'activate' : 'deactivate';
    if (window.confirm(`Are you sure you want to ${verb} ${user.name}'s account?`)) {
        router.patch(`/admin/users/${user.id}/status`, { status }, { preserveScroll: true });
    }
}
function formatDate(value) {
    return value ? new Date(value).toLocaleDateString() : 'Never';
}
</script>
