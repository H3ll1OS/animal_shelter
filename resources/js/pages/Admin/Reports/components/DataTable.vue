<template>
    <div class="table-wrap">
        <table>
            <thead><tr><th v-for="column in columns" :key="column">{{ column }}</th></tr></thead>
            <tbody>
                <tr v-for="(row, index) in rows" :key="row.id || index">
                    <slot name="row" :row="row"></slot>
                </tr>
                <tr v-if="!rows.length"><td :colspan="columns.length" class="empty">{{ empty }}</td></tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({
    rows: { type: Array, default: () => [] },
    columns: { type: Array, default: () => [] },
    empty: { type: String, default: 'No records found.' },
});
</script>

<style scoped>
.table-wrap { overflow-x:auto; }
table { width:100%; border-collapse:collapse; }
th { background:#f8fafc; color:#64748b; text-align:left; text-transform:uppercase; font-size:.72rem; }
th, td { border-bottom:1px solid #e2e8f0; padding:.75rem; }
.empty { text-align:center; color:#64748b; }
</style>
