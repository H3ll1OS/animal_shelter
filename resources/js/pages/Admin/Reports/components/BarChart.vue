<template>
    <div class="chart" role="img" :aria-label="ariaLabel">
        <div v-if="!hasData" class="empty">No data for this period</div>
        <div v-for="(label, index) in labels" v-else :key="label" class="row">
            <span class="label">{{ label }}</span>
            <div class="track" :aria-label="`${label}: ${values[index]}`">
                <div class="bar" :style="{ width: `${widths[index]}%` }"></div>
            </div>
            <strong>{{ Number(values[index]).toLocaleString() }}</strong>
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
.chart { display:grid; gap:.95rem; min-height:112px; align-content:center; padding:.15rem 0; }
.row { display:grid; grid-template-columns:minmax(90px,.8fr) minmax(70px,2fr) 36px; align-items:center; gap:.75rem; }
.label { overflow:hidden; color:#5f6b7b; font-size:.8rem; font-weight:550; text-overflow:ellipsis; white-space:nowrap; }
.track { height:9px; border-radius:999px; background:#f1f4f8; overflow:hidden; }
.bar { height:100%; min-width:3px; border-radius:999px; background:linear-gradient(90deg,#fb923c,#f97316); transition:width .35s ease; }
.row strong { color:#243044; font-size:.8rem; font-variant-numeric:tabular-nums; text-align:right; }
.empty { display:grid; place-items:center; min-height:112px; border:1px dashed #dbe1e9; border-radius:10px; background:#fafbfc; color:#8490a2; text-align:center; font-size:.82rem; }
@media (max-width:480px) { .row { grid-template-columns:minmax(76px,.9fr) minmax(50px,2fr) 30px; gap:.5rem; } }
</style>
