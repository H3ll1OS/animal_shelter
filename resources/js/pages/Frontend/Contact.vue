<template>
    <AppLayout>
        <section class="contact-premium">
            <div class="contact-shell">
                <div class="contact-page-hero">
                    <p class="hero-kicker">Support Center</p>
                    <h1>Contact Our Shelter Team</h1>
                    <p>
                        We are here to help with adoptions, volunteering, foster inquiries, and donation support.
                        Reach out through the channel that works best for you.
                    </p>
                </div>

                <div class="contact-layout">
                    <div class="contact-main">
                        <article class="info-card">
                            <h2>Shelter Information</h2>
                            <div class="info-list">
                                <a href="mailto:info@animalshelter.org" class="info-item">
                                    <span class="info-icon" aria-hidden="true">&#9993;</span>
                                    <div>
                                        <small>Email</small>
                                        <strong>info@animalshelter.org</strong>
                                    </div>
                                </a>
                                <a href="tel:+15555552287" class="info-item">
                                    <span class="info-icon" aria-hidden="true">&#9742;</span>
                                    <div>
                                        <small>Phone</small>
                                        <strong>(555) 555-CATS</strong>
                                    </div>
                                </a>
                                <div class="info-item">
                                    <span class="info-icon" aria-hidden="true">&#128205;</span>
                                    <div>
                                        <small>Address</small>
                                        <strong>123 Navalca Street, Surigao City, 8400</strong>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <article class="info-card">
                            <h2>Visiting Hours</h2>
                            <div class="hours-grid">
                                <div><span>Monday - Friday</span><strong>9:00 AM - 6:00 PM</strong></div>
                                <div><span>Saturday</span><strong>10:00 AM - 5:00 PM</strong></div>
                                <div><span>Sunday</span><strong>Closed</strong></div>
                            </div>
                        </article>
                    </div>

                    <aside class="contact-side">
                        <article class="support-card">
                            <p class="support-kicker">Need Assistance?</p>
                            <h3>Quick Response During Business Hours</h3>
                            <p>For urgent concerns related to rescue and animal welfare, call us directly for faster assistance.</p>
                            <ul class="support-points">
                                <li>Adoption and application concerns</li>
                                <li>Donation and payment support</li>
                                <li>Volunteer and foster inquiries</li>
                            </ul>
                            <div class="support-actions">
                                <a href="mailto:info@animalshelter.org" class="btn-primary">Email Us</a>
                                <a href="tel:+15555552287" class="btn-secondary">Call Now</a>
                            </div>
                        </article>
                    </aside>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<style scoped>
.contact-premium {
    padding: 42px 20px 78px;
    background:
        radial-gradient(circle at 7% 0%, rgba(251, 146, 60, 0.16) 0, rgba(251, 146, 60, 0) 27%),
        radial-gradient(circle at 100% 12%, rgba(249, 115, 22, 0.12) 0, rgba(249, 115, 22, 0) 28%),
        linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
}

.contact-shell {
    max-width: 1160px;
    margin: 0 auto;
}

.contact-page-hero {
    margin-bottom: 26px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
    padding: 26px 30px;
    text-align: left;
}

.hero-kicker {
    margin: 0 0 6px;
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 800;
    color: #c2410c;
}

.contact-page-hero h1 {
    margin: 0;
    font-size: clamp(1.9rem, 3vw, 2.35rem);
    line-height: 1.2;
    color: #0f172a;
    max-width: 640px;
}

.contact-page-hero p {
    margin: 12px 0 0;
    max-width: 700px;
    color: #475569;
    font-size: 1rem;
    line-height: 1.68;
}

.contact-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.95fr);
    gap: 22px;
    align-items: start;
}

.contact-main {
    display: grid;
    gap: 20px;
}

.info-card,
.support-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 14px 36px rgba(15, 23, 42, 0.08);
    padding: 22px;
}

.info-card h2 {
    margin: 0 0 16px;
    font-size: 1.24rem;
    color: #0f172a;
}

.info-list {
    display: grid;
    gap: 10px;
}

.info-item {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    min-height: 76px;
    padding: 12px 13px;
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f8fafc;
    text-decoration: none;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

a.info-item:hover {
    border-color: #fdba74;
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.12);
}

.info-item strong {
    color: #0f172a;
    display: block;
}

.info-item small {
    display: block;
    color: #64748b;
    font-size: 0.76rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.info-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    background: linear-gradient(180deg, #fff7ed 0%, #ffedd5 100%);
    color: #c2410c;
    border: 1px solid #fed7aa;
}

.hours-grid {
    display: grid;
    gap: 10px;
}

.hours-grid div {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #f8fafc;
    padding: 12px 13px;
    display: flex;
    justify-content: space-between;
    gap: 14px;
}

.hours-grid span {
    color: #475569;
    font-weight: 600;
}

.hours-grid strong {
    color: #0f172a;
}

.contact-side {
    align-self: start;
}

.support-card {
    background: linear-gradient(160deg, #fff7ed 0%, #ffedd5 100%);
    border-color: #fdba74;
    position: sticky;
    top: 96px;
}

.support-kicker {
    margin: 0 0 8px;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 800;
    color: #c2410c;
}

.support-card h3 {
    margin: 0;
    font-size: 1.34rem;
    color: #7c2d12;
}

.support-card p {
    margin: 10px 0 0;
    color: #9a3412;
    line-height: 1.65;
}

.support-points {
    margin: 14px 0 0;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 8px;
}

.support-points li {
    position: relative;
    padding-left: 16px;
    color: #7c2d12;
    font-weight: 600;
}

.support-points li::before {
    content: '';
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: #f97316;
    position: absolute;
    left: 0;
    top: 0.55em;
}

.support-actions {
    margin-top: 18px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.btn-primary,
.btn-secondary {
    min-height: 42px;
    padding: 0 16px;
    border-radius: 11px;
    font-weight: 800;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-primary {
    color: #fff;
    border: 0;
    background: linear-gradient(135deg, #fb923c 0%, #f97316 70%, #ea580c 100%);
    box-shadow: 0 12px 24px rgba(249, 115, 22, 0.25);
}

.btn-secondary {
    color: #7c2d12;
    border: 1px solid #fdba74;
    background: rgba(255, 255, 255, 0.72);
}

@media (max-width: 980px) {
    .contact-layout {
        grid-template-columns: 1fr;
    }

    .support-card {
        position: static;
        top: auto;
    }

    .support-actions {
        flex-direction: column;
    }

    .btn-primary,
    .btn-secondary {
        width: 100%;
    }
}

@media (max-width: 640px) {
    .contact-premium {
        padding: 22px 14px 54px;
    }

    .contact-page-hero {
        padding: 20px 18px;
    }

    .info-card,
    .support-card {
        padding: 18px;
    }

    .hours-grid div {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
}
</style>
