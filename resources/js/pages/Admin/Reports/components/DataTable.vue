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
.table-wrap { overflow-x:auto; border:1px solid #edf0f4; border-radius:9px; background:#fff; }
table { width:100%; border-collapse:separate; border-spacing:0; text-align:left; }
:deep(th) { position:sticky; top:0; background:#f8fafc; color:#8290a2; text-align:left; text-transform:uppercase; letter-spacing:.06em; font-size:.66rem; font-weight:800; white-space:nowrap; }
:deep(th), :deep(td) { border-bottom:1px solid #edf0f4; padding:.78rem .85rem; }
:deep(td) { color:#596579; font-size:.8rem; white-space:nowrap; }
:deep(tbody tr) { transition:background .12s ease; }
:deep(tbody tr:hover) { background:#fafbfc; }
:deep(tbody tr:last-child td) { border-bottom:0; }
:deep(.empty) { text-align:center; color:#8490a2; padding:1.75rem; white-space:normal; }
@media (max-width:480px) { :deep(th), :deep(td) { padding:.65rem; } }
</style>
