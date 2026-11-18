<template>
    <div class="chart" role="img" :aria-label="ariaLabel">
        <div v-if="!hasData" class="empty">No data for this period</div>
        <div v-for="(label, index) in labels" v-else :key="label" class="row">
            <span class="label">{{ label }}</span>
            <div class="track"><div class="bar" :style="{ width: `${widths[index]}%` }"></div></div>
            <strong>{{ values[index] }}</strong>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({ labels: { type: Array, default: () => [] }, values: { type: Array, default: () => [] } });
const hasData = computed(() => props.values.some(value => Number(value) > 0));
const max = computed(() => Math.max(...props.values.map(Number), 1));
const widths = computed(() => props.values.map(value => Math.round((Number(value) / max.value) * 100)));
const ariaLabel = computed(() => props.labels.map((label, i) => `${label}: ${props.values[i]}`).join(', ') || 'No chart data');
</script>

<style scoped>
.chart { display:grid; gap:.7rem; min-height:120px; }
.row { display:grid; grid-template-columns:minmax(90px,1fr) 2fr 40px; align-items:center; gap:.6rem; }
.label { color:#475569; font-size:.82rem; }
.track { height:10px; border-radius:999px; background:#f1f5f9; overflow:hidden; }
.bar { height:100%; border-radius:999px; background:#f97316; }
.empty { color:#64748b; text-align:center; align-self:center; }
</style>
