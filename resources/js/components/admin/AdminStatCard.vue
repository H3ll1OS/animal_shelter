<template>
    <article class="monitoring-stat-card">
        <p class="monitoring-stat-label">{{ label }}</p>
        <p class="monitoring-stat-value">{{ value ?? 0 }}</p>
    </article>
</template>

<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [Number, String], default: 0 },
});
</script>

<style scoped>
.monitoring-stat-card {
    position: relative;
    display: flex;
    min-height: 7rem;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    padding: 1rem 1.125rem;
    box-shadow: 0 1px 2px rgb(15 23 42 / 4%);
    transition: border-color 150ms ease, box-shadow 150ms ease, transform 150ms ease;
}

.monitoring-stat-card::before {
    position: absolute;
    inset: 0 auto 0 0;
    width: 3px;
    background: #ea580c;
    content: "";
}

.monitoring-stat-card:hover {
    transform: translateY(-1px);
    border-color: #cbd5e1;
    box-shadow: 0 5px 14px rgb(15 23 42 / 6%);
}

.monitoring-stat-label {
    color: #64748b;
    font-size: 0.8125rem;
    font-weight: 600;
    line-height: 1.25rem;
}

.monitoring-stat-value {
    margin-top: 0.65rem;
    color: #0f172a;
    font-size: 1.875rem;
    font-weight: 750;
    line-height: 1;
    letter-spacing: -0.035em;
    font-variant-numeric: tabular-nums;
}

@media (min-width: 640px) {
    .monitoring-stat-card {
        min-height: 7.25rem;
        padding: 1.125rem 1.25rem;
    }

    .monitoring-stat-value {
        font-size: 2rem;
    }
}
</style>
