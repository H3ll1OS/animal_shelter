<template>
    <nav v-if="links && links.length > 3" class="pagination-links">
        <Link
            v-for="(link, index) in links"
            :key="index"
            :href="link.url || ''"
            class="pagination-link"
            :class="{ 'is-active': link.active, 'is-disabled': !link.url }"
            v-html="link.label"
            :preserve-scroll="true"
        />
    </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>

<style scoped>
.pagination-links {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.pagination-link {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 0.85rem;
    color: #4b5563;
    text-decoration: none;
    background: #fff;
}

.pagination-link.is-active {
    background: #ff6b35;
    border-color: #ff6b35;
    color: #fff;
}

.pagination-link.is-disabled {
    opacity: 0.45;
    pointer-events: none;
}
</style>
