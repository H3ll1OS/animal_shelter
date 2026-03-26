<template>
    <AdminLayout>
        <section class="admin-page-container pet-create-page">
            <div class="pet-create-card">
                <div class="pet-create-header">
                    <p class="pet-create-kicker">Pet Registry</p>
                    <h1>Create New Pet Profile</h1>
                    <p>Add a complete profile to keep shelter records accurate and adoption-ready.</p>
                </div>

                <div v-if="flash.success" class="flash-success" role="alert">
                    <p>{{ flash.success }}</p>
                </div>

                <form @submit.prevent="submit" enctype="multipart/form-data" class="pet-form">
                    <div class="form-grid">
                        <div class="field">
                            <label for="name">Name *</label>
                            <input id="name" v-model="form.name" type="text" required class="form-control" :class="{ 'border-red-500': form.errors.name }">
                            <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                        </div>

                        <div class="field">
                            <label for="species">Species *</label>
                            <select id="species" v-model="form.species" required class="form-control" :class="{ 'border-red-500': form.errors.species }">
                                <option value="">Select Species</option>
                                <option value="Dog">Dog</option>
                                <option value="Cat">Cat</option>
                                <option value="Rabbit">Rabbit</option>
                                <option value="Bird">Bird</option>
                                <option value="Other">Other</option>
                            </select>
                            <p v-if="form.errors.species" class="field-error">{{ form.errors.species }}</p>
                        </div>

                        <div class="field">
                            <label for="breed">Breed</label>
                            <input id="breed" v-model="form.breed" type="text" class="form-control">
                            <p v-if="form.errors.breed" class="field-error">{{ form.errors.breed }}</p>
                        </div>

                        <div class="field">
                            <label for="age">Age (Years) *</label>
                            <input id="age" v-model.number="form.age" type="number" required min="0" class="form-control" :class="{ 'border-red-500': form.errors.age }">
                            <p v-if="form.errors.age" class="field-error">{{ form.errors.age }}</p>
                        </div>

                        <div class="field">
                            <label for="gender">Gender *</label>
                            <select id="gender" v-model="form.gender" required class="form-control" :class="{ 'border-red-500': form.errors.gender }">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Unknown">Unknown</option>
                            </select>
                            <p v-if="form.errors.gender" class="field-error">{{ form.errors.gender }}</p>
                        </div>

                        <div class="field">
                            <label for="date_received">Date Received *</label>
                            <input id="date_received" v-model="form.date_received" type="date" required class="form-control" :class="{ 'border-red-500': form.errors.date_received }">
                            <p v-if="form.errors.date_received" class="field-error">{{ form.errors.date_received }}</p>
                        </div>

                        <div class="field">
                            <label for="status">Adoption Status *</label>
                            <select id="status" v-model="form.status" required class="form-control" :class="{ 'border-red-500': form.errors.status }">
                                <option value="Available">Available</option>
                                <option value="Fostered">Fostered</option>
                                <option value="Sick">Sick</option>
                                <option value="Adopted">Adopted</option>
                            </select>
                            <p v-if="form.errors.status" class="field-error">{{ form.errors.status }}</p>
                        </div>

                        <div class="field field--wide">
                            <label for="image">Pet Image (Max 2MB)</label>
                            <input id="image" type="file" accept="image/*" class="file-control" @change="onFileChange">
                            <p v-if="form.errors.image" class="field-error">{{ form.errors.image }}</p>
                        </div>
                    </div>

                    <div class="field field--full">
                        <label for="description">Description / Notes</label>
                        <textarea id="description" v-model="form.description" rows="4" class="form-control form-textarea"></textarea>
                        <p v-if="form.errors.description" class="field-error">{{ form.errors.description }}</p>
                    </div>

                    <div class="form-actions">
                        <Link href="/admin/pets" class="btn btn-secondary">Cancel</Link>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">Register Pet</button>
                    </div>
                </form>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const page = usePage();
const flash = computed(() => page.props.flash || {});

const form = useForm({
    name: '', species: '', breed: '', age: null, gender: '',
    date_received: new Date().toISOString().slice(0, 10),
    status: 'Available', image: null, description: '',
});

function onFileChange(event) { form.image = event.target.files[0] || null; }
function submit() { form.post('/admin/pets', { forceFormData: true }); }
</script>

<style scoped>
.pet-create-page {
    max-width: 1100px;
}

.pet-create-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    padding: 26px;
}

.pet-create-header {
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid #e2e8f0;
}

.pet-create-kicker {
    margin: 0;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    font-weight: 700;
}

.pet-create-header h1 {
    margin: 6px 0 4px;
    font-size: clamp(1.65rem, 2.2vw, 2.2rem);
    font-weight: 800;
    color: #0f172a;
}

.pet-create-header p {
    margin: 0;
    color: #64748b;
}

.flash-success {
    margin-bottom: 18px;
    border: 1px solid #bbf7d0;
    background: #f0fdf4;
    color: #166534;
    border-radius: 12px;
    padding: 11px 14px;
}

.pet-form {
    display: grid;
    gap: 18px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 16px 18px;
}

@media (min-width: 900px) {
    .form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

.field label {
    display: block;
    font-size: 0.92rem;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
}

.form-control {
    width: 100%;
    min-height: 44px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 10px 12px;
    color: #0f172a;
    background: #ffffff;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.18);
}

.form-textarea {
    min-height: 120px;
    resize: vertical;
}

.file-control {
    width: 100%;
    color: #475569;
    font-size: 0.92rem;
}

.file-control::file-selector-button {
    margin-right: 10px;
    border: 0;
    border-radius: 999px;
    background: #ffedd5;
    color: #9a3412;
    padding: 8px 14px;
    font-weight: 700;
    cursor: pointer;
}

.field--wide,
.field--full {
    grid-column: 1 / -1;
}

.field-error {
    margin-top: 5px;
    font-size: 0.78rem;
    color: #dc2626;
}

.form-actions {
    margin-top: 4px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    padding: 10px 15px;
    font-size: 0.92rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
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
    border: 1px solid transparent;
    background: #f97316;
    color: #ffffff;
}

.btn-primary:hover {
    background: #ea580c;
}

.btn-primary:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}
</style>
