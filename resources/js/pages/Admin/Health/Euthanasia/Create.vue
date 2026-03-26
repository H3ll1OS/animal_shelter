<template>
    <AdminLayout>
        <section class="euthanasia-create-page">
            <header class="create-head">
                <p class="create-kicker">Health Records</p>
                <h1>Log Euthanasia</h1>
                <p>Record the details for a pet euthanasia.</p>
            </header>

            <EuthanasiaForm
                :pets="pets"
                submit-label="Save Record"
                cancel-url="/admin/health/euthanasia"
            />
        </section>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import EuthanasiaForm from '@/components/admin/health/EuthanasiaForm.vue';

defineProps({
    pets: {
        type: Array,
        default: () => [],
    },
});
</script>

<style scoped>
.euthanasia-create-page {
    max-width: 980px;
    margin: 0 auto;
    padding: 14px 20px 18px;
}

.create-head {
    margin-bottom: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: linear-gradient(180deg, #ffffff 0%, #fff1f2 100%);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    padding: 14px 18px;
}

.create-kicker {
    margin: 0 0 6px;
    font-size: 0.76rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-weight: 800;
    color: #64748b;
}

.create-head h1 {
    margin: 0;
    font-size: clamp(1.6rem, 2.5vw, 2rem);
    line-height: 1.15;
    color: #0f172a;
}

.create-head p {
    margin: 4px 0 0;
    color: #475569;
}

@media (max-width: 760px) {
    .euthanasia-create-page {
        padding: 16px;
    }
}
</style>
