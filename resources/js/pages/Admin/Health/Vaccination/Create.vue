<template>
    <AdminLayout>
        <section class="vaccination-create-page">
            <header class="create-head">
                <p class="create-kicker">Health Records</p>
                <h1>New Vaccination Schedule</h1>
                <p>Create a follow-up vaccination reminder for a pet.</p>
            </header>

            <form class="form-card" @submit.prevent="submit">
                <div class="form-grid">
                    <div class="field field-full">
                        <label for="pet_id">Select Pet <span class="required">*</span></label>
                        <select id="pet_id" v-model="form.pet_id" required>
                            <option value="">-- Select an Animal --</option>
                            <option v-for="pet in pets" :key="pet.id" :value="String(pet.id)">{{ pet.name }}</option>
                        </select>
                        <p v-if="form.errors.pet_id" class="field-error">{{ form.errors.pet_id }}</p>
                    </div>

                    <div class="field">
                        <label for="vaccine_type">Vaccine Type <span class="required">*</span></label>
                        <input
                            id="vaccine_type"
                            v-model="form.vaccine_type"
                            type="text"
                            required
                            placeholder="e.g., Rabies Booster"
                        >
                        <p v-if="form.errors.vaccine_type" class="field-error">{{ form.errors.vaccine_type }}</p>
                    </div>

                    <div class="field">
                        <label for="next_due_date">Next Due Date <span class="required">*</span></label>
                        <input id="next_due_date" v-model="form.next_due_date" type="date" required>
                        <p v-if="form.errors.next_due_date" class="field-error">{{ form.errors.next_due_date }}</p>
                    </div>

                    <div class="field field-full">
                        <label for="notes">Notes (Optional)</label>
                        <textarea id="notes" v-model="form.notes" rows="3" placeholder="Add any special instructions or context."></textarea>
                        <p v-if="form.errors.notes" class="field-error">{{ form.errors.notes }}</p>
                    </div>
                </div>

                <div class="form-actions">
                    <Link href="/admin/health/vaccination-schedule" class="btn-secondary">Cancel</Link>
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Scheduling...' : 'Schedule Vaccination' }}
                    </button>
                </div>
            </form>
        </section>
    </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    pets: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    pet_id: '',
    vaccine_type: '',
    next_due_date: '',
    notes: '',
});

function submit() {
    form.post('/admin/health/vaccination-schedule');
}
</script>

<style scoped>
.vaccination-create-page {
    max-width: 980px;
    margin: 0 auto;
    padding: 16px 20px 24px;
}

.create-head {
    margin-bottom: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    padding: 15px 18px;
}

.create-kicker {
    margin: 0 0 5px;
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
    margin: 5px 0 0;
    color: #475569;
}

.form-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    padding: 16px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field-full {
    grid-column: 1 / -1;
}

.field label {
    color: #374151;
    font-size: 0.92rem;
    font-weight: 700;
}

.required {
    color: #dc2626;
}

.field input,
.field select,
.field textarea {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    background: #ffffff;
    padding: 9px 11px;
    color: #0f172a;
    outline: none;
}

.field input:focus,
.field select:focus,
.field textarea:focus {
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.14);
}

.field textarea {
    resize: vertical;
    min-height: 92px;
}

.field-error {
    margin: 0;
    color: #dc2626;
    font-size: 0.82rem;
    font-weight: 600;
}

.form-actions {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.btn-secondary,
.btn-primary {
    min-height: 38px;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 14px;
}

.btn-secondary {
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
}

.btn-secondary:hover {
    background: #f8fafc;
}

.btn-primary {
    border: 0;
    color: #fff;
    background: linear-gradient(135deg, #fb923c 0%, #f97316 70%, #ea580c 100%);
    box-shadow: 0 10px 24px rgba(249, 115, 22, 0.25);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    box-shadow: none;
}

@media (max-width: 760px) {
    .vaccination-create-page {
        padding: 16px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .btn-secondary,
    .btn-primary {
        width: 100%;
    }
}
</style>
