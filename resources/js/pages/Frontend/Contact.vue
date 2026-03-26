<template>
    <AppLayout>
        <section class="contact-hero contact-hero-custom">
            <div class="hero-content contact-hero-inner">
                <h1>Contact Us</h1>
                <p>We would love to hear from you. Reach out for adoptions, volunteering, or support.</p>
            </div>
        </section>

        <section class="contact-info contact-info-custom">
            <div class="contact-grid contact-grid-custom">
                <div class="contact-card contact-card-custom">
                    <h3>Shelter Information</h3>
                    <p><strong>Email:</strong> info@animalshelter.org</p>
                    <p><strong>Phone:</strong> (555) 555-CATS</p>
                    <p><strong>Address:</strong> 123 Shelter Lane, Metropolis, 90210</p>
                </div>
                <div class="contact-card contact-card-custom">
                    <h3>Hours</h3>
                    <p>Monday - Friday: 9:00 AM to 6:00 PM</p>
                    <p>Saturday: 10:00 AM to 5:00 PM</p>
                    <p>Sunday: Closed</p>
                </div>
                <div class="contact-card contact-card-custom">
                    <h3>Support</h3>
                    <p>Questions about adoption, donations, or volunteering?</p>
                    <p>Send us a message and our team will get back to you as soon as possible.</p>
                    <a href="mailto:info@animalshelter.org" class="btn btn-primary mt-2">Email Us</a>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<style scoped>
.contact-hero-custom {
    padding: 64px 20px 56px;
}

.contact-hero-inner {
    max-width: 860px;
}

.contact-info-custom {
    padding: 56px 0 72px;
}

.contact-grid-custom {
    align-items: stretch;
    gap: 24px;
}

.contact-card-custom {
    background: #ffffff;
    border: 1px solid #e4e7ec;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(16, 24, 40, 0.06);
    min-height: 260px;
    text-align: left;
}

.contact-card-custom h3 {
    margin-bottom: 14px;
}

.contact-card-custom p {
    margin-bottom: 10px;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .contact-hero-custom {
        padding: 48px 16px 40px;
    }

    .contact-info-custom {
        padding: 36px 0 48px;
    }
}
</style>
