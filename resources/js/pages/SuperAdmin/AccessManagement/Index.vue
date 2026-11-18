<template>
    <SuperAdminLayout>
        <section class="sa-page">
            <header class="sa-page-heading">
                <div>
                    <p class="sa-eyebrow">Administrative access</p>
                    <h1>Role and Access Management</h1>
                    <p>Review roles and control which administrative functions each role can access.</p>
                </div>
            </header>

            <section v-for="role in roles" :key="role.id" class="sa-card sa-access-card">
                <header class="sa-card-header">
                    <div>
                        <h2>{{ role.label }}</h2>
                        <p>{{ role.accounts_count }} {{ role.accounts_count === 1 ? 'account' : 'accounts' }} · {{ role.key === 'superadmin' ? 'Full system access is protected' : 'Configure this role’s access' }}</p>
                    </div>
                    <span v-if="role.key === 'superadmin'" class="sa-badge sa-badge--superadmin">Protected role</span>
                </header>

                <form class="sa-card-body sa-access-form" @submit.prevent="save(role)">
                    <div class="sa-field">
                        <label :for="`role-description-${role.id}`">Role description</label>
                        <textarea :id="`role-description-${role.id}`" v-model="forms[role.id].description" class="sa-control sa-textarea" rows="2" maxlength="1000" :disabled="role.key === 'superadmin'"></textarea>
                        <span v-if="forms[role.id].errors.description" class="sa-field-error">{{ forms[role.id].errors.description }}</span>
                    </div>

                    <div class="sa-permission-groups">
                        <section v-for="[module, modulePermissions] in permissionGroups" :key="module" class="sa-permission-group">
                            <h3>{{ title(module) }}</h3>
                            <label v-for="permission in modulePermissions" :key="permission.id" class="sa-permission-option">
                                <input v-model="forms[role.id].permission_ids" type="checkbox" :value="permission.id" :disabled="role.key === 'superadmin'">
                                <span>
                                    {{ permission.label }}
                                    <small v-if="permission.description">{{ permission.description }}</small>
                                </span>
                            </label>
                        </section>
                    </div>
                    <p v-if="role.key === 'superadmin'" class="sa-form-help">Super Admin permissions are locked to preserve system administration access.</p>
                    <div v-else class="sa-form-actions">
                        <button class="sa-button sa-button--primary" type="submit" :disabled="forms[role.id].processing">
                            {{ forms[role.id].processing ? 'Saving…' : 'Save role access' }}
                        </button>
                    </div>
                </form>
            </section>
        </section>
    </SuperAdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/layouts/SuperAdminLayout.vue';

const props = defineProps({
    roles: { type: Array, required: true },
    permissions: { type: Object, required: true },
});
const permissionGroups = computed(() => Object.entries(props.permissions));
const forms = {};

props.roles.forEach((role) => {
    forms[role.id] = useForm({
        description: role.description || '',
        permission_ids: role.permissions.map((permission) => permission.id),
    });
});

function save(role) {
    forms[role.id].put(`/superadmin/roles/${role.id}/access`, { preserveScroll: true });
}

function title(value) {
    return String(value || '').replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}
</script>

<style scoped>
.sa-access-card{overflow:visible}
.sa-access-form{display:grid;gap:16px}
.sa-textarea{min-height:64px;resize:vertical}
</style>
