<template>
    <AppLayout>
        <section class="account-settings">
            <div class="container account-shell">
                <div class="account-header">
                    <div>
                        <span class="account-kicker">Profile</span>
                        <h1 class="account-title">Account Settings</h1>
                        <p class="account-intro">Keep your contact details up to date so we can reach you about adoptions and account activity.</p>
                    </div>
                    <div class="account-badge">
                        <span>Signed in as</span>
                        <strong>{{ user.email || 'Member' }}</strong>
                    </div>
                </div>

                <div class="account-layout">
                    <aside class="account-panel account-panel--hero">
                        <div class="account-avatar-wrap">
                            <div class="account-avatar-frame account-avatar-frame--editable">
                                <img v-if="avatarDisplayUrl" :src="avatarDisplayUrl" alt="Profile image">
                                <span v-else>{{ userInitials }}</span>
                                <button
                                    type="button"
                                    class="avatar-edit-btn"
                                    @click="openImagePicker"
                                    aria-label="Change profile image"
                                    title="Change photo"
                                >
                                    <span class="avatar-edit-icon">📷</span>
                                </button>
                            </div>
                        </div>

                        <div class="account-meta">
                            <h2>{{ form.name || 'Account Owner' }}</h2>
                            <p>{{ form.email || 'No email set' }}</p>
                        </div>

                        <div class="account-divider"></div>

                        <div class="account-quick">
                            <div>
                                <span class="account-label">Phone</span>
                                <strong>{{ form.phone || 'Not provided' }}</strong>
                            </div>
                            <div>
                                <span class="account-label">Date of birth</span>
                                <strong>{{ form.date_of_birth || 'Not provided' }}</strong>
                            </div>
                            <div>
                                <span class="account-label">Address</span>
                                <strong>{{ form.address || 'Not provided' }}</strong>
                            </div>
                        </div>
                    </aside>

                    <div class="account-panel account-panel--form">
                        <form @submit.prevent="submit" enctype="multipart/form-data" class="account-form">
                            <div class="account-field-grid">
                                <div>
                                    <label for="name">Full Name</label>
                                    <input id="name" v-model="form.name" type="text" class="form-control" required>
                                    <div v-if="form.errors.name" class="text-danger mt-1">{{ form.errors.name }}</div>
                                </div>

                                <div>
                                    <label for="email">Email Address</label>
                                    <input id="email" v-model="form.email" type="email" class="form-control" required>
                                    <div v-if="form.errors.email" class="text-danger mt-1">{{ form.errors.email }}</div>
                                </div>

                                <div>
                                    <label for="phone">Phone Number</label>
                                    <input id="phone" v-model="form.phone" type="text" class="form-control" placeholder="Optional">
                                </div>

                                <div>
                                    <label for="date_of_birth">Date of Birth</label>
                                    <input id="date_of_birth" v-model="form.date_of_birth" type="date" class="form-control">
                                </div>

                                <div class="account-field--full">
                                    <label for="address">Address</label>
                                    <textarea id="address" v-model="form.address" class="form-control" rows="3" placeholder="Street, city, province"></textarea>
                                </div>

                                <div class="account-field--full">
                                    <label for="profile_image">Profile Image</label>
                                    <input id="profile_image" ref="fileInputRef" type="file" class="form-control" accept="image/*" @change="onFileChange">
                                    <span v-if="form.profile_image" class="account-muted">Selected: {{ form.profile_image.name }}</span>
                                    <div v-if="form.errors.profile_image" class="text-danger mt-1">{{ form.errors.profile_image }}</div>
                                </div>
                            </div>

                            <div class="account-actions account-actions--inline">
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                                </button>
                                <Link href="/account/password" class="account-back">Change Password</Link>
                                <span class="account-muted">Profile image updates after you click Save Changes.</span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useForm, usePage, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const user = usePage().props.auth?.user || {};

const form = useForm({
    name: user.name || '',
    email: user.email || '',
    phone: user.phone || '',
    date_of_birth: normalizeDate(user.date_of_birth),
    address: user.address || '',
    profile_image: null,
});

function onFileChange(event) {
    form.profile_image = event.target.files[0] || null;
}

function submit() {
    form
    .transform((data) => ({
        ...data,
        _method: 'patch',
    }))
    .post('/account/settings', {
        forceFormData: true,
        preserveScroll: false,
        onSuccess: () => {
            form.profile_image = null;
            router.visit('/account/settings', {
                preserveState: false,
                preserveScroll: false,
                replace: true,
            });
        },
    });
}

function normalizeDate(value) {
    if (!value) return '';
    return String(value).slice(0, 10);
}

const avatarUrl = computed(() => {
    if (!user.profile_image) return null;
    return `/storage/${user.profile_image}`;
});

const previewUrl = ref(null);
const fileInputRef = ref(null);

watch(
    () => form.profile_image,
    (file) => {
        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value);
            previewUrl.value = null;
        }

        if (file instanceof File) {
            previewUrl.value = URL.createObjectURL(file);
        }
    }
);

onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
});

const avatarDisplayUrl = computed(() => previewUrl.value || avatarUrl.value);

function openImagePicker() {
    if (fileInputRef.value) {
        fileInputRef.value.click();
    }
}

const userInitials = computed(() => {
    const name = (form.name || user.name || '').trim();
    if (!name) return 'AS';
    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});
</script>

<style scoped>
.account-avatar-frame--editable {
    position: relative;
}

.avatar-edit-btn {
    position: absolute;
    right: 4px;
    bottom: 4px;
    width: 34px;
    height: 34px;
    border: 1px solid #ffd2c0;
    border-radius: 999px;
    background: #ffffff;
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.16);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.avatar-edit-btn:hover {
    background: #fff4f1;
}

.avatar-edit-icon {
    font-size: 0.95rem;
    line-height: 1;
}
</style>
