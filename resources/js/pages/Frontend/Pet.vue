<template>
    <AppLayout>
        <div class="page-wrap">
            <div class="shell">
                <div class="topbar">
                    <div class="crumb">
                        <Link href="/adopt">Adopt</Link> / <span>{{ pet.name }}</span>
                    </div>
                    <div class="chiprow">
                        <span class="chip"><span class="dot"></span> {{ pet.species }}</span>
                        <span class="chip chip-muted"><span class="dot"></span> {{ pet.breed || 'Mixed Breed' }}</span>
                        <span class="chip chip-muted"><span class="dot"></span> Age: {{ pet.age || 'N/A' }}</span>
                    </div>
                </div>

                <div class="grid">
                    <div class="adopt-card form-card">
                        <div class="form-hero">
                            <div class="kicker">Adoption application</div>
                            <h2>Apply for {{ pet.name }}</h2>
                            <p>We'll review your request and contact you for next steps.</p>
                        </div>

                        <form @submit.prevent="submitApplication" enctype="multipart/form-data">
                            <div class="section">
                                <h4>About You</h4>
                                <p class="hint">We'll use this to stay in touch about your application.</p>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="field-label mb-1">Full Name <span class="req">*</span></label>
                                        <input v-model="form.full_name" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="field-label mb-1">Email Address <span class="req">*</span></label>
                                        <input v-model="form.email" type="email" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="field-label mb-1">Phone Number <span class="req">*</span></label>
                                        <input v-model="form.phone" type="tel" class="form-control" placeholder="e.g., 09xxxxxxxxx" required>
                                    </div>
                                </div>
                            </div>

                            <div class="section">
                                <h4>Your Home & Fit</h4>
                                <p class="hint">Optional, but it helps us understand your lifestyle and experience.</p>

                                <label class="field-label mb-1">Why do you want to adopt {{ pet.name }}?</label>
                                <textarea v-model="form.message" class="form-control" rows="4" placeholder="Tell us about your home, routine, and any experience with pets."></textarea>
                            </div>

                            <div class="section">
                                <h4>Required Document</h4>
                                <p class="hint">Upload PDF/JPG/PNG (max 5MB).</p>

                                <div class="row g-3">
                                    <div class="col-lg-8">
                                        <div class="drop-zone">
                                            <label class="field-label mb-2">Supporting Document <span class="req">*</span></label>
                                            <input @change="handleFileUpload" type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png" multiple required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="actions">
                                <div v-if="flashSuccess" class="alert alert-success mb-3">
                                    {{ flashSuccess }}
                                </div>
                                <div v-if="flashError" class="alert alert-danger mb-3">
                                    {{ flashError }}
                                </div>
                                <div v-if="!$page.props.auth?.user" class="alert alert-info mb-3">Please log in to submit your application.</div>
                                <Link v-if="!$page.props.auth?.user" href="/login" class="accent-btn d-inline-flex justify-content-center align-items-center">
                                    Log In to Apply
                                </Link>
                                <button v-else type="submit" class="accent-btn d-inline-flex justify-content-center align-items-center">
                                    Submit Application
                                </button>
                                <div class="text-center mt-3 text-muted note-text">
                                    Submitting doesn't guarantee adoption — we'll follow up after review.
                                </div>
                            </div>
                        </form>
                    </div>

                    <aside class="pet-card">
                        <div class="pet-media">
                            <img :src="petImage" :alt="pet.name">
                            <div class="pet-badge" :class="{ 'warn': pet.status !== 'Available' }">
                                <span class="dot"></span>
                                <span>{{ pet.status || 'Available' }}</span>
                            </div>
                        </div>

                        <div class="pet-body">
                            <h3>About {{ pet.name }}</h3>
                            <p>{{ pet.description || 'No detailed description available yet. Contact the shelter for more information.' }}</p>

                            <div class="facts">
                                <div class="fact"><div class="k">Gender</div><div class="v">{{ pet.gender || 'N/A' }}</div></div>
                                <div class="fact"><div class="k">Age</div><div class="v">{{ pet.age || 'Unknown' }}</div></div>
                                <div class="fact"><div class="k">Weight</div><div class="v">{{ pet.weight || 'N/A' }}</div></div>
                                <div class="fact"><div class="k">Location</div><div class="v">{{ pet.location || 'Main Shelter' }}</div></div>
                                <div class="fact"><div class="k">Fee</div><div class="v">{{ pet.adoption_fee ? '₱' + Number(pet.adoption_fee).toLocaleString() : 'None' }}</div></div>
                                <div class="fact"><div class="k">Breed</div><div class="v">{{ pet.breed || 'Mixed Breed' }}</div></div>
                            </div>

                            <div class="trust">
                                <span><i></i> Reviewed by shelter staff</span>
                                <span><i></i> Safe adoption process</span>
                                <span><i></i> Follow-up support</span>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    pet: {
        type: Object,
        required: true
    },
    user: {
        type: Object,
        default: null
    }
});

const form = useForm({
    full_name: props.user?.full_name || '',
    email: props.user?.email || '',
    phone: props.user?.phone || '',
    message: '',
    documents: []
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success || null);
const flashError = computed(() => page.props.flash?.error || null);

const petImage = props.pet?.image_path 
    ? '/storage/' + props.pet.image_path 
    : '/images/default_pet.jpg';

function handleFileUpload(event) {
    form.documents = Array.from(event.target.files || []);
}

function submitApplication() {
    form.post(`/adopt/${props.pet.id}`, {
        forceFormData: true,
        preserveScroll: true
    });
}
</script>

