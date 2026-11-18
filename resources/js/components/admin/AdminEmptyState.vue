<template>
    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 text-center" :class="compact ? 'py-6' : 'py-10'">
        <span class="mx-auto mb-3 inline-flex h-9 w-9 items-center justify-center rounded-full bg-orange-50 text-orange-700" aria-hidden="true">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h8m-8 4h8m-8 4h5M7 3.75h7.5L19 8.25v11A1.75 1.75 0 0117.25 21h-10A1.75 1.75 0 015.5 19.25v-13A2.5 2.5 0 018 3.75zM14.5 4v4.5H19" />
            </svg>
        </span>
        <h3 class="text-sm font-semibold text-slate-800">{{ title }}</h3>
        <p v-if="description" class="mx-auto mt-1 max-w-lg text-sm leading-6 text-slate-600">{{ description }}</p>
        <div v-if="$slots.actions" class="mt-4 flex justify-center">
            <slot name="actions" />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    compact: { type: Boolean, default: false },
});
</script>
