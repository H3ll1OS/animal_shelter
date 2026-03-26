<template>
    <AdminLayout>
        <section class="monitoring-edit-page">
            <header class="edit-head">
                <p class="edit-kicker">Health Monitoring</p>
                <h1>Edit Alert</h1>
                <p>Update details for {{ alert.pet?.name || 'this pet' }}.</p>
            </header>

            <MonitoringForm
                :pets="pets"
                :alert="alert"
                submit-label="Save Changes"
                :cancel-url="`/admin/health/monitoring/${alert.id}`"
            />
        </section>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import MonitoringForm from '@/components/admin/health/MonitoringForm.vue';

defineProps({
    alert: {
        type: Object,
        required: true,
    },
    pets: {
        type: Array,
        default: () => [],
    },
});
</script>

<style scoped>
.monitoring-edit-page {
    max-width: 980px;
    margin: 0 auto;
    padding: 14px 20px 18px;
}

.edit-head {
    margin-bottom: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: linear-gradient(180deg, #ffffff 0%, #eff6ff 100%);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    padding: 14px 18px;
}

.edit-kicker {
    margin: 0 0 6px;
    font-size: 0.76rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-weight: 800;
    color: #64748b;
}

.edit-head h1 {
    margin: 0;
    font-size: clamp(1.6rem, 2.5vw, 2rem);
    line-height: 1.15;
    color: #0f172a;
}

.edit-head p {
    margin: 4px 0 0;
    color: #475569;
}

@media (max-width: 760px) {
    .monitoring-edit-page {
        padding: 16px;
    }
}
</style>
