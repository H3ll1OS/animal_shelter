<template>
    <AppLayout>
        <section class="hero">
            <div class="hero-content">
                <h1>Find Your New <span style="color: #FF6D2E;">Best Friend</span></h1>
                <p>Give a deserving animal a forever home. Start your search today and discover a world of unconditional love.</p>
                <Link href="/adopt" class="btn btn-primary">Meet The Animals</Link>
            </div>
        </section>

        <section class="featured-animals">
            <h2>Ready For a Home</h2>
            <div class="animals-grid">
                <div v-for="animal in featuredAnimals" :key="animal.id" 
                     class="animal-card clickable-card" 
                     :data-id="animal.id" 
                     :data-animal-name="animal.name"
                     @click="viewPet(animal.id)">
                    <img :src="'/storage/' + animal.image_path" :alt="animal.name">
                    <div class="animal-info">
                        <h3>{{ animal.name }}</h3>
                        <p>{{ animal.breed }}</p>
                    </div>
                </div>
            </div>
            <div class="featured-cta">
                <Link href="/adopt" class="btn btn-primary">View All Pets</Link>
            </div>
        </section>

        <section class="process">
            <h2>Our Adoption Process</h2>
            <div class="process-steps">
                <div class="step">
                    <div class="step-icon">🔎</div>
                    <h3>1. Find a Friend</h3>
                    <p>Browse our adoptable pets online. Use filters to find the perfect companion for your lifestyle.</p>
                </div>
                <div class="step">
                    <div class="step-icon">📝</div>
                    <h3>2. Apply Online</h3>
                    <p>Fill out our simple adoption request form. This helps us understand your home and needs.</p>
                </div>
                <div class="step">
                    <div class="step-icon">🤝</div>
                    <h3>3. Meet & Greet</h3>
                    <p>We'll schedule a visit for you to meet the animal and finalize the adoption with our team.</p>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps({
    featuredAnimals: {
        type: Array,
        default: () => []
    }
});

function viewPet(id) {
    window.location.href = `/adopt/${id}`;
}
</script>

