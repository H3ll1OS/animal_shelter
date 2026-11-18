<template>
    <div class="sa-shell">
        <aside class="sa-sidebar" :class="{ 'sa-sidebar--open': menuOpen }">
            <Link href="/superadmin" class="sa-brand">
                <span class="sa-brand-mark" aria-hidden="true">AS</span>
                <span><strong>System Console</strong><small>Animal Shelter</small></span>
            </Link>

            <nav class="sa-nav" aria-label="Super Admin navigation">
                <p class="sa-nav-label">System administration</p>
                <Link v-for="item in systemLinks" :key="item.href" :href="item.href" class="sa-nav-link" :class="{ 'is-active': isActive(item.href) }">
                    <span class="sa-nav-icon" aria-hidden="true">{{ item.icon }}</span>{{ item.label }}
                </Link>
                <p class="sa-nav-label">Administration</p>
                <Link v-for="item in administrationLinks" :key="item.href" :href="item.href" class="sa-nav-link" :class="{ 'is-active': isActive(item.href) }">
                    <span class="sa-nav-icon" aria-hidden="true">{{ item.icon }}</span>{{ item.label }}
                </Link>
                <button type="button" class="sa-nav-link sa-logout" @click="logoutDialogOpen = true">
                    <span class="sa-nav-icon" aria-hidden="true">↪</span>Logout
                </button>
            </nav>
            <div class="sa-sidebar-foot">Restricted system administration</div>
        </aside>

        <div v-if="menuOpen" class="sa-backdrop" @click="menuOpen = false"></div>
        <div v-if="logoutDialogOpen" class="sa-dialog-backdrop" @click.self="logoutDialogOpen = false">
            <section class="sa-dialog" role="dialog" aria-modal="true" aria-labelledby="sa-logout-title" aria-describedby="sa-logout-description" @keydown.esc.stop="logoutDialogOpen = false">
                <h2 id="sa-logout-title">Log out?</h2>
                <p id="sa-logout-description">You can sign back in at any time.</p>
                <div class="sa-dialog-actions">
                    <button type="button" class="sa-button" autofocus @click="logoutDialogOpen = false">Cancel</button>
                    <Link href="/logout" method="post" as="button" class="sa-button sa-button--primary">Log out</Link>
                </div>
            </section>
        </div>
        <div class="sa-main">
            <header class="sa-topbar">
                <button type="button" class="sa-menu-toggle" aria-label="Toggle navigation" @click="menuOpen = !menuOpen">☰</button>
                <div class="sa-topbar-title">
                    <span>Super Admin</span>
                    <strong>{{ title }}</strong>
                </div>
                <div class="sa-user">
                    <span class="sa-user-avatar">{{ initials }}</span>
                    <span><strong>{{ user?.name || 'Super Admin' }}</strong><small>{{ user?.email }}</small></span>
                </div>
            </header>
            <main class="sa-content">
                <div v-if="flash.success" class="sa-alert sa-alert--success" role="status">{{ flash.success }}</div>
                <div v-if="flash.error" class="sa-alert sa-alert--error" role="alert">{{ flash.error }}</div>
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const flash = computed(() => page.props.flash ?? {});
const path = computed(() => (page.url || '/superadmin').split('?')[0]);
const menuOpen = ref(false);
const logoutDialogOpen = ref(false);
const initials = computed(() => (user.value?.name || 'SA').trim().split(/\s+/).slice(0, 2).map((part) => part[0]?.toUpperCase()).join(''));

const systemLinks = [
    { href: '/superadmin', label: 'Overview', icon: '⌂' },
    { href: '/superadmin/accounts', label: 'Account Management', icon: '◎' },
];
const administrationLinks = [
    { href: '/superadmin/access-management', label: 'Role and Access Management', icon: '◇' },
    { href: '/superadmin/activity-logs', label: 'Activity Logs', icon: '◷' },
];
const title = computed(() => {
    if (path.value === '/superadmin') return 'Overview';
    if (path.value.startsWith('/superadmin/accounts')) return 'Account Management';
    if (path.value.startsWith('/superadmin/access-management')) return 'Role and Access Management';
    if (path.value.startsWith('/superadmin/activity-logs')) return 'Activity Logs';
    return 'System Administration';
});

function isActive(href) {
    return href === '/superadmin' ? path.value === href : path.value.startsWith(href);
}
</script>

<style scoped>
.sa-shell{--navy:#101c35;--navy-soft:#1b2a46;--orange:#f97316;--ink:#172033;--muted:#64748b;--line:#e2e8f0;min-height:100vh;background:#f5f7fa;color:var(--ink);font-family:inherit}
.sa-sidebar{position:fixed;inset:0 auto 0 0;z-index:40;display:flex;width:264px;flex-direction:column;background:linear-gradient(180deg,#101c35 0%,#14213b 100%);color:#e2e8f0;box-shadow:4px 0 18px rgba(15,23,42,.08)}
.sa-brand{display:flex;align-items:center;gap:12px;padding:19px 20px;border-bottom:1px solid rgba(255,255,255,.09);color:white;text-decoration:none}
.sa-brand-mark{display:grid;width:40px;height:40px;place-items:center;border:1px solid rgba(255,255,255,.18);border-radius:11px;background:var(--orange);font-size:13px;font-weight:900;color:#fff;box-shadow:0 4px 10px rgba(249,115,22,.22)}
.sa-brand strong,.sa-brand small,.sa-user strong,.sa-user small{display:block}
.sa-brand strong{font-size:14px;letter-spacing:.01em}.sa-brand small{margin-top:3px;color:#aab5c7;font-size:12px}
.sa-nav{flex:1;padding:14px 12px;overflow:hidden}
.sa-nav-label{margin:21px 10px 8px;color:#93a1b6;font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.sa-nav-label:first-child{margin-top:5px}
.sa-nav-link{display:flex;width:100%;min-height:42px;align-items:center;gap:11px;margin:3px 0;padding:0 12px;border:0;border-radius:9px;background:transparent;color:#cbd5e1;text-align:left;text-decoration:none;font-family:inherit;font-size:13px;font-weight:600;line-height:1.2;cursor:pointer;transition:background .15s,color .15s,transform .15s}
.sa-nav-link:hover{transform:translateX(2px);background:rgba(255,255,255,.075);color:#fff}.sa-nav-link.is-active{background:rgba(249,115,22,.16);color:#fff;box-shadow:inset 3px 0 0 var(--orange)}
.sa-nav-link.is-active .sa-nav-icon{color:#fb923c}
.sa-nav-icon{display:inline-grid;width:20px;flex:0 0 20px;place-items:center;color:#97a6bc;font-size:17px;font-weight:700}
.sa-logout{color:#f5c5b2}.sa-sidebar-foot{padding:14px 20px;border-top:1px solid rgba(255,255,255,.09);color:#9ba8bb;font-size:11px}
.sa-main{min-height:100vh;margin-left:264px}.sa-topbar{position:sticky;top:0;z-index:20;display:flex;min-height:70px;align-items:center;justify-content:space-between;gap:16px;padding:0 32px;border-bottom:1px solid rgba(226,232,240,.9);background:rgba(255,255,255,.96);backdrop-filter:blur(12px)}
.sa-topbar-title span{display:block;color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.sa-topbar-title strong{display:block;margin-top:3px;color:#172033;font-size:15px}
.sa-user{display:flex;align-items:center;gap:10px;padding:6px 10px 6px 6px;border:1px solid #edf0f4;border-radius:999px;background:#fff}.sa-user-avatar{display:grid;width:34px;height:34px;place-items:center;border:1px solid #fed7aa;border-radius:50%;background:#fff7ed;color:#c2410c;font-size:12px;font-weight:800}
.sa-user strong{font-size:12px}.sa-user small{margin-top:2px;color:var(--muted);font-size:11px}
.sa-content{max-width:1480px;margin:0 auto;padding:30px 34px 48px}.sa-alert{margin-bottom:18px;padding:13px 16px;border:1px solid;border-radius:10px;font-size:13px}.sa-alert--success{border-color:#bbf7d0;background:#f0fdf4;color:#166534}.sa-alert--error{border-color:#fecaca;background:#fef2f2;color:#991b1b}
.sa-menu-toggle,.sa-backdrop{display:none}
.sa-dialog-backdrop{position:fixed;inset:0;z-index:80;display:grid;place-items:center;padding:20px;background:rgba(15,23,42,.42)}
.sa-dialog{width:min(100%,380px);padding:24px;border:1px solid #e7ebf1;border-radius:12px;background:#fff;box-shadow:0 16px 48px rgba(15,23,42,.18)}
.sa-dialog h2{margin:0;color:#172033;font-size:18px;letter-spacing:-.02em}
.sa-dialog p{margin:7px 0 0;color:#64748b;font-size:13px;line-height:1.5}
.sa-dialog-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:20px}
@media(max-width:420px){.sa-dialog{padding:20px}.sa-dialog-actions{flex-direction:row}.sa-dialog-actions .sa-button{flex:1}}
@media(max-width:900px){.sa-sidebar{width:238px}.sa-main{margin-left:238px}.sa-topbar{padding:0 22px}.sa-content{padding:24px 22px 38px}}
@media(max-width:680px){.sa-sidebar{width:min(84vw,300px);transform:translateX(-102%);transition:transform .2s ease}.sa-sidebar--open{transform:translateX(0)}.sa-main{margin-left:0}.sa-topbar{min-height:64px;padding:0 14px}.sa-menu-toggle{display:inline-grid;width:38px;height:38px;place-items:center;border:1px solid var(--line);border-radius:10px;background:#fff;color:var(--navy);font-size:18px}.sa-topbar-title{margin-right:auto}.sa-user{padding:4px}.sa-user span:last-child{display:none}.sa-content{padding:20px 14px 32px}.sa-backdrop{position:fixed;inset:0;z-index:30;display:block;background:rgba(15,23,42,.48)}}
@media(max-height:650px){.sa-brand{padding:12px 16px}.sa-nav{padding:8px 10px}.sa-nav-label{margin:10px 8px 4px;font-size:9px}.sa-nav-link{min-height:34px;margin:1px 0;font-size:12px}.sa-sidebar-foot{padding:9px 16px}}
@media(max-height:450px){.sa-brand{padding:8px 12px}.sa-brand-mark{width:32px;height:32px}.sa-nav{padding:5px 8px}.sa-nav-label{margin:5px 8px 2px;font-size:8px}.sa-nav-link{min-height:27px;font-size:11px}.sa-sidebar-foot{padding:6px 12px;font-size:9px}}
</style>
