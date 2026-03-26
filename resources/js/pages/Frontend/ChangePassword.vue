<template>
    <AppLayout>
        <section class="about-content">
            <div class="about-section">
                <h2>Change Password</h2>
                <form @submit.prevent="submit" class="mt-3">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input id="current_password" v-model="form.current_password" type="password" class="form-control" required>
                        <div v-if="form.errors.current_password" class="text-danger mt-1">{{ form.errors.current_password }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input id="password" v-model="form.password" type="password" class="form-control" required>
                        <div v-if="form.errors.password" class="text-danger mt-1">{{ form.errors.password }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input id="password_confirmation" v-model="form.password_confirmation" type="password" class="form-control" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">Update Password</button>
                        <Link href="/account/settings" class="btn btn-outline-secondary">Back</Link>
                    </div>
                </form>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.patch('/account/password');
}
</script>
