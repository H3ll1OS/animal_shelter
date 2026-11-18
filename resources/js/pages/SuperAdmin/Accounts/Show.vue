<template>
    <SuperAdminLayout>
        <section class="sa-page">
            <header class="sa-page-heading">
                <div><p class="sa-eyebrow">Account details</p><h1>{{ user.name }}</h1><p>{{ user.email }}</p></div>
                <div class="sa-table-actions">
                    <Link v-if="!isSelf" :href="`/superadmin/accounts/${user.id}/edit`" class="sa-button sa-button--primary">Edit account</Link>
                    <Link href="/superadmin/accounts" class="sa-button">Back to list</Link>
                </div>
            </header>
            <section class="sa-card">
                <header class="sa-card-header"><div><h2>Account information</h2><p>Credentials and tokens are not displayed.</p></div></header>
                <div class="sa-card-body">
                    <dl class="sa-definition-list">
                        <dt>Name</dt><dd>{{ user.name }}</dd>
                        <dt>Email address</dt><dd>{{ user.email }}</dd>
                        <dt>Role</dt><dd><span class="sa-badge" :class="`sa-badge--${user.role}`">{{ roleLabel }}</span></dd>
                        <dt>Status</dt><dd><span class="sa-badge" :class="user.status === 'Active' ? 'sa-badge--active' : 'sa-badge--blocked'">{{ user.status }}</span></dd>
                        <dt>Registered</dt><dd>{{ formatDate(user.created_at) }}</dd>
                        <dt>Last login</dt><dd>{{ formatDate(user.last_login_at) }}</dd>
                    </dl>
                </div>
            </section>
        </section>
    </SuperAdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import SuperAdminLayout from '@/layouts/SuperAdminLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    currentUserId: { type: Number, required: true },
});
const roleLabel = computed(() => ({ superadmin: 'Super Admin', admin: 'Administrator', user: 'Registered User' }[props.user.role] || props.user.role));
const isSelf = computed(() => props.user.id === props.currentUserId);
function formatDate(value) {
    return value ? new Date(value).toLocaleString() : 'Never';
}
</script>
