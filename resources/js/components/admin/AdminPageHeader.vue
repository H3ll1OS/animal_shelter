<template>
    <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <p v-if="eyebrow" class="mb-1 text-xs font-bold uppercase tracking-[0.12em] text-orange-700">{{ eyebrow }}</p>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ title }}</h2>
            <p v-if="description" class="mt-1.5 max-w-2xl text-sm leading-6 text-slate-600">{{ description }}</p>
        </div>
        <div v-if="$slots.actions" class="flex shrink-0 flex-wrap items-center gap-2">
            <slot name="actions" />
        </div>
    </header>
</template>

<script setup>
defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>
