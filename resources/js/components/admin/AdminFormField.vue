<template>
    <label class="block min-w-0">
        <span class="mb-1.5 flex items-center gap-1 text-sm font-semibold text-slate-700">
            {{ label }}<span v-if="required" class="text-rose-600" aria-hidden="true">*</span>
            <span v-if="required" class="sr-only">required</span>
        </span>
        <slot />
        <span v-if="hint && !error" class="mt-1.5 block text-xs leading-5 text-slate-500">{{ hint }}</span>
        <span v-if="error" class="mt-1.5 block text-xs font-medium leading-5 text-rose-700" role="alert">{{ error }}</span>
    </label>
</template>

<script setup>
defineProps({
    label: { type: String, required: true },
    required: { type: Boolean, default: false },
    hint: { type: String, default: '' },
    error: { type: String, default: '' },
});
</script>
