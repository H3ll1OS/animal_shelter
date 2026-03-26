<template>
    <AppLayout>
        <section class="about-hero about-hero-custom">
            <div class="hero-content about-hero-inner">
                <h1>About Our Shelter</h1>
                <p>Committed to providing love and care to animals in need since 2010.</p>
            </div>
        </section>

        <section class="about-details about-details-custom">
            <div class="about-grid about-grid-custom">
                <div class="about-card about-card-custom">
                    <h3>Our Mission</h3>
                    <p>We are dedicated to rescuing, rehabilitating, and rehoming animals in need. Every animal deserves a loving forever home.</p>
                </div>
                <div class="about-card about-card-custom">
                    <h3>What We Do</h3>
                    <p>Rescue abandoned and surrendered animals.</p>
                    <p>Provide medical care and rehabilitation.</p>
                    <p>Find loving forever homes.</p>
                    <p>Promote responsible pet ownership.</p>
                </div>
                <div class="about-card about-card-custom">
                    <h3>Our Team</h3>
                    <p>Our dedicated team of veterinarians, trainers, and volunteers works tirelessly to ensure every animal receives the best care possible.</p>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<style scoped>
.about-hero-custom {
    padding: 64px 20px 56px;
}

.about-hero-inner {
    max-width: 860px;
}

.about-details-custom {
    padding: 56px 0 72px;
}

.about-grid-custom {
    align-items: stretch;
    gap: 24px;
}

.about-card-custom {
    background: #ffffff;
    border: 1px solid #e4e7ec;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(16, 24, 40, 0.06);
    min-height: 280px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

.about-card-custom h3 {
    margin-bottom: 14px;
}

.about-card-custom p {
    margin-bottom: 8px;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .about-hero-custom {
        padding: 48px 16px 40px;
    }

    .about-details-custom {
        padding: 36px 0 48px;
    }
}
</style>

