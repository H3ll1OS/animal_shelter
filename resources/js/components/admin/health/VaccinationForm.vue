<template>
    <form @submit.prevent="submit" class="admin-form-card">
        <div class="admin-form-grid">
            <div class="admin-form-field admin-form-field--full">
                <label for="pet_id">Pet <span class="required">*</span></label>
                <select id="pet_id" v-model="form.pet_id" required>
                    <option value="">Select a pet</option>
                    <option v-for="pet in pets" :key="pet.id" :value="pet.id">{{ pet.name }}</option>
                </select>
                <p v-if="form.errors.pet_id" class="field-error">{{ form.errors.pet_id }}</p>
            </div>

            <div class="admin-form-field">
                <label for="vaccine_type">Vaccine Type <span class="required">*</span></label>
                <input id="vaccine_type" v-model="form.vaccine_type" type="text" required>
                <p v-if="form.errors.vaccine_type" class="field-error">{{ form.errors.vaccine_type }}</p>
            </div>

            <div class="admin-form-field">
                <label for="next_due_date">Next Due Date <span class="required">*</span></label>
                <input id="next_due_date" v-model="form.next_due_date" type="date" required>
                <p v-if="form.errors.next_due_date" class="field-error">{{ form.errors.next_due_date }}</p>
            </div>

            <div class="admin-form-field admin-form-field--full">
                <label for="notes">Notes</label>
                <textarea id="notes" v-model="form.notes" rows="3"></textarea>
                <p v-if="form.errors.notes" class="field-error">{{ form.errors.notes }}</p>
            </div>
        </div>

        <div class="admin-form-actions">
            <Link href="/admin/health/vaccination-schedule" class="account-back">Cancel</Link>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                {{ form.processing ? 'Saving...' : 'Schedule Vaccination' }}
            </button>
        </div>
    </form>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    pets: { type: Array, default: () => [] },
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
.admin-form-card { background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:24px;box-shadow:0 10px 24px rgba(0,0,0,.06); }
.admin-form-grid { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px; }
.admin-form-field { display:flex;flex-direction:column;gap:6px; }
.admin-form-field--full { grid-column:1/-1; }
.admin-form-field input,.admin-form-field select,.admin-form-field textarea{border:1px solid #d1d5db;border-radius:10px;padding:10px 12px;outline:none;}
.admin-form-field input:focus,.admin-form-field select:focus,.admin-form-field textarea:focus{border-color:#ff6b35;box-shadow:0 0 0 3px rgba(255,107,53,.12);}
.required{color:#dc2626;}
.field-error{color:#dc2626;font-size:.85rem;margin:0;}
.admin-form-actions{margin-top:18px;padding-top:16px;border-top:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;gap:12px;}
@media (max-width:768px){.admin-form-grid{grid-template-columns:1fr;}}
</style>
