<template>
    <AdminLayout>
        <section class="admin-page-container pet-show-page">
            <article class="pet-show-card">
                <header class="pet-show-header">
                    <div>
                        <p class="pet-show-kicker">Pet Profile</p>
                        <h1>{{ pet.name }}</h1>
                    </div>

                    <div class="pet-show-actions">
                        <Link :href="`/admin/pets/${pet.id}/edit`" class="btn btn-primary">Edit Profile</Link>
                        <Link href="/admin/pets" class="btn btn-secondary">Back to Inventory</Link>
                    </div>
                </header>

                <div class="pet-show-grid">
                    <section class="pet-media-card">
                        <h2>Pet Photo</h2>
                        <img v-if="pet.image_path" :src="`/storage/${pet.image_path}`" :alt="pet.name" class="pet-photo">
                        <div v-else class="pet-photo-fallback">No Image Available</div>
                    </section>

                    <section class="pet-info-card">
                        <h2>Basic Information</h2>
                        <dl class="info-list">
                            <div class="info-row"><dt>Species</dt><dd>{{ pet.species }}</dd></div>
                            <div class="info-row"><dt>Breed</dt><dd>{{ pet.breed || 'N/A' }}</dd></div>
                            <div class="info-row"><dt>Age</dt><dd>{{ pet.age }} years</dd></div>
                            <div class="info-row"><dt>Gender</dt><dd>{{ pet.gender }}</dd></div>
                            <div class="info-row"><dt>Date Received</dt><dd>{{ formatDateLong(pet.date_received) }}</dd></div>
                            <div class="info-row">
                                <dt>Status</dt>
                                <dd>
                                    <span class="status-pill" :class="statusClass(pet.status)">{{ pet.status }}</span>
                                </dd>
                            </div>
                        </dl>
                    </section>
                </div>

                <section class="pet-section">
                    <h2>Description</h2>
                    <p class="section-text">{{ pet.description || 'No description provided.' }}</p>
                </section>

                <section class="pet-section">
                    <h2>Health Records</h2>
                    <div v-if="pet.health_records && pet.health_records.length" class="records-table-wrap">
                        <table class="records-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="record in sortedHealthRecords" :key="record.id">
                                    <td>{{ record.type }}</td>
                                    <td>{{ formatDateLong(record.date) || 'N/A' }}</td>
                                    <td>{{ limitText(record.notes, 80) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="section-muted">No health records found for this pet.</p>
                </section>
            </article>
        </section>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    pet: { type: Object, required: true },
});

const sortedHealthRecords = computed(() => {
    const records = props.pet.health_records || [];
    return [...records].sort((a, b) => String(b.date || '').localeCompare(String(a.date || '')));
});

function formatDateLong(value) {
    if (!value) return '';
    return new Date(value).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
}

function limitText(value, max) {
    const text = value || '';
    if (text.length <= max) return text;
    return `${text.slice(0, max)}...`;
}

function statusClass(status) {
    if (status === 'Available') return 'bg-green-100 text-green-800';
    if (status === 'Adopted') return 'bg-indigo-100 text-indigo-800';
    return 'bg-yellow-100 text-yellow-800';
}
</script>

<style scoped>
.pet-show-page {
    max-width: 1120px;
}

.pet-show-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
    padding: 24px;
    display: grid;
    gap: 18px;
}

.pet-show-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    padding-bottom: 14px;
    border-bottom: 1px solid #e2e8f0;
}

.pet-show-kicker {
    margin: 0;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
}

.pet-show-header h1 {
    margin: 6px 0 0;
    font-size: clamp(1.85rem, 2.3vw, 2.3rem);
    line-height: 1.1;
    color: #0f172a;
}

.pet-show-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.92rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-primary {
    color: #ffffff;
    background: #f97316;
}

.btn-primary:hover {
    background: #ea580c;
}

.btn-secondary {
    color: #334155;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
}

.btn-secondary:hover {
    background: #f1f5f9;
}

.pet-show-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 16px;
}

@media (min-width: 900px) {
    .pet-show-grid {
        grid-template-columns: 340px minmax(0, 1fr);
    }
}

.pet-media-card,
.pet-info-card,
.pet-section {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px;
    background: #ffffff;
}

.pet-media-card h2,
.pet-info-card h2,
.pet-section h2 {
    margin: 0 0 12px;
    font-size: 1.22rem;
    color: #0f172a;
}

.pet-photo {
    width: 100%;
    height: 290px;
    border-radius: 12px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
}

.pet-photo-fallback {
    min-height: 290px;
    border-radius: 12px;
    border: 1px dashed #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    background: #f8fafc;
}

.info-list {
    margin: 0;
    display: grid;
    gap: 10px;
}

.info-row {
    display: grid;
    grid-template-columns: 150px minmax(0, 1fr);
    align-items: center;
    gap: 8px;
    padding: 8px 0;
    border-bottom: 1px solid #f1f5f9;
}

.info-row:last-child {
    border-bottom: 0;
}

.info-row dt {
    margin: 0;
    font-weight: 700;
    color: #475569;
}

.info-row dd {
    margin: 0;
    color: #0f172a;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 0.65rem;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
}

.section-text {
    margin: 0;
    color: #334155;
    line-height: 1.7;
}

.section-muted {
    margin: 0;
    color: #64748b;
}

.records-table-wrap {
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
}

.records-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.records-table thead {
    background: #f8fafc;
}

.records-table th,
.records-table td {
    text-align: left;
    padding: 12px 14px;
    border-bottom: 1px solid #f1f5f9;
}

.records-table th {
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
}

.records-table td {
    font-size: 0.95rem;
    color: #334155;
}

.records-table tbody tr:last-child td {
    border-bottom: 0;
}

@media (max-width: 640px) {
    .pet-show-card {
        padding: 18px;
    }

    .pet-show-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .info-row {
        grid-template-columns: 1fr;
        gap: 4px;
    }
}
</style>
