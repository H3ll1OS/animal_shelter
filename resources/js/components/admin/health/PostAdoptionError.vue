<template><small v-if="message" class="mt-1 block font-normal text-red-600">{{ message }}</small></template>
<script setup>defineProps({ message: { type: String, default: '' } });</script>
