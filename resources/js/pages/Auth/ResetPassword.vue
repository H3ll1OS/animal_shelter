<template>
    <div class="auth-page">
        <div class="auth-container">
            <h1>Reset Password</h1>
            <form @submit.prevent="submit">
                <input type="hidden" v-model="form.token">

                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" v-model="form.email" type="email" required autocomplete="username">
                    <div v-if="form.errors.email" class="error">{{ form.errors.email }}</div>
                </div>

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input id="password" v-model="form.password" type="password" required autocomplete="new-password">
                    <div v-if="form.errors.password" class="error">{{ form.errors.password }}</div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" v-model="form.password_confirmation" type="password" required autocomplete="new-password">
                </div>

                <button type="submit" :disabled="form.processing">Reset Password</button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    request: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    token: props.request?.token ?? '',
    email: props.request?.email ?? '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/password/reset');
}
</script>
