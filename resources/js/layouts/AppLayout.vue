<template>
    <div>
        <header class="main-header">
            <nav class="navbar">
                <div class="nav-container">
                    <div class="nav-logo">
                        <Link href="/" class="nav-logo-link">
                            <span class="nav-brand-mark" aria-hidden="true">&#128062;</span>
                            <span>Animal Shelter</span>
                        </Link>
                    </div>

                    <ul class="nav-menu primary-nav" :class="{ 'is-open': isMobileMenuOpen }">
                        <li><Link href="/" class="nav-link" :class="{ 'active-nav': isActive('/') }">Home</Link></li>
                        <li><Link href="/about" class="nav-link" :class="{ 'active-nav': isActive('/about') }">About</Link></li>
                        <li><Link href="/adopt" class="nav-link" :class="{ 'active-nav': isActive('/adopt') }">Adopt</Link></li>
                        <li>
                            <Link
                                href="/donate"
                                class="nav-link btn-cta"
                                :class="{ 'active-nav': isActive('/donate') }"
                            >
                                Donate
                            </Link>
                        </li>
                        <li><Link href="/contact" class="nav-link" :class="{ 'active-nav': isActive('/contact') }">Contact</Link></li>
                    </ul>

                    <ul class="nav-menu nav-utility" :class="{ 'is-open': isMobileMenuOpen }">
                        <li v-if="!user"><Link href="/login" class="nav-link utility-link" :class="{ 'active-nav': isActive('/login') }">Login</Link></li>
                        <li v-if="!user"><Link href="/register" class="nav-link utility-link btn-register" :class="{ 'active-nav': isActive('/register') }">Register</Link></li>
                        <li v-else class="profile-menu" ref="profileMenuRef">
                            <button
                                type="button"
                                class="profile-toggle"
                                :aria-expanded="isProfileMenuOpen ? 'true' : 'false'"
                                aria-haspopup="true"
                                @click="toggleProfileMenu"
                            >
                                <img v-if="avatarUrl" class="profile-avatar" :src="avatarUrl" alt="Profile picture">
                                <span v-else class="profile-fallback">{{ userInitials }}</span>
                            </button>

                            <div class="profile-dropdown" :class="{ hidden: !isProfileMenuOpen }" role="menu">
                                <Link href="/account/settings" class="profile-dropdown-item" role="menuitem">Account Settings</Link>
                                <Link href="/logout" method="post" as="button" class="profile-dropdown-item profile-logout" role="menuitem">Logout</Link>
                            </div>
                        </li>
                    </ul>

                    <button type="button" class="hamburger" @click="isMobileMenuOpen = !isMobileMenuOpen">
                        <span class="bar"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                    </button>
                </div>
            </nav>
        </header>

        <main>
            <slot />
        </main>

        <footer class="main-footer">
            <div class="container footer-content-grid">
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><Link href="/">Home</Link></li>
                        <li><Link href="/about">About Us</Link></li>
                        <li><Link href="/adopt">View Animals</Link></li>
                        <li><Link href="/contact">Contact & FAQ</Link></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Get In Touch</h3>
                    <p class="contact-detail">Location: 123 Navalca Street, Surigao City, 8400</p>
                    <p class="contact-detail">Phone: (555) 555-CATS</p>
                    <p class="contact-detail">Email: info@animalshelter.org</p>
                </div>
                <div class="footer-section mission-cta">
                    <h3>Help Our Mission</h3>
                    <p>Every adoption and donation helps us rescue, rehabilitate, and rehome animals in need.</p>
                    <Link href="/donate" class="btn-footer-cta">Donate Now</Link>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="container">&copy; {{ currentYear }} Animal Shelter. All Rights Reserved.</div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const isMobileMenuOpen = ref(false);
const isProfileMenuOpen = ref(false);
const profileMenuRef = ref(null);
const user = computed(() => page.props.auth?.user ?? null);
const currentYear = new Date().getFullYear();
const currentPath = computed(() => (page.url || '/').split('?')[0]);
const avatarUrl = computed(() => {
    if (!user.value?.profile_image) return null;
    const version = user.value?.updated_at ? `?v=${encodeURIComponent(user.value.updated_at)}` : '';
    return `/storage/${user.value.profile_image}${version}`;
});
const userInitials = computed(() => {
    const name = (user.value?.name || '').trim();
    if (!name) return 'U';
    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});

function isActive(path) {
    if (path === '/') {
        return currentPath.value === '/';
    }

    return currentPath.value === path || currentPath.value.startsWith(`${path}/`);
}

function toggleProfileMenu() {
    isProfileMenuOpen.value = !isProfileMenuOpen.value;
}

function handleClickOutside(event) {
    if (!profileMenuRef.value) return;
    if (!profileMenuRef.value.contains(event.target)) {
        isProfileMenuOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.profile-fallback {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 700;
    color: #2d3748;
    background: #ffe8dc;
}

@media (max-width: 992px) {
    .primary-nav.is-open,
    .nav-utility.is-open {
        display: flex;
        flex-direction: column;
        position: absolute;
        left: 0;
        right: 0;
        background: #fff;
        padding: 12px 24px;
        z-index: 1002;
    }

    .primary-nav.is-open {
        top: 70px;
        gap: 12px;
    }

    .nav-utility.is-open {
        top: 250px;
        gap: 10px;
    }
}
</style>

