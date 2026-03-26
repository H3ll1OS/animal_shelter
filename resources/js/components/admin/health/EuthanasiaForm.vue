<template>
    <form @submit.prevent="submit" class="admin-form-card">
        <div class="admin-form-grid">
            <div class="admin-form-field admin-form-field--full">
                <label for="pet_id">Pet <span class="required">*</span></label>
                <select id="pet_id" v-model="form.pet_id" required>
                    <option value="">Select a pet</option>
                    <option v-for="pet in pets" :key="pet.id" :value="pet.id">
                        {{ pet.name }} ({{ pet.status || 'N/A' }})
                    </option>
                </select>
                <p v-if="form.errors.pet_id" class="field-error">{{ form.errors.pet_id }}</p>
            </div>

            <div class="admin-form-field">
                <label for="reason">Reason <span class="required">*</span></label>
                <input id="reason" v-model="form.reason" type="text" required placeholder="e.g., Severe illness">
                <p v-if="form.errors.reason" class="field-error">{{ form.errors.reason }}</p>
            </div>

            <div class="admin-form-field">
                <label for="veterinarian">Veterinarian <span class="required">*</span></label>
                <input id="veterinarian" v-model="form.veterinarian" type="text" required placeholder="Dr. Smith">
                <p v-if="form.errors.veterinarian" class="field-error">{{ form.errors.veterinarian }}</p>
            </div>

            <div class="admin-form-field">
                <label for="euthanasia_date">Euthanasia Date <span class="required">*</span></label>
                <input id="euthanasia_date" v-model="form.euthanasia_date" type="date" required>
                <p v-if="form.errors.euthanasia_date" class="field-error">{{ form.errors.euthanasia_date }}</p>
            </div>

            <div class="admin-form-field admin-form-field--full">
                <label for="notes">Notes</label>
                <textarea id="notes" v-model="form.notes" rows="3" placeholder="Additional context, procedure notes, or follow-up."></textarea>
                <p v-if="form.errors.notes" class="field-error">{{ form.errors.notes }}</p>
            </div>
        </div>

        <div class="admin-form-actions">
            <Link :href="cancelUrl" class="account-back">Cancel</Link>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                {{ form.processing ? 'Saving...' : submitLabel }}
            </button>
        </div>
    </form>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    pets: { type: Array, default: () => [] },
    log: { type: Object, default: null },
    submitLabel: { type: String, default: 'Save Record' },
    cancelUrl: { type: String, default: '/admin/health/euthanasia' },
});

const form = useForm({
    pet_id: props.log?.pet_id ?? '',
    reason: props.log?.reason ?? '',
    euthanasia_date: props.log?.euthanasia_date ? String(props.log.euthanasia_date).slice(0, 10) : '',
    veterinarian: props.log?.veterinarian ?? '',
    notes: props.log?.notes ?? '',
});

function submit() {
    if (props.log?.id) {
        form.put(`/admin/health/euthanasia/${props.log.id}`);
    } else {
        form.post('/admin/health/euthanasia');
    }
}
</script>

<style scoped>
.admin-form-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
}

.admin-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.admin-form-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.admin-form-field--full {
    grid-column: 1 / -1;
}

.admin-form-field label {
    font-weight: 600;
    color: #374151;
}

.admin-form-field input,
.admin-form-field select,
.admin-form-field textarea {
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 10px 12px;
    outline: none;
}

.admin-form-field input:focus,
.admin-form-field select:focus,
.admin-form-field textarea:focus {
    border-color: #ff6b35;
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.12);
}

.required {
    color: #dc2626;
}

.field-error {
    color: #dc2626;
    font-size: 0.85rem;
    margin: 0;
}

.admin-form-actions {
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

@media (max-width: 768px) {
    .admin-form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
