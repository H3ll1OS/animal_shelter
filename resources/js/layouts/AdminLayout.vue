<template>
    <div id="app-container" class="min-h-screen bg-slate-100" data-vue-admin="true">
        <aside
            id="sidebar"
            class="fixed inset-y-0 left-0 transform transition-transform duration-300 ease-in-out w-64 bg-slate-900 border-r border-slate-800 shadow-xl overflow-y-auto sidebar z-50"
            :class="sidebarClasses"
        >
            <div class="brand-row px-4 py-4 flex items-center border-b border-slate-800">
                <span class="brand-icon inline-flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500 text-white text-lg flex-shrink-0">&#128062;</span>
                <div v-if="showNavText" class="brand-meta">
                    <p class="brand-title text-white">Admin Panel</p>
                    <p class="brand-subtitle text-slate-400">Animal Shelter</p>
                </div>
            </div>

            <ul class="admin-nav-list space-y-1 p-3">
                <li>
                    <Link href="/admin" class="admin-nav-link" :class="navClass('/admin')">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span v-if="showNavText">Dashboard</span>
                    </Link>
                </li>

                <li v-if="showNavText" class="admin-section-label">Adoption & Inventory</li>
                <li>
                    <Link href="/admin/pets" class="admin-nav-link" :class="navClass('/admin/pets')">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span v-if="showNavText">Manage Pets</span>
                    </Link>
                </li>
                <li>
                    <Link href="/admin/adoptions" class="admin-nav-link" :class="navClass('/admin/adoptions')">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span v-if="showNavText">Adoption Applications</span>
                    </Link>
                </li>

                <li v-if="showNavText" class="admin-section-label">Health Records</li>
                <li>
                    <Link href="/admin/health/monitoring" class="admin-nav-link" :class="navClass('/admin/health/monitoring')">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l2-2 2 2v13M9 19h6M9 19c0 1.105-.895 2-2 2H5c-1.105 0-2-.895-2-2v-3m18 3v-3c0-1.105-.895-2-2-2h-2c-1.105 0-2 .895-2 2v3"></path>
                        </svg>
                        <span v-if="showNavText">Health Monitoring</span>
                    </Link>
                </li>
                <li>
                    <Link href="/admin/health/vaccination-schedule" class="admin-nav-link" :class="navClass('/admin/health/vaccination-schedule')">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        <span v-if="showNavText">Vaccination Schedule</span>
                    </Link>
                </li>
                <li>
                    <Link href="/admin/health/euthanasia" class="admin-nav-link" :class="navClass('/admin/health/euthanasia')">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span v-if="showNavText">Euthanasia Logs</span>
                    </Link>
                </li>

                <li v-if="showNavText" class="admin-section-label">Finance</li>
                <li>
                    <Link href="/admin/donations" class="admin-nav-link" :class="navClass('/admin/donations')">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2v2H7a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2h-1v-2c0-1.105-1.343-2-3-2zM9 16l3-3 3 3"></path>
                        </svg>
                        <span v-if="showNavText">Donations</span>
                    </Link>
                </li>

                <li v-if="isDeveloper && showNavText" class="admin-section-label">Developer</li>
                <li v-if="isDeveloper">
                    <Link href="/developer/users" class="admin-nav-link" :class="navClass('/developer/users')">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        <span v-if="showNavText">User Management</span>
                    </Link>
                </li>
            </ul>
        </aside>

        <div id="main-content" class="flex-1 transition-all duration-300 ease-in-out main-content" :style="mainStyle">
            <header class="bg-white/95 backdrop-blur border-b border-slate-200 h-20 flex items-center justify-between px-4 lg:px-8 sticky top-0 z-40 shadow-sm">
                <div class="flex items-center gap-4">
                    <button id="sidebar-toggle" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none transition" @click.stop="toggleSidebar">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Administration</p>
                        <h1 class="text-2xl font-bold text-slate-800 leading-tight">{{ pageTitle }}</h1>
                    </div>
                </div>

                <div class="admin-header-actions relative">
                    <button id="notification-toggle" class="p-2 rounded-full text-slate-600 hover:bg-slate-100 transition relative" @click.stop="toggleNotif">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.405L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </button>
                    <div id="notification-panel" class="absolute right-0 top-12 w-80 bg-white border border-gray-200 rounded-lg shadow-lg z-50" :class="{ hidden: !showNotif }">
                        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-800">Notifications</p>
                            <span class="text-xs text-gray-500">0 new</span>
                        </div>
                        <div id="notification-list" class="p-4 space-y-3 text-sm text-gray-700 max-h-64 overflow-y-auto">
                            <p class="text-gray-600">No new notifications.</p>
                        </div>
                    </div>

                    <Link href="/" class="view-site-link">View Site</Link>
                    <div class="relative">
                        <button id="profile-toggle" class="profile-avatar-btn" @click.stop="toggleProfile">
                            <img class="h-10 w-10 rounded-full object-cover" src="https://placehold.co/100x100/4f46e5/ffffff?text=AD" alt="Admin Avatar">
                        </button>
                        <div id="profile-panel" class="absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-lg shadow-lg z-50" :class="{ hidden: !showProfile }">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <p class="text-sm font-semibold text-gray-800">Account</p>
                                <p class="text-xs text-gray-500">{{ user?.name || 'Admin' }}</p>
                            </div>
                            <div class="p-2">
                                <Link href="/logout" method="post" as="button" class="w-full text-left px-3 py-2 text-sm font-semibold text-red-600 rounded-md hover:bg-red-50 transition">
                                    Log Out
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="admin-main">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const currentPath = computed(() => (page.url || '/').split('?')[0]);
const isDeveloper = computed(() => user.value?.role === 'developer' || user.value?.is_developer === true);

const showNotif = ref(false);
const showProfile = ref(false);
const isMobile = ref(false);
const isCollapsed = ref(false);
const isSidebarOpenMobile = ref(false);

const pageTitle = computed(() => {
    if (currentPath.value.startsWith('/admin/pets')) return 'Pet Management';
    if (currentPath.value.startsWith('/admin/adoptions')) return 'Adoption Applications';
    if (currentPath.value.startsWith('/admin/donations')) return 'Donation History';
    if (currentPath.value.startsWith('/admin/health/monitoring')) return 'Health Alerts';
    if (currentPath.value.startsWith('/admin/health/vaccination-schedule')) return 'Vaccination Schedule';
    if (currentPath.value.startsWith('/admin/health/euthanasia')) return 'Euthanasia Records';
    if (currentPath.value.startsWith('/developer/users')) return 'System User Management';
    return 'Dashboard Overview';
});

const sidebarClasses = computed(() => ({
    'translate-x-0': !isMobile.value || isSidebarOpenMobile.value,
    '-translate-x-full': isMobile.value && !isSidebarOpenMobile.value,
    collapsed: !isMobile.value && isCollapsed.value,
    open: isMobile.value && isSidebarOpenMobile.value,
}));

const showNavText = computed(() => !(!isMobile.value && isCollapsed.value));

const mainStyle = computed(() => {
    if (isMobile.value) {
        return { marginLeft: '0', width: '100%' };
    }

    if (isCollapsed.value) {
        return { marginLeft: '5rem', width: 'calc(100% - 5rem)' };
    }

    return { marginLeft: '16rem', width: 'calc(100% - 16rem)' };
});

function isActive(path) {
    if (path === '/admin') return currentPath.value === '/admin';
    return currentPath.value === path || currentPath.value.startsWith(`${path}/`);
}

function navClass(path) {
    if (isActive(path)) {
        return 'bg-orange-500 text-white shadow-sm';
    }
    return 'text-slate-200 hover:bg-slate-800 hover:text-white';
}

function updateViewportState() {
    isMobile.value = window.innerWidth < 1024;
    if (!isMobile.value) {
        isSidebarOpenMobile.value = false;
    }
}

function toggleSidebar() {
    if (isMobile.value) {
        isSidebarOpenMobile.value = !isSidebarOpenMobile.value;
        return;
    }

    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem('adminSidebarCollapsed', isCollapsed.value ? '1' : '0');
}

function toggleNotif() {
    showNotif.value = !showNotif.value;
    if (showNotif.value) showProfile.value = false;
}

function toggleProfile() {
    showProfile.value = !showProfile.value;
    if (showProfile.value) showNotif.value = false;
}

function onDocClick(event) {
    const notifPanel = document.getElementById('notification-panel');
    const notifToggle = document.getElementById('notification-toggle');
    const profilePanel = document.getElementById('profile-panel');
    const profileToggle = document.getElementById('profile-toggle');
    const sidebarEl = document.getElementById('sidebar');
    const sidebarToggleEl = document.getElementById('sidebar-toggle');

    if (showNotif.value && notifPanel && notifToggle && !notifPanel.contains(event.target) && !notifToggle.contains(event.target)) {
        showNotif.value = false;
    }

    if (showProfile.value && profilePanel && profileToggle && !profilePanel.contains(event.target) && !profileToggle.contains(event.target)) {
        showProfile.value = false;
    }

    if (
        isMobile.value &&
        isSidebarOpenMobile.value &&
        sidebarEl &&
        sidebarToggleEl &&
        !sidebarEl.contains(event.target) &&
        !sidebarToggleEl.contains(event.target)
    ) {
        isSidebarOpenMobile.value = false;
    }
}

onMounted(() => {
    isCollapsed.value = localStorage.getItem('adminSidebarCollapsed') === '1';
    updateViewportState();
    window.addEventListener('resize', updateViewportState);
    document.addEventListener('click', onDocClick);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateViewportState);
    document.removeEventListener('click', onDocClick);
});
</script>

<style scoped>
.admin-nav-link {
    display: flex;
    align-items: center;
    width: 100%;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.95rem;
    font-weight: 600;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.brand-row {
    min-height: 74px;
    gap: 10px;
}

.brand-title {
    margin: 0;
    font-size: 2rem;
    line-height: 1;
    font-weight: 800;
    letter-spacing: -0.01em;
}

.brand-subtitle {
    margin: 3px 0 0;
    font-size: 0.8rem;
    line-height: 1.1;
    font-weight: 500;
}

.admin-section-label {
    padding: 10px 12px 4px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #94a3b8;
}

.sidebar.collapsed {
    width: 4.75rem !important;
}

.sidebar.collapsed .brand-meta {
    display: none;
}

.sidebar.collapsed .brand-row {
    justify-content: center;
    padding: 0.75rem 0.5rem;
}

.sidebar.collapsed .brand-icon {
    margin-right: 0;
    height: 2.25rem;
    width: 2.25rem;
}

.sidebar.collapsed .admin-section-label {
    display: none;
}

.sidebar.collapsed .admin-nav-list {
    padding: 0.65rem 0.4rem;
}

.sidebar.collapsed .admin-nav-list li {
    display: flex;
    justify-content: center;
}

.sidebar.collapsed .admin-nav-link {
    justify-content: center;
    width: 2.9rem;
    height: 2.9rem;
    padding: 0;
    border-radius: 12px;
    font-size: 0;
    margin: 0 auto;
}

.sidebar.collapsed .admin-nav-link svg {
    margin-right: 0 !important;
    width: 1.25rem;
    height: 1.25rem;
}

.admin-header-actions {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    min-width: max-content;
    white-space: nowrap;
    padding-right: 0.6rem;
}

.view-site-link {
    display: inline-flex;
    align-items: center;
    font-size: 0.95rem;
    font-weight: 700;
    color: #475569;
    text-decoration: none;
    padding: 0.35rem 0.1rem;
    transition: color 0.2s ease;
}

.view-site-link:hover {
    color: #ea580c;
}

.profile-avatar-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    margin-left: 0.35rem;
    border-radius: 9999px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    outline: none;
}

.admin-main {
    padding: 1rem;
}

:deep(.admin-page-container) {
    width: min(100%, 1240px);
    margin-inline: auto;
}

@media (min-width: 640px) {
    .admin-main {
        padding: 1.25rem;
    }
}

@media (min-width: 1024px) {
    .admin-main {
        padding: 1.5rem;
    }
}

@media (max-width: 640px) {
    .admin-header-actions {
        gap: 0.45rem;
        padding-right: 0.75rem;
    }

    .view-site-link {
        font-size: 0.86rem;
    }
}
</style>
