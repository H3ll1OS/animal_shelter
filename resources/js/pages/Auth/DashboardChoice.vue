<template>
    <main class="dashboard-choice">
        <section class="choice-card" aria-label="Dashboard Selection">
            <p class="choice-kicker">Privileged Access</p>
            <h1 class="choice-title">Choose Dashboard</h1>
            <p class="choice-subtitle">
                You are logged in as a privileged user. Choose where to continue.
            </p>

            <div class="choice-actions" role="group" aria-label="Dashboard destination links">
                <Link v-if="canAccessAdmin" href="/admin" class="choice-btn choice-btn--primary">
                    Open Admin Dashboard
                </Link>
                <Link v-if="canAccessDeveloper" href="/developer/users" class="choice-btn choice-btn--dark">
                    Open Developer Dashboard
                </Link>
                <Link href="/" class="choice-btn choice-btn--light">
                    Go to Home
                </Link>
            </div>
        </section>
    </main>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

defineOptions({ layout: null });

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const canAccessAdmin = computed(() => ['admin', 'developer'].includes(props.user?.role));
const canAccessDeveloper = computed(() => props.user?.role === 'developer');
</script>

<style scoped>
.dashboard-choice {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    background:
        radial-gradient(780px 360px at 8% 12%, rgba(251, 146, 60, 0.18), transparent 70%),
        radial-gradient(760px 340px at 95% 88%, rgba(234, 88, 12, 0.14), transparent 70%),
        linear-gradient(180deg, #fffaf5 0%, #fff4e8 100%);
}

.choice-card {
    width: min(100%, 680px);
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 34px 30px;
    box-shadow:
        0 20px 40px rgba(15, 23, 42, 0.08),
        0 6px 16px rgba(15, 23, 42, 0.05);
}

.choice-kicker {
    margin: 0 0 6px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9a3412;
}

.choice-title {
    margin: 0;
    font-size: clamp(1.75rem, 2.5vw, 2.2rem);
    line-height: 1.1;
    color: #0f172a;
}

.choice-subtitle {
    margin: 12px 0 0;
    color: #7c2d12;
    font-size: 1.03rem;
    max-width: 56ch;
}

.choice-actions {
    margin-top: 26px;
    display: grid;
    gap: 10px;
}

.choice-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 13px 16px;
    border-radius: 12px;
    border: 1px solid transparent;
    font-weight: 700;
    text-decoration: none;
    transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.choice-btn--primary {
    background: #f97316;
    color: #ffffff;
}

.choice-btn--primary:hover {
    background: #ea580c;
}

.choice-btn--dark {
    background: #9a3412;
    color: #ffffff;
}

.choice-btn--dark:hover {
    background: #7c2d12;
}

.choice-btn--light {
    background: #fff7ed;
    color: #7c2d12;
    border-color: #fdba74;
}

.choice-btn--light:hover {
    background: #ffedd5;
}

@media (max-width: 640px) {
    .choice-card {
        padding: 26px 20px;
        border-radius: 16px;
    }

    .choice-btn {
        width: 100%;
    }
}
</style>
