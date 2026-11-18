<template>
    <SuperAdminLayout>
        <section class="sa-page">
            <header class="sa-page-heading">
                <div>
                    <p class="sa-eyebrow">Accountability</p>
                    <h1>Activity Logs</h1>
                    <p>Review important account and access changes recorded by the system.</p>
                </div>
            </header>

            <section class="sa-card">
                <form class="sa-toolbar" @submit.prevent="applyFilters">
                    <div class="sa-field sa-field--grow">
                        <label for="log-search">Search accounts</label>
                        <input id="log-search" v-model="form.search" class="sa-control" type="search" placeholder="Administrator or affected account">
                    </div>
                    <div class="sa-field">
                        <label for="log-action">Action</label>
                        <select id="log-action" v-model="form.action" class="sa-control">
                            <option value="">All actions</option>
                            <option v-for="action in actions" :key="action" :value="action">{{ title(action) }}</option>
                        </select>
                    </div>
                    <div class="sa-field">
                        <label for="log-actor">Administrator</label>
                        <select id="log-actor" v-model="form.actor" class="sa-control">
                            <option value="">All administrators</option>
                            <option v-for="administrator in administrators" :key="administrator.id" :value="administrator.id">{{ administrator.name }}</option>
                        </select>
                    </div>
                    <div class="sa-field">
                        <label for="log-from">From</label>
                        <input id="log-from" v-model="form.from" class="sa-control" type="date">
                    </div>
                    <div class="sa-field">
                        <label for="log-to">To</label>
                        <input id="log-to" v-model="form.to" class="sa-control" type="date">
                    </div>
                    <button class="sa-button sa-button--primary" type="submit">Apply filters</button>
                    <Link href="/superadmin/activity-logs" class="sa-button">Clear</Link>
                </form>

                <div class="sa-table-wrap">
                    <table class="sa-table">
                        <thead><tr><th>Action</th><th>Administrator</th><th>Affected account</th><th>Details</th><th>Date and time</th></tr></thead>
                        <tbody>
                            <tr v-for="log in logs.data" :key="log.id">
                                <td><span class="sa-badge">{{ title(log.action) }}</span><small class="sa-log-module">{{ title(log.module) }}</small></td>
                                <td>{{ log.actor_name }}<small v-if="log.actor_email"><br>{{ log.actor_email }}</small></td>
                                <td>{{ log.subject_name || '—' }}<small v-if="log.subject_email"><br>{{ log.subject_email }}</small></td>
                                <td class="sa-log-details">{{ detailSummary(log.details) }}</td>
                                <td>{{ formatDate(log.created_at) }}</td>
                            </tr>
                            <tr v-if="logs.data.length === 0"><td colspan="5" class="sa-empty">No activity matches the selected filters.</td></tr>
                        </tbody>
                    </table>
                </div>
                <nav v-if="logs.links.length > 3" class="sa-pagination" aria-label="Activity log pages">
                    <template v-for="(link, index) in logs.links" :key="index">
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
    logs: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    actions: { type: Array, default: () => [] },
    administrators: { type: Array, default: () => [] },
});
const form = reactive({
    search: props.filters.search || '',
    action: props.filters.action || '',
    actor: props.filters.actor || '',
    from: props.filters.from || '',
    to: props.filters.to || '',
});

function applyFilters() {
    router.get('/superadmin/activity-logs', form, { preserveState: true, replace: true });
}

function title(value) {
    return String(value || '').replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function detailSummary(details) {
    if (!details || typeof details !== 'object') return '—';
    return Object.entries(details)
        .map(([key, value]) => `${title(key)}: ${Array.isArray(value) ? value.join(', ') : value}`)
        .join(' · ');
}

function formatDate(value) {
    return value ? new Date(value).toLocaleString() : '—';
}
</script>

<style scoped>
.sa-log-module{display:block;margin-top:5px;color:#7a8798;font-size:10px}
</style>
