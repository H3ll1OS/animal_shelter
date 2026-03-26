<template>
    <AdminLayout>
        <section class="euthanasia-edit-page">
            <header class="edit-head">
                <p class="edit-kicker">Health Records</p>
                <h1>Edit Euthanasia Record</h1>
                <p>Update details for {{ log.pet?.name || 'this pet' }}.</p>
            </header>

            <EuthanasiaForm
                :pets="pets"
                :log="log"
                submit-label="Save Changes"
                :cancel-url="`/admin/health/euthanasia/${log.id}`"
            />
        </section>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import EuthanasiaForm from '@/components/admin/health/EuthanasiaForm.vue';

defineProps({
    log: {
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
.euthanasia-edit-page {
    max-width: 980px;
    margin: 0 auto;
    padding: 14px 20px 18px;
}

.edit-head {
    margin-bottom: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: linear-gradient(180deg, #ffffff 0%, #fff1f2 100%);
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
    .euthanasia-edit-page {
        padding: 16px;
    }
}
</style>
