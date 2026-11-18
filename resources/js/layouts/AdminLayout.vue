<template>
    <div id="app-container" class="min-h-screen bg-slate-100" data-vue-admin="true">
        <aside
            id="sidebar"
            class="fixed inset-y-0 left-0 h-screen transform transition-transform duration-300 ease-in-out w-64 overflow-x-hidden overflow-y-hidden bg-slate-900 border-r border-slate-800 shadow-xl sidebar z-50"
            :class="sidebarClasses"
        >
            <div class="brand-row flex items-center border-b border-slate-800">
                <span class="brand-icon inline-flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500 text-white text-lg flex-shrink-0">&#128062;</span>
                <div v-if="showNavText" class="brand-meta">
                    <p class="brand-title text-white">Admin Panel</p>
                    <p class="brand-subtitle text-slate-400">Animal Shelter</p>
                </div>
            </div>

            <ul class="admin-nav-list">
                <li v-if="canAccess('dashboard', 'view')">
                    <Link href="/admin" class="admin-nav-link" :class="navClass('/admin')" title="Dashboard">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span v-if="showNavText">Dashboard</span>
                    </Link>
                </li>

                <li v-if="showNavText && (canAccess('pets', 'view') || canAccess('adoptions', 'view'))" class="admin-section-label">Adoption & Inventory</li>
                <li v-if="canAccess('pets', 'view')">
                    <Link href="/admin/pets" class="admin-nav-link" :class="navClass('/admin/pets')" title="Manage Pets">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span v-if="showNavText">Manage Pets</span>
                    </Link>
                </li>
                <li v-if="canAccess('adoptions', 'view')">
                    <Link href="/admin/adoptions" class="admin-nav-link" :class="navClass('/admin/adoptions')" title="Adoption Applications">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span v-if="showNavText">Adoption Applications</span>
                    </Link>
                </li>

                <li v-if="showNavText && canAccess('post_adoption', 'view')" class="admin-section-label">Post-Adoption</li>
                <li v-if="canAccess('post_adoption', 'view')">
                    <Link href="/admin/post-adoption" class="admin-nav-link" :class="navClass('/admin/post-adoption')" title="Post-Adoption Monitoring">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span v-if="showNavText">Post-Adoption Monitoring</span>
                    </Link>
                </li>
                <li v-if="canAccess('post_adoption', 'view')">
                    <Link href="/admin/post-adoption/schedules" class="admin-nav-link admin-nav-link--nested" :class="navClass('/admin/post-adoption/schedules')" aria-label="Monitoring Schedules" title="Monitoring Schedules">
                        <svg class="mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3v3m8-3v3M4 9h16M5 5h14a1 1 0 011 1v13H4V6a1 1 0 011-1z" /></svg>
                        <span v-if="showNavText">Monitoring Schedules</span>
                    </Link>
                </li>
                <li v-if="canAccess('post_adoption', 'view')">
                    <Link href="/admin/post-adoption/history" class="admin-nav-link admin-nav-link--nested" :class="navClass('/admin/post-adoption/history')" aria-label="Monitoring History" title="Monitoring History">
                        <svg class="mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span v-if="showNavText">Monitoring History</span>
                    </Link>
                </li>

                <li v-if="showNavText && (canAccess('health', 'view') || canAccess('vaccinations', 'view') || canAccess('euthanasia', 'view'))" class="admin-section-label">Health Records</li>
                <li v-if="canAccess('health', 'view')">
                    <Link href="/admin/health/monitoring" class="admin-nav-link" :class="navClass('/admin/health/monitoring')" title="Health Monitoring">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l2-2 2 2v13M9 19h6M9 19c0 1.105-.895 2-2 2H5c-1.105 0-2-.895-2-2v-3m18 3v-3c0-1.105-.895-2-2-2h-2c-1.105 0-2 .895-2 2v3"></path>
                        </svg>
                        <span v-if="showNavText">Health Monitoring</span>
                    </Link>
                </li>
                <li v-if="canAccess('vaccinations', 'view')">
                    <Link href="/admin/health/vaccination-schedule" class="admin-nav-link" :class="navClass('/admin/health/vaccination-schedule')" title="Vaccination Schedule">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        <span v-if="showNavText">Vaccination Schedule</span>
                    </Link>
                </li>
                <li v-if="canAccess('euthanasia', 'view')">
                    <Link href="/admin/health/euthanasia" class="admin-nav-link" :class="navClass('/admin/health/euthanasia')" title="Euthanasia Logs">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span v-if="showNavText">Euthanasia Logs</span>
                    </Link>
                </li>

                <li v-if="showNavText && canAccess('donations', 'view')" class="admin-section-label">Finance</li>
                <li v-if="canAccess('donations', 'view')">
                    <Link href="/admin/donations" class="admin-nav-link" :class="navClass('/admin/donations')" title="Donations">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2v2H7a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2h-1v-2c0-1.105-1.343-2-3-2zM9 16l3-3 3 3"></path>
                        </svg>
                        <span v-if="showNavText">Donations</span>
                    </Link>
                </li>
                <li v-if="showNavText && canAccess('dashboard', 'view')" class="admin-section-label">Analytics</li>
                <li v-if="canAccess('dashboard', 'view')">
                    <Link href="/admin/reports" class="admin-nav-link" :class="navClass('/admin/reports')" title="Reports & Analytics">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3h10v18H3V11h8V3zm2 3v5h5V6h-5zM6 14v4h5v-4H6zm7 0v4h5v-4h-5z" />
                        </svg>
                        <span v-if="showNavText">Reports &amp; Analytics</span>
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
                        <span v-if="unreadCount" class="notif-badge">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
                    </button>
                    <div id="notification-panel" class="notif-panel" :class="{ hidden: !showNotif }">
                        <div class="notif-head">
                            <p class="notif-title">Notifications</p>
                            <div class="notif-head-actions">
                                <span class="notif-count">{{ unreadCount }} new</span>
                                <Link
                                    v-if="unreadCount"
                                    href="/admin/notifications/clear"
                                    method="post"
                                    as="button"
                                    class="notif-clear-btn"
                                >
                                    Clear
                                </Link>
                            </div>
                        </div>
                        <div id="notification-list" class="notif-list">
                            <template v-if="notifications.length">
                                <Link
                                    v-for="notification in notifications"
                                    :key="notification.id"
                                    :href="notification.href"
                                    class="notif-item"
                                    :class="`notif-item--${notification.kind}`"
                                >
                                    <span class="notif-item-icon" aria-hidden="true">{{ notification.kind === 'donation' ? '$' : '@' }}</span>
                                    <span class="notif-item-copy">
                                        <span class="notif-item-kind">{{ notification.kind === 'donation' ? 'Donation' : 'Adoption' }}</span>
                                        <span class="notif-item-message">{{ notification.message }}</span>
                                        <span class="notif-item-time">{{ notification.time_label || 'Just now' }}</span>
                                    </span>
                                </Link>
                            </template>
                            <p v-else class="notif-empty">No new user activity right now.</p>
                        </div>
                    </div>

                    <Link href="/" class="view-site-link">View Site</Link>
                    <div class="relative">
                        <button id="profile-toggle" class="profile-avatar-btn" @click.stop="toggleProfile">
                            <img v-if="avatarUrl" class="h-10 w-10 rounded-full object-cover" :src="avatarUrl" alt="Profile picture">
                            <span v-else class="profile-avatar-fallback">{{ userInitials }}</span>
                        </button>
                        <div id="profile-panel" class="profile-menu-panel" :class="{ hidden: !showProfile }">
                            <div class="profile-menu-head">
                                <p class="profile-menu-label">Account</p>
                                <p class="profile-menu-name">{{ user?.name || 'Admin' }}</p>
                            </div>
                            <div class="profile-menu-actions">
                                <Link href="/logout" method="post" as="button" class="profile-logout-btn">
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
const permissionKeys = computed(() => new Set(user.value?.permissions || []));
const currentPath = computed(() => (page.url || '/').split('?')[0]);
const notifications = computed(() => page.props.notifications?.admin_items || []);
const avatarUrl = computed(() => {
    if (!user.value?.profile_image) return null;
    const version = user.value?.updated_at ? `?v=${encodeURIComponent(user.value.updated_at)}` : '';
    return `/storage/${user.value.profile_image}${version}`;
});
const userInitials = computed(() => {
    const name = (user.value?.name || '').trim();
    if (!name) return 'AD';
    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});

const showNotif = ref(false);
const showProfile = ref(false);
const isMobile = ref(false);
const isCollapsed = ref(false);
const isSidebarOpenMobile = ref(false);
const unreadCount = computed(() => Number(page.props.notifications?.admin_count || 0));

const pageTitle = computed(() => {
    if (currentPath.value.startsWith('/admin/pets')) return 'Pet Management';
    if (currentPath.value.startsWith('/admin/adoptions')) return 'Adoption Applications';
    if (currentPath.value.startsWith('/admin/post-adoption')) return 'Post-Adoption Monitoring';
    if (currentPath.value.startsWith('/admin/donations')) return 'Donation History';
    if (currentPath.value.startsWith('/admin/health/monitoring')) return 'Health Alerts';
    if (currentPath.value.startsWith('/admin/health/vaccination-schedule')) return 'Vaccination Schedule';
    if (currentPath.value.startsWith('/admin/health/euthanasia')) return 'Euthanasia Records';
    return 'Dashboard Overview';
});

const sidebarClasses = computed(() => ({
    'translate-x-0': !isMobile.value || isSidebarOpenMobile.value,
    '-translate-x-full': isMobile.value && !isSidebarOpenMobile.value,
    collapsed: !isMobile.value && isCollapsed.value,
    open: isMobile.value && isSidebarOpenMobile.value,
}));

const showNavText = computed(() => !(!isMobile.value && isCollapsed.value));

function canAccess(module, action) {
    return permissionKeys.value.has(`${module}.${action}`);
}

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
    if (path === '/admin/post-adoption') return currentPath.value === path;
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
    if (showNotif.value) {
        showProfile.value = false;
    }
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
.sidebar {
    display: flex;
    height: 100vh;
    height: 100dvh;
    min-height: 0;
    flex-direction: column;
    overflow: hidden;
}

.admin-nav-link {
    display: flex;
    align-items: center;
    width: 100%;
    min-height: 2.25rem;
    border-radius: 0.55rem;
    padding: 6px 10px;
    font-size: 0.84rem;
    line-height: 1.2rem;
    font-weight: 600;
    white-space: nowrap;
    text-decoration: none;
    transition: background-color 150ms ease, color 150ms ease, box-shadow 150ms ease;
}

.admin-nav-link:focus-visible {
    outline: 2px solid #fdba74;
    outline-offset: 2px;
}

.admin-nav-link svg {
    flex: 0 0 1.25rem;
    width: 1.25rem;
    height: 1.25rem;
}

.admin-nav-link--nested {
    min-height: 2rem;
    padding-left: 1.65rem;
    font-size: 0.8rem;
    font-weight: 550;
}

.admin-nav-link--nested:not(.bg-orange-500) {
    color: #cbd5e1;
}

.admin-nav-link--nested:not(.bg-orange-500):hover {
    color: #fff;
    background: #1e293b;
}

.admin-nav-list {
    display: flex;
    min-height: 0;
    flex: 1 1 auto;
    flex-direction: column;
    gap: 1px;
    padding: 0.5rem 0.5rem 0.65rem;
}

.brand-row {
    min-height: 64px;
    flex: 0 0 auto;
    gap: 9px;
    align-items: center;
    padding: 0.55rem 0.75rem;
}

.brand-title {
    margin: 0;
    font-size: 1.65rem;
    line-height: 1.05;
    font-weight: 800;
    letter-spacing: -0.01em;
    white-space: nowrap;
}

.brand-subtitle {
    margin: 3px 0 0;
    font-size: 0.8rem;
    line-height: 1.1;
    font-weight: 500;
}

.brand-meta {
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.brand-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    font-size: 1.05rem;
    flex-shrink: 0;
}

.admin-section-label {
    margin-top: 0.25rem;
    padding: 0.3rem 0.65rem 0.12rem;
    font-size: 0.62rem;
    line-height: 0.9rem;
    font-weight: 700;
    letter-spacing: 0.075em;
    text-transform: uppercase;
    color: #94a3b8;
}

.sidebar.collapsed {
    width: 5rem !important;
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
    line-height: 1;
}

.sidebar.collapsed .admin-section-label {
    display: none;
}

.sidebar.collapsed .admin-nav-list {
    padding: 0.5rem 0.4rem;
}

.sidebar.collapsed .admin-nav-list li {
    display: flex;
    justify-content: center;
}

.sidebar.collapsed .admin-nav-link {
    justify-content: center;
    width: 2.9rem;
    height: 2.9rem;
    min-height: 2.9rem;
    padding: 0;
    border-radius: 12px;
    font-size: 0;
    margin: 0 auto;
}

.sidebar.collapsed .admin-nav-link--nested {
    padding: 0;
}

.sidebar.collapsed .admin-nav-link svg {
    margin-right: 0 !important;
    width: 1.25rem;
    height: 1.25rem;
}

@media (max-height: 720px) {
    .sidebar:not(.collapsed) .brand-title {
        font-size: 1.45rem;
    }

    .sidebar:not(.collapsed) .admin-nav-list {
        gap: 0;
    }
}

@media (max-height: 640px) {
    .brand-row {
        min-height: 56px;
        padding-block: 0.3rem;
    }

    .brand-title {
        font-size: 1.5rem;
    }

    .admin-nav-list {
        gap: 0;
        padding: 0.25rem 0.5rem;
    }

    .admin-nav-link {
        min-height: 1.9rem;
        padding-block: 3px;
        font-size: 0.82rem;
    }

    .admin-nav-link--nested {
        min-height: 1.75rem;
    }

    .admin-section-label {
        margin-top: 0.05rem;
        padding-top: 3px;
        font-size: 0.6rem;
    }

    .sidebar.collapsed .admin-nav-link {
        height: 2.5rem;
        min-height: 2.5rem;
    }
}

@media (max-height: 560px) {
    .brand-row {
        min-height: 48px;
        padding-block: 0.2rem;
    }

    .brand-title {
        font-size: 1.4rem;
    }

    .brand-subtitle {
        font-size: 0.72rem;
    }

    .admin-nav-list {
        padding-block: 0.25rem;
    }

    .admin-nav-link {
        min-height: 1.65rem;
        padding-block: 1px;
        font-size: 0.78rem;
        line-height: 1rem;
    }

    .admin-nav-link--nested {
        min-height: 1.55rem;
    }

    .admin-nav-link svg {
        width: 1.1rem;
        height: 1.1rem;
    }

    .admin-section-label {
        margin-top: 0;
        padding-block: 2px 0;
        font-size: 0.58rem;
        line-height: 0.75rem;
    }

    .sidebar.collapsed .admin-nav-link {
        height: 2.2rem;
        min-height: 2.2rem;
    }
}

@media (max-height: 440px) {
    .brand-row {
        min-height: 48px;
        padding-block: 0.2rem;
    }

    .brand-title {
        font-size: 1.3rem;
    }

    .brand-subtitle {
        margin-top: 1px;
        font-size: 0.68rem;
    }

    .admin-nav-list {
        padding-block: 0.15rem;
    }

    .admin-nav-link,
    .admin-nav-link--nested {
        min-height: 1.55rem;
        padding-block: 1px;
        font-size: 0.8rem;
        line-height: 1rem;
    }

    .admin-section-label {
        margin-top: 0;
        padding-block: 2px 0;
        font-size: 0.58rem;
        line-height: 0.75rem;
    }

    .sidebar.collapsed .admin-nav-link {
        height: 2rem;
        min-height: 2rem;
    }
}

@media (max-height: 380px) {
    .brand-row {
        min-height: 40px;
        padding-block: 0.15rem;
    }

    .brand-icon {
        width: 1.9rem;
        height: 1.9rem;
    }

    .admin-nav-list {
        padding-block: 0.1rem;
    }

    .admin-nav-link,
    .admin-nav-link--nested {
        min-height: 1.35rem;
        padding-block: 0;
        font-size: 0.7rem;
        line-height: 0.9rem;
    }

    .admin-nav-link svg {
        width: 1rem;
        height: 1rem;
    }

    .admin-section-label {
        padding-block: 1px 0;
        font-size: 0.52rem;
        line-height: 0.65rem;
    }

    .sidebar.collapsed .admin-nav-link {
        width: 2rem;
        height: 1.8rem;
        min-height: 1.8rem;
    }
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

.profile-avatar-fallback {
    width: 40px;
    height: 40px;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    color: #ffffff;
    font-size: 0.92rem;
    font-weight: 800;
    letter-spacing: 0.02em;
}

.notif-panel {
    position: absolute;
    right: 0;
    top: calc(100% + 0.55rem);
    z-index: 50;
    width: min(92vw, 360px);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 22px 40px rgba(15, 23, 42, 0.12);
    overflow: hidden;
}

.notif-head {
    min-height: 54px;
    padding: 0 16px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.notif-head-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.notif-title {
    margin: 0;
    font-size: 1rem;
    line-height: 1.2;
    font-weight: 800;
    color: #0f172a;
}

.notif-count {
    font-size: 0.78rem;
    line-height: 1.1;
    color: #64748b;
    font-weight: 700;
}

.notif-clear-btn {
    border: 0;
    background: transparent;
    padding: 0;
    color: #ea580c;
    font-size: 0.78rem;
    line-height: 1.1;
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
}

.notif-clear-btn:hover {
    color: #c2410c;
}

.notif-list {
    padding: 12px;
    max-height: 320px;
    overflow-y: auto;
    background: #f8fafc;
}

.notif-empty {
    margin: 0;
    padding: 12px 10px;
    font-size: 0.88rem;
    line-height: 1.4;
    color: #64748b;
    text-align: center;
}

.notif-item {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    text-align: left;
    padding: 12px 13px;
    transition: background-color 0.18s ease, border-color 0.18s ease;
    text-decoration: none;
}

.notif-item + .notif-item {
    margin-top: 10px;
}

.notif-item:hover {
    background: #ffffff;
    border-color: #cbd5e1;
}

.notif-item--adoption {
    border-left: 4px solid #93c5fd;
}

.notif-item--donation {
    border-left: 4px solid #86efac;
}

.notif-item-icon {
    width: 28px;
    height: 28px;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.84rem;
    font-weight: 800;
    color: #fff;
    background: #2563eb;
}

.notif-item--donation .notif-item-icon {
    background: #16a34a;
}

.notif-item-copy {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
    flex: 1;
}

.notif-item-kind {
    font-size: 0.68rem;
    line-height: 1.1;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #94a3b8;
    font-weight: 800;
}

.notif-item-message {
    font-size: 0.94rem;
    line-height: 1.3;
    color: #0f172a;
    font-weight: 700;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notif-item-time {
    font-size: 0.74rem;
    line-height: 1.2;
    color: #64748b;
    font-weight: 600;
}

.notif-badge {
    position: absolute;
    top: 0;
    right: -1px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: #dc2626;
    color: #fff;
    font-size: 0.68rem;
    line-height: 18px;
    font-weight: 800;
    text-align: center;
    box-shadow: 0 0 0 2px #fff;
}

.profile-menu-panel {
    position: absolute;
    right: 0;
    top: calc(100% + 0.55rem);
    z-index: 50;
    width: 208px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #ffffff;
    box-shadow: 0 18px 32px rgba(15, 23, 42, 0.14);
    overflow: hidden;
}

.profile-menu-head {
    padding: 12px 14px 10px;
    border-bottom: 1px solid #e2e8f0;
}

.profile-menu-label {
    margin: 0;
    font-size: 0.9rem;
    line-height: 1.2;
    font-weight: 700;
    color: #0f172a;
}

.profile-menu-name {
    margin: 4px 0 0;
    font-size: 0.8rem;
    line-height: 1.2;
    color: #64748b;
}

.profile-menu-actions {
    padding: 8px;
}

.profile-logout-btn {
    width: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: flex-start;
    min-height: 38px;
    padding: 0 12px;
    border: 0;
    border-radius: 8px;
    background: #fff;
    color: #dc2626;
    font-size: 0.9rem;
    font-weight: 700;
    text-align: left;
    transition: background-color 0.18s ease, color 0.18s ease;
}

.profile-logout-btn:hover {
    background: #fef2f2;
    color: #b91c1c;
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
