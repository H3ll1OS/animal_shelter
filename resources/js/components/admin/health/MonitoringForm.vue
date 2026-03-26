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
                <label for="diagnosis">Diagnosis <span class="required">*</span></label>
                <input id="diagnosis" v-model="form.diagnosis" type="text" required>
                <p v-if="form.errors.diagnosis" class="field-error">{{ form.errors.diagnosis }}</p>
            </div>

            <div class="admin-form-field">
                <label for="veterinarian">Veterinarian <span class="required">*</span></label>
                <input id="veterinarian" v-model="form.veterinarian" type="text" required>
                <p v-if="form.errors.veterinarian" class="field-error">{{ form.errors.veterinarian }}</p>
            </div>

            <div class="admin-form-field admin-form-field--full">
                <label for="description">Description <span class="required">*</span></label>
                <textarea id="description" v-model="form.description" rows="2" required></textarea>
                <p v-if="form.errors.description" class="field-error">{{ form.errors.description }}</p>
            </div>

            <div class="admin-form-field admin-form-field--full">
                <label for="notes">Notes</label>
                <textarea id="notes" v-model="form.notes" rows="2"></textarea>
                <p v-if="form.errors.notes" class="field-error">{{ form.errors.notes }}</p>
            </div>

            <div class="admin-form-field admin-form-field--full">
                <label class="checkbox">
                    <input v-model="form.is_critical" type="checkbox">
                    <span>Mark as critical</span>
                </label>
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
    alert: { type: Object, default: null },
    submitLabel: { type: String, default: 'Save Alert' },
    cancelUrl: { type: String, default: '/admin/health/monitoring' },
});

const form = useForm({
    pet_id: props.alert?.pet_id ?? '',
    diagnosis: props.alert?.diagnosis ?? '',
    description: props.alert?.description ?? '',
    veterinarian: props.alert?.veterinarian ?? '',
    notes: props.alert?.notes ?? '',
    is_critical: !!props.alert?.is_critical,
});

function submit() {
    if (props.alert?.id) {
        form.put(`/admin/health/monitoring/${props.alert.id}`);
    } else {
        form.post('/admin/health/monitoring');
    }
}
</script>

<style scoped>
.admin-form-card { background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:18px;box-shadow:0 10px 24px rgba(0,0,0,.06); }
.admin-form-grid { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px; }
.admin-form-field { display:flex;flex-direction:column;gap:6px; }
.admin-form-field--full { grid-column:1/-1; }
.admin-form-field input,.admin-form-field select,.admin-form-field textarea{border:1px solid #d1d5db;border-radius:10px;padding:9px 11px;outline:none;}
.admin-form-field input:focus,.admin-form-field select:focus,.admin-form-field textarea:focus{border-color:#ff6b35;box-shadow:0 0 0 3px rgba(255,107,53,.12);}
.admin-form-field textarea{resize:vertical;min-height:72px;}
.checkbox{display:inline-flex;align-items:center;gap:8px;font-weight:600;color:#374151;}
.required{color:#dc2626;}
.field-error{color:#dc2626;font-size:.85rem;margin:0;}
.admin-form-actions{margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;gap:12px;}
@media (max-width:768px){.admin-form-grid{grid-template-columns:1fr;}}
</style>
