<template>
    <AdminLayout>
        <section class="admin-page-container adoptions-page">
            <header class="adoptions-header">
                <h1>Adoption Applications Queue</h1>
                <p>Review, approve, or reject applications submitted by users.</p>
            </header>

            <div v-if="flash.success || flash.error" class="flash-wrap">
                <div v-if="flash.success" class="flash flash-success" role="alert">
                    <span>{{ flash.success }}</span>
                </div>
                <div v-if="flash.error" class="flash flash-error" role="alert">
                    <span>{{ flash.error }}</span>
                </div>
            </div>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="adoptions-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Applicant Name</th>
                                <th>Pet Applied For</th>
                                <th>Documents</th>
                                <th>Date Submitted</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="application in applications.data" :key="application.id">
                                <td class="font-semibold text-slate-900">#{{ application.id }}</td>
                                <td class="font-medium text-slate-900">{{ application.applicant_name || application.full_name || 'N/A' }}</td>
                                <td class="text-slate-600">{{ application.pet?.name || 'N/A' }} ({{ application.pet?.species || 'N/A' }})</td>
                                <td>
                                    <div v-if="getDocs(application.document_path).length" class="docs-stack">
                                        <a
                                            v-for="(path, idx) in getDocs(application.document_path)"
                                            :key="idx"
                                            :href="`/storage/${path}`"
                                            class="doc-link"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            Doc {{ idx + 1 }}
                                        </a>
                                    </div>
                                    <span v-else class="text-slate-400">None</span>
                                </td>
                                <td class="text-slate-500">{{ formatDate(application.created_at) }}</td>
                                <td class="text-center">
                                    <span class="status-pill" :class="statusPillClass(application.status)">
                                        {{ application.status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="action-cell">
                                        <Link :href="`/admin/adoptions/${application.id}`" class="view-link">View</Link>

                                        <template v-if="application.status === 'Pending Review' || application.status === 'Pending'">
                                            <span class="state-pill">Pending</span>
                                        </template>
                                        <template v-else-if="application.status === 'Approved'">
                                            <span class="state-pill state-pill--muted">Finalized</span>
                                        </template>
                                        <template v-else>
                                            <span class="state-pill state-pill--muted">{{ application.status }}</span>
                                        </template>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="applications.data.length === 0">
                                <td colspan="7" class="empty-cell">No adoption applications found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <PaginationLinks :links="applications.links" />
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PaginationLinks from '@/components/common/PaginationLinks.vue';

defineProps({
    applications: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

function formatDate(value) {
    if (!value) return 'N/A';
    return String(value).slice(0, 10);
}

function getDocs(value) {
    if (!value) return [];
    try {
        const parsed = typeof value === 'string' ? JSON.parse(value) : value;
        if (Array.isArray(parsed)) return parsed;
        if (typeof parsed === 'string') return [parsed];
        return [];
    } catch {
        return [value];
    }
}

function statusPillClass(status) {
    if (status === 'Pending Review' || status === 'Pending') return 'bg-yellow-100 text-yellow-800';
    if (status === 'Approved') return 'bg-green-100 text-green-800';
    return 'bg-red-100 text-red-800';
}
</script>

<style scoped>
.adoptions-page {
    max-width: 1240px;
    display: grid;
    gap: 14px;
}

.adoptions-header h1 {
    margin: 0;
    font-size: 2rem;
    line-height: 1.15;
    font-weight: 800;
    color: #0f172a;
}

.adoptions-header p {
    margin: 8px 0 0;
    font-size: 1.04rem;
    color: #64748b;
}

.flash-wrap {
    display: grid;
    gap: 10px;
}

.flash {
    border-radius: 12px;
    padding: 10px 13px;
    border: 1px solid transparent;
}

.flash-success {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #166534;
}

.flash-error {
    background: #fef2f2;
    border-color: #fecaca;
    color: #b91c1c;
}

.table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
    overflow: hidden;
}

.table-scroll {
    overflow-x: auto;
}

.adoptions-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    min-width: 980px;
}

.adoptions-table thead {
    background: #f8fafc;
}

.adoptions-table th,
.adoptions-table td {
    padding: 12px 14px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: top;
}

.adoptions-table th {
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #64748b;
}

.adoptions-table td {
    font-size: 0.94rem;
    color: #334155;
}

.docs-stack {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.doc-link {
    color: #2563eb;
    text-decoration: none;
    font-weight: 600;
}

.doc-link:hover {
    color: #1d4ed8;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 0.25rem 0.65rem;
    font-size: 0.78rem;
    font-weight: 700;
}

.action-cell {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: center;
}

.view-link {
    min-height: 34px;
    padding: 0 12px;
    border-radius: 10px;
    border: 1px solid #d1d5db;
    background: #fff;
    color: #334155;
    text-decoration: none;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.84rem;
    transition: all 0.18s ease;
}

.view-link:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    color: #0f172a;
}

.state-pill {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 0.22rem 0.52rem;
    font-size: 0.74rem;
    font-weight: 700;
    background: #fef3c7;
    color: #92400e;
}

.state-pill--muted {
    background: #e2e8f0;
    color: #475569;
}

.empty-cell {
    text-align: center !important;
    color: #64748b;
    padding: 24px !important;
}

.table-footer {
    padding: 12px 14px;
}
</style>
