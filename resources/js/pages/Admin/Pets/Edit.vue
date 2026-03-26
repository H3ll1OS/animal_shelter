<template>
    <AdminLayout>
        <section class="admin-page-container pet-edit-page">
            <div class="pet-edit-card">
                <div class="pet-edit-header">
                    <p class="pet-edit-kicker">Pet Registry</p>
                    <h1>Edit Profile for {{ pet.name }}</h1>
                    <p>Update pet details and keep records current for adoption and care workflows.</p>
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
                                <option v-for="species in ['Dog','Cat','Rabbit','Bird','Other']" :key="species" :value="species">{{ species }}</option>
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
                                <option v-for="gender in ['Male','Female','Unknown']" :key="gender" :value="gender">{{ gender }}</option>
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
                                <option v-for="status in ['Available','Fostered','Sick','Adopted']" :key="status" :value="status">{{ status }}</option>
                            </select>
                            <p v-if="form.errors.status" class="field-error">{{ form.errors.status }}</p>
                        </div>

                        <div class="field field--wide">
                            <label for="image">Pet Image</label>
                            <div class="image-row">
                                <img v-if="pet.image_path" :src="`/storage/${pet.image_path}`" alt="Current Pet Image" class="current-thumb">
                                <span v-else class="thumb-fallback">No current image.</span>
                                <input id="image" type="file" accept="image/*" class="file-control" @change="onFileChange">
                            </div>
                            <p v-if="form.errors.image" class="field-error">{{ form.errors.image }}</p>
                        </div>
                    </div>

                    <div class="field field--full">
                        <label for="description">Description / Notes</label>
                        <textarea id="description" v-model="form.description" rows="4" class="form-control form-textarea"></textarea>
                        <p v-if="form.errors.description" class="field-error">{{ form.errors.description }}</p>
                    </div>

                    <div class="form-actions">
                        <Link :href="`/admin/pets/${pet.id}`" class="btn btn-secondary">Cancel</Link>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">Update Pet</button>
                    </div>
                </form>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({ pet: { type: Object, required: true } });

const form = useForm({
    name: props.pet.name || '', species: props.pet.species || '', breed: props.pet.breed || '', age: props.pet.age ?? null,
    gender: props.pet.gender || 'Unknown', date_received: String(props.pet.date_received || '').slice(0, 10),
    status: props.pet.status || 'Available', image: null, description: props.pet.description || '',
});

function onFileChange(event) { form.image = event.target.files[0] || null; }
function submit() { form.post(`/admin/pets/${props.pet.id}`, { method: 'put', forceFormData: true }); }
</script>

<style scoped>
.pet-edit-page {
    max-width: 1100px;
}

.pet-edit-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    padding: 26px;
}

.pet-edit-header {
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid #e2e8f0;
}

.pet-edit-kicker {
    margin: 0;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    font-weight: 700;
}

.pet-edit-header h1 {
    margin: 6px 0 4px;
    font-size: clamp(1.65rem, 2.2vw, 2.2rem);
    font-weight: 800;
    color: #0f172a;
}

.pet-edit-header p {
    margin: 0;
    color: #64748b;
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

.image-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.current-thumb {
    width: 56px;
    height: 56px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid #cbd5e1;
}

.thumb-fallback {
    color: #64748b;
    font-size: 0.9rem;
}

.file-control {
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
