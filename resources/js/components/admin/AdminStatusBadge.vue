<template>
    <span class="inline-flex max-w-full items-center rounded-full border px-2.5 py-1 text-xs font-semibold leading-4" :class="toneClass">
        {{ label || status || 'Unknown' }}
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, default: '' },
    label: { type: String, default: '' },
});

const toneClass = computed(() => {
    const value = (props.status || props.label).toLowerCase();
    if (['completed', 'approved', 'alive', 'healthy', 'good', 'with original adopter', 'available'].includes(value)) return 'border-emerald-200 bg-emerald-50 text-emerald-800';
    if (['overdue', 'deceased', 'critical condition', 'poor', 'needs attention', 'needs medical attention', 'returned to shelter'].includes(value)) return 'border-rose-200 bg-rose-50 text-rose-800';
    if (['cancelled', 'rejected'].includes(value)) return 'border-slate-300 bg-slate-100 text-slate-700';
    if (['upcoming', 'pending', 'missing', 'acceptable', 'rehomed / transferred'].includes(value)) return 'border-amber-200 bg-amber-50 text-amber-800';
    return 'border-slate-200 bg-slate-50 text-slate-700';
});
</script>
