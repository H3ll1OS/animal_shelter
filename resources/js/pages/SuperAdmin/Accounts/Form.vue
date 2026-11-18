<template>
    <SuperAdminLayout>
        <section class="sa-page">
            <header class="sa-page-heading">
                <div><p class="sa-eyebrow">Account administration</p><h1>{{ account ? 'Edit account' : 'Create account' }}</h1><p>Role and status changes are validated and audited.</p></div>
                <Link href="/superadmin/accounts" class="sa-button">Back to accounts</Link>
            </header>

            <form class="sa-card sa-card-body" @submit.prevent="submit">
                <div class="sa-form-grid">
                    <div class="sa-field">
                        <label for="account-name">Full name</label>
                        <input id="account-name" v-model="form.name" class="sa-control" autocomplete="name" required>
                        <span v-if="form.errors.name" class="sa-field-error">{{ form.errors.name }}</span>
                    </div>
                    <div class="sa-field">
                        <label for="account-email">Email address</label>
                        <input id="account-email" v-model="form.email" class="sa-control" type="email" autocomplete="email" required>
                        <span v-if="form.errors.email" class="sa-field-error">{{ form.errors.email }}</span>
                    </div>
                    <div class="sa-field">
                        <label for="account-role">Role</label>
                        <select id="account-role" v-model="form.role" class="sa-control" required>
                            <option v-for="role in roles" :key="role.key" :value="role.key">{{ role.label }}</option>
                        </select>
                        <span v-if="form.errors.role" class="sa-field-error">{{ form.errors.role }}</span>
                    </div>
                    <div class="sa-field">
                        <label for="account-status">Account status</label>
                        <select id="account-status" v-model="form.status" class="sa-control" required>
                            <option value="Active">Active</option><option value="Blocked">Blocked</option>
                        </select>
                        <span v-if="form.errors.status" class="sa-field-error">{{ form.errors.status }}</span>
                    </div>
                    <div class="sa-field">
                        <label for="account-password">{{ account ? 'New password (optional)' : 'Password' }}</label>
                        <input id="account-password" v-model="form.password" class="sa-control" type="password" :autocomplete="account ? 'new-password' : 'new-password'" :required="!account">
                        <span v-if="form.errors.password" class="sa-field-error">{{ form.errors.password }}</span>
                    </div>
                    <div class="sa-field">
                        <label for="account-password-confirm">Confirm password</label>
                        <input id="account-password-confirm" v-model="form.password_confirmation" class="sa-control" type="password" autocomplete="new-password" :required="!!form.password">
                    </div>
                </div>
                <p class="sa-form-help">Passwords are never shown or recorded in activity logs. The last active Super Admin cannot be demoted, blocked, or deleted.</p>
                <div class="sa-form-actions">
                    <button class="sa-button sa-button--primary" type="submit" :disabled="form.processing">{{ form.processing ? 'Saving…' : 'Save account' }}</button>
                    <Link href="/superadmin/accounts" class="sa-button">Cancel</Link>
                </div>
            </form>
        </section>
    </SuperAdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import SuperAdminLayout from '@/layouts/SuperAdminLayout.vue';

const props = defineProps({
    user: { type: Object, default: null },
    roles: { type: Array, default: () => [] },
});
const account = props.user;
const form = useForm({
    name: account?.name || '',
    email: account?.email || '',
    role: account?.role || 'user',
    status: account?.status || 'Active',
    password: '',
    password_confirmation: '',
});
function submit() {
    if (account) {
        form.put(`/superadmin/accounts/${account.id}`);
    } else {
        form.post('/superadmin/accounts');
    }
}
</script>
