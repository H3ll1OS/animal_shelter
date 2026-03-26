<template>
    <main class="auth-page">
        <section class="auth-shell" aria-label="Register">
            <div class="auth-intro">
                <p class="auth-kicker">Animal Shelter</p>
                <h1>Create Account</h1>
                <p>Join the shelter community and manage adoptions, donations, and your account details.</p>
            </div>

            <form class="auth-form" @submit.prevent="submit">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input id="name" v-model="form.name" type="text" required autofocus autocomplete="name">
                    <p v-if="errors.name" class="error">{{ errors.name }}</p>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" v-model="form.email" type="email" required autocomplete="email">
                    <p v-if="errors.email" class="error">{{ errors.email }}</p>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" v-model="form.password" type="password" required autocomplete="new-password">
                    <p v-if="errors.password" class="error">{{ errors.password }}</p>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" v-model="form.password_confirmation" type="password" required autocomplete="new-password">
                </div>

                <button type="submit" class="submit-btn" :disabled="form.processing">
                    {{ form.processing ? 'Creating account...' : 'Register' }}
                </button>
            </form>

            <div class="auth-links">
                <p>Already have an account? <a :href="loginUrl">Login</a></p>
            </div>
        </section>
    </main>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const errors = ref({});
const loginUrl = '/login';

function submit() {
    errors.value = {};
    form.post('/register', {
        onError: (err) => {
            errors.value = err;
        }
    });
}
</script>

<style scoped>
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    background:
        radial-gradient(780px 340px at 10% 10%, rgba(251, 146, 60, 0.2), transparent 72%),
        radial-gradient(760px 320px at 90% 90%, rgba(234, 88, 12, 0.16), transparent 72%),
        linear-gradient(180deg, #fffaf5 0%, #ffedd5 100%);
}

.auth-shell {
    width: min(100%, 500px);
    background: #ffffff;
    border: 1px solid #fed7aa;
    border-radius: 20px;
    box-shadow:
        0 20px 44px rgba(124, 45, 18, 0.14),
        0 6px 16px rgba(124, 45, 18, 0.08);
    padding: 34px 30px 28px;
}

.auth-intro {
    margin-bottom: 20px;
}

.auth-kicker {
    margin: 0 0 8px;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #c2410c;
}

h1 {
    margin: 0 0 8px;
    font-size: clamp(1.7rem, 2.4vw, 2rem);
    line-height: 1.15;
    color: #7c2d12;
}

.auth-intro p {
    margin: 0;
    color: #9a3412;
    line-height: 1.5;
}

.auth-form {
    width: 100%;
}

.form-group {
    margin-bottom: 14px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 0.92rem;
    font-weight: 600;
    color: #7c2d12;
}

.form-group input {
    width: 100%;
    padding: 12px 13px;
    border: 1px solid #fdba74;
    border-radius: 10px;
    background: #fff7ed;
    color: #7c2d12;
    font-size: 0.98rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #f97316;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.2);
}

.submit-btn {
    width: 100%;
    padding: 12px 14px;
    background: linear-gradient(135deg, #fb923c 0%, #f97316 60%, #ea580c 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 700;
    transition: transform 0.14s ease, box-shadow 0.18s ease, filter 0.18s ease;
    box-shadow: 0 10px 20px rgba(234, 88, 12, 0.25);
}

.submit-btn:hover {
    filter: brightness(0.98);
    transform: translateY(-1px);
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.auth-links {
    margin-top: 16px;
}

.auth-links p {
    margin: 0;
    color: #9a3412;
}

.auth-links a {
    color: #c2410c;
    font-weight: 600;
    text-decoration: none;
}

.auth-links a:hover {
    color: #9a3412;
    text-decoration: underline;
}

.error {
    color: #b91c1c;
    font-size: 0.86rem;
    margin-top: 4px;
}

@media (max-width: 560px) {
    .auth-shell {
        padding: 26px 18px 20px;
        border-radius: 14px;
    }
}
</style>
