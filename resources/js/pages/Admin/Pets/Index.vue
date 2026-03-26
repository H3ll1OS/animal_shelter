<template>
    <AdminLayout>
        <section class="admin-page-container pets-index-page">
            <div class="pets-header">
                <h2>All Registered Pets</h2>
                <Link href="/admin/pets/create" class="add-pet-btn">
                    + Add New Pet
                </Link>
            </div>

            <div class="pets-table-card">
                <table class="pets-table">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Species</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Breed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="pet in pets.data" :key="pet.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ pet.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ pet.species }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ pet.breed }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClass(pet.status)">{{ pet.status }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="action-group">
                                    <Link :href="`/admin/pets/${pet.id}`" class="view-link">View</Link>

                                    <Link
                                        :href="`/admin/pets/${pet.id}/edit`"
                                        class="action-icon-btn action-icon-btn--edit"
                                        title="Edit pet profile"
                                        aria-label="Edit pet profile"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>

                                    <Link
                                        :href="`/admin/pets/${pet.id}`"
                                        method="delete"
                                        as="button"
                                        class="action-icon-btn action-icon-btn--delete"
                                        title="Delete pet profile"
                                        aria-label="Delete pet profile"
                                        @before="(visit) => confirmDelete(visit, pet.name)"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8" />
                                        </svg>
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="pets.data.length === 0"><td colspan="5" class="px-6 py-4 text-center text-gray-500">No pets currently in the system.</td></tr>
                    </tbody>
                </table>

                <div class="p-4">
                    <PaginationLinks :links="pets.links" />
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PaginationLinks from '@/components/common/PaginationLinks.vue';

defineProps({
    pets: { type: Object, required: true },
});

function statusClass(status) {
    if (status === 'Available') return 'bg-green-100 text-green-800';
    if (status === 'Adopted') return 'bg-indigo-100 text-indigo-800';
    return 'bg-yellow-100 text-yellow-800';
}

function confirmDelete(visit, name) {
    if (!window.confirm(`Are you sure you want to permanently delete the profile for ${name}? This action cannot be undone.`)) {
        visit.cancel();
    }
}
</script>

<style scoped>
.pets-index-page {
    padding-top: 0.5rem;
}

.pets-header {
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.pets-header h2 {
    margin: 0;
    font-size: 1.9rem;
    line-height: 1.15;
    font-weight: 800;
    color: #0f172a;
}

.add-pet-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
    background: #f97316;
    color: #ffffff;
    font-weight: 700;
    text-decoration: none;
    padding: 0.65rem 0.95rem;
    box-shadow: 0 8px 16px rgba(249, 115, 22, 0.28);
    transition: background-color 0.2s ease;
}

.add-pet-btn:hover {
    background: #ea580c;
}

.pets-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
    overflow: hidden;
}

.pets-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
}

.pets-table thead {
    background: #f8fafc;
}

.pets-table th,
.pets-table td {
    padding-top: 0.8rem !important;
    padding-bottom: 0.8rem !important;
}

.pets-table th:first-child,
.pets-table td:first-child {
    padding-left: 1.3rem !important;
}

.pets-table th:last-child,
.pets-table td:last-child {
    padding-right: 1.3rem !important;
}

.pets-table th {
    font-size: 0.78rem !important;
    letter-spacing: 0.06em;
}

.pets-table td {
    font-size: 1rem !important;
}

.action-group {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
}

.view-link {
    color: #2563eb;
    font-weight: 600;
    text-decoration: none;
    padding: 0.25rem 0.3rem;
}

.view-link:hover {
    color: #1d4ed8;
}

.action-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.15rem;
    height: 2.15rem;
    border-radius: 0.5rem;
    border: 1px solid transparent;
    background: transparent;
    transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.action-icon-btn svg {
    width: 1.05rem;
    height: 1.05rem;
}

.action-icon-btn--edit {
    color: #475569;
    border-color: #cbd5e1;
}

.action-icon-btn--edit:hover {
    background: #f8fafc;
    color: #0f172a;
}

.action-icon-btn--delete {
    color: #dc2626;
    border-color: #fecaca;
}

.action-icon-btn--delete:hover {
    background: #fef2f2;
    color: #b91c1c;
}
</style>
