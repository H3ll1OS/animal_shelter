<template>
    <AppLayout>
        <section class="donate-hero">
            <div class="donate-content">
                <h1>Support Our Mission</h1>
                <p>Your generous donations help us provide food, medical care, and shelter for our animals. Every contribution makes a difference.</p>
                <button id="donate-btn" type="button" class="btn btn-primary" @click="openDonationFlow">Donate Now</button>

                <div v-if="flashSuccess" class="donation-page-alert donation-page-alert--success">{{ flashSuccess }}</div>
                <div v-if="flashError" class="donation-page-alert donation-page-alert--error">{{ flashError }}</div>
            </div>
        </section>

        <div class="donation-modal-overlay" v-if="showLoginPrompt" @click.self="showLoginPrompt = false">
            <div class="login-required-dialog">
                <h2>Login Required</h2>
                <p>You need to log in first before making a donation.</p>
                <div class="login-required-actions">
                    <Link href="/login" class="btn btn-primary">Go to Login</Link>
                    <button type="button" class="btn btn-secondary" @click="showLoginPrompt = false">Cancel</button>
                </div>
            </div>
        </div>

        <div class="donation-modal-overlay" v-if="showModal" @click.self="closeDonationModal">
            <div class="donation-dialog">
                <div class="donation-checkout-head">
                    <div class="brand-mini">
                        <span class="brand-mini-icon" aria-hidden="true">&#128062;</span>
                        <span class="brand-mini-text">
                            <strong>Animal Shelter</strong>
                            <small>Secure Donation</small>
                        </span>
                    </div>
                    <div class="checkout-steps">
                        <button
                            type="button"
                            class="step"
                            :class="{ 'is-active': currentStep === 1, 'is-done': currentStep > 1 }"
                            @click="goToStep(1)"
                        >
                            <span class="step-number">1</span>
                            <span class="step-label">Donation Details</span>
                        </button>
                        <button
                            type="button"
                            class="step"
                            :class="{ 'is-active': currentStep === 2, 'is-done': currentStep > 2 }"
                            @click="goToStep(2)"
                        >
                            <span class="step-number">2</span>
                            <span class="step-label">Payment Method</span>
                        </button>
                        <button
                            type="button"
                            class="step"
                            :class="{ 'is-active': currentStep === 3 }"
                            @click="goToStep(3)"
                        >
                            <span class="step-number">3</span>
                            <span class="step-label">Complete Gift</span>
                        </button>
                    </div>
                    <button type="button" class="donation-close" @click="closeDonationModal" aria-label="Close">&times;</button>
                </div>

                <div class="donation-layout">
                    <form @submit.prevent="submitDonation" class="donation-form-pane">
                        <h2>Donation Details</h2>
                        <p class="form-sub">Complete your donation securely in a few simple steps.</p>

                        <div v-if="stepError" class="step-alert">{{ stepError }}</div>

                        <section v-if="currentStep === 1" class="form-block">
                            <h3>Choose Amount</h3>
                            <div class="amount-options-clean">
                                <button type="button" class="amount-btn-clean" :class="{ active: selectedAmount === 50 }" @click="selectAmount(50)">₱50</button>
                                <button type="button" class="amount-btn-clean" :class="{ active: selectedAmount === 100 }" @click="selectAmount(100)">₱100</button>
                                <button type="button" class="amount-btn-clean" :class="{ active: selectedAmount === 500 }" @click="selectAmount(500)">₱500</button>
                                <button type="button" class="amount-btn-clean custom-trigger" :class="{ active: isCustom }" @click="isCustom = true">Custom</button>
                            </div>

                            <div v-if="isCustom" class="custom-input-wrapper">
                                <label for="custom-amount">Custom Amount (₱)</label>
                                <input type="number" id="custom-amount" v-model="customAmount" min="1" class="form-input-clean">
                            </div>

                            <h3>Donor Information</h3>
                            <div class="form-row-grid">
                                <div class="form-group-clean">
                                    <label for="name">First Name</label>
                                    <input type="text" id="name" v-model="form.name" :required="!form.anonymous" :disabled="form.anonymous" class="form-input-clean">
                                </div>
                                <div class="form-group-clean">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" id="last_name" v-model="form.last_name" :required="!form.anonymous" :disabled="form.anonymous" class="form-input-clean">
                                </div>
                            </div>
                            <div class="form-group-clean">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" v-model="form.email" :required="!form.anonymous" :disabled="form.anonymous" class="form-input-clean">
                            </div>
                            <label class="checkbox-row">
                                <input type="checkbox" v-model="form.anonymous">
                                <span>Give anonymously</span>
                            </label>
                        </section>

                        <section v-if="currentStep === 2" class="form-block">
                            <h3>Select Payment Method</h3>
                            <div class="pay-grid">
                                <button type="button" class="pay-btn" :class="{ active: form.payment_method === 'gcash' }" @click="form.payment_method = 'gcash'">
                                    <span class="pay-icon pay-icon--gcash" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <rect x="3.5" y="4.5" width="17" height="15" rx="2.5" stroke-width="1.8"/>
                                            <path d="M7 9h10M7 12h7M7 15h5" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <span>GCash</span>
                                </button>
                                <button type="button" class="pay-btn" :class="{ active: form.payment_method === 'paypal' }" @click="form.payment_method = 'paypal'">
                                    <span class="pay-icon pay-icon--paypal" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none">
                                            <path d="M8 5h5.8c2.9 0 4.4 1.7 4 4.1-.4 2.4-2.1 3.9-4.8 3.9H10l-.8 5H6.2L8 5z" fill="currentColor"/>
                                            <path d="M11 8h4.2c1.9 0 2.8 1 2.5 2.5-.3 1.5-1.3 2.4-3.1 2.4H12l-.5 3h-2l1.5-8z" fill="#fff" fill-opacity=".45"/>
                                        </svg>
                                    </span>
                                    <span>PayPal</span>
                                </button>
                                <button type="button" class="pay-btn" :class="{ active: form.payment_method === 'bank_transfer' }" @click="form.payment_method = 'bank_transfer'">
                                    <span class="pay-icon pay-icon--bank" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10l9-6 9 6M5 10v8m4-8v8m4-8v8m4-8v8M3 18h18"/></svg>
                                    </span>
                                    <span>Bank Transfer</span>
                                </button>
                                <button type="button" class="pay-btn" :class="{ active: form.payment_method === 'credit_card' }" @click="form.payment_method = 'credit_card'">
                                    <span class="pay-icon pay-icon--card" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="5" width="18" height="14" rx="2" ry="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18"/></svg>
                                    </span>
                                    <span>Credit Card</span>
                                </button>
                            </div>

                            <div v-if="form.payment_method === 'gcash'" class="pay-detail-grid">
                                <div class="form-group-clean">
                                    <label for="gcash_number">GCash Number</label>
                                    <input id="gcash_number" type="text" v-model="form.gcash_number" class="form-input-clean" placeholder="09XXXXXXXXX">
                                </div>
                                <div class="form-group-clean">
                                    <label for="gcash_ref">Reference Number (Optional)</label>
                                    <input id="gcash_ref" type="text" v-model="form.gcash_ref" class="form-input-clean" placeholder="Transaction reference">
                                </div>
                            </div>

                            <div v-if="form.payment_method === 'paypal'" class="pay-detail-grid">
                                <div class="form-group-clean">
                                    <label for="paypal_email">PayPal Email</label>
                                    <input id="paypal_email" type="email" v-model="form.paypal_email" class="form-input-clean" placeholder="you@example.com">
                                </div>
                            </div>

                            <div v-if="form.payment_method === 'bank_transfer'" class="pay-detail-grid">
                                <div class="form-group-clean">
                                    <label for="bank_name">Bank Name</label>
                                    <input id="bank_name" type="text" v-model="form.bank_name" class="form-input-clean" placeholder="Bank name">
                                </div>
                                <div class="form-group-clean">
                                    <label for="bank_ref">Transfer Reference</label>
                                    <input id="bank_ref" type="text" v-model="form.bank_ref" class="form-input-clean" placeholder="Reference number">
                                </div>
                            </div>

                            <div v-if="form.payment_method === 'credit_card'" class="pay-detail-grid">
                                <div class="form-group-clean">
                                    <label for="card_name">Cardholder Name</label>
                                    <input id="card_name" type="text" v-model="form.card_name" class="form-input-clean" placeholder="Name on card">
                                </div>
                                <div class="form-group-clean">
                                    <label for="card_last4">Last 4 Digits</label>
                                    <input id="card_last4" type="text" v-model="form.card_last4" class="form-input-clean" placeholder="1234" maxlength="4">
                                </div>
                            </div>
                        </section>

                        <section v-if="currentStep === 3" class="form-block">
                            <h3>Complete Your Gift</h3>
                            <div class="review-list">
                                <div><span>Amount</span><strong>₱{{ displayAmount }}</strong></div>
                                <div><span>Payment Method</span><strong>{{ paymentLabel }}</strong></div>
                                <div><span>Donation Type</span><strong>{{ form.anonymous ? 'Anonymous' : 'Named Donation' }}</strong></div>
                                <div v-if="!form.anonymous"><span>Donor</span><strong>{{ form.name }} {{ form.last_name }}</strong></div>
                            </div>
                        </section>

                        <div class="donation-actions">
                            <button v-if="currentStep > 1" type="button" class="btn-secondary-step" @click="goToStep(currentStep - 1)">
                                Back
                            </button>
                            <button v-if="currentStep < 3" type="button" class="btn-primary-step" @click="goToStep(currentStep + 1)">
                                Next
                            </button>
                            <button v-if="currentStep === 3" type="submit" class="btn-donate-final" :disabled="form.processing || !form.payment_method">
                                {{ form.processing ? 'Processing...' : 'Confirm Donation' }} <span>₱{{ displayAmount }}</span>
                            </button>
                        </div>
                    </form>

                    <aside class="donation-summary-pane">
                        <div class="summary-glow"></div>
                        <p class="summary-kicker">Your Impact</p>
                        <h3>Animal Care Fund</h3>
                        <p class="summary-copy">Every donation helps provide food, medicine, shelter maintenance, and rescue operations.</p>

                        <div class="summary-items">
                            <div><span>Donation Amount</span><strong>₱{{ displayAmount }}</strong></div>
                            <div><span>Payment Method</span><strong>{{ paymentLabel }}</strong></div>
                            <div><span>Support Type</span><strong>{{ form.anonymous ? 'Anonymous' : 'Named Donation' }}</strong></div>
                        </div>

                        <ul class="trust-list">
                            <li>Verified shelter operations</li>
                            <li>Transparent donation tracking</li>
                            <li>Secure payment handling</li>
                        </ul>
                    </aside>
                </div>
            </div>
        </div>

        <div class="donation-modal-overlay" v-if="showSuccessPopup" @click.self="showSuccessPopup = false">
            <div class="success-dialog">
                <div class="success-badge">Success</div>
                <h2>Donation Completed</h2>
                <p>{{ successMessage }}</p>
                <button type="button" class="btn btn-primary" @click="showSuccessPopup = false">Okay</button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success || null);
const flashError = computed(() => page.props.flash?.error || null);
const isAuthenticated = computed(() => Boolean(page.props.auth?.user?.id));

const showModal = ref(false);
const showLoginPrompt = ref(false);
const showSuccessPopup = ref(false);
const successMessage = ref('Thank you! Your donation has been successfully processed.');
const currentStep = ref(1);
const stepError = ref('');
const selectedAmount = ref(50);
const isCustom = ref(false);
const customAmount = ref('');

const form = useForm({
    name: '',
    last_name: '',
    email: '',
    anonymous: false,
    payment_method: '',
    amount: 50,
    gcash_number: '',
    gcash_ref: '',
    paypal_email: '',
    bank_name: '',
    bank_ref: '',
    card_name: '',
    card_last4: ''
});

const displayAmount = computed(() => {
    const value = isCustom.value ? Number(customAmount.value || 0) : selectedAmount.value;
    return Number(value || 0).toLocaleString();
});

const paymentLabel = computed(() => {
    if (!form.payment_method) return 'Not selected';
    return form.payment_method.replace('_', ' ').replace(/\b\w/g, (m) => m.toUpperCase());
});

function selectAmount(amount) {
    isCustom.value = false;
    selectedAmount.value = amount;
    form.amount = amount;
}

function openDonationFlow() {
    if (!isAuthenticated.value) {
        showLoginPrompt.value = true;
        return;
    }
    stepError.value = '';
    currentStep.value = 1;
    showModal.value = true;
}

function closeDonationModal() {
    showModal.value = false;
    stepError.value = '';
}

function getAmountValue() {
    return isCustom.value ? Number(customAmount.value || 0) : selectedAmount.value;
}

function clearPaymentDetails() {
    form.gcash_number = '';
    form.gcash_ref = '';
    form.paypal_email = '';
    form.bank_name = '';
    form.bank_ref = '';
    form.card_name = '';
    form.card_last4 = '';
}

function validateStep(step) {
    stepError.value = '';
    const amount = getAmountValue();

    if (step >= 1) {
        if (amount <= 0) {
            stepError.value = 'Please enter a valid donation amount.';
            return false;
        }

        if (!form.anonymous) {
            if (!form.name?.trim() || !form.last_name?.trim() || !form.email?.trim()) {
                stepError.value = 'Please complete your donor information or enable anonymous donation.';
                return false;
            }
        }
    }

    if (step >= 2) {
        if (!form.payment_method) {
            stepError.value = 'Please select a payment method.';
            return false;
        }

        if (form.payment_method === 'gcash' && !form.gcash_number?.trim()) {
            stepError.value = 'Please enter your GCash number.';
            return false;
        }

        if (form.payment_method === 'paypal' && !form.paypal_email?.trim()) {
            stepError.value = 'Please enter your PayPal email.';
            return false;
        }

        if (form.payment_method === 'bank_transfer' && (!form.bank_name?.trim() || !form.bank_ref?.trim())) {
            stepError.value = 'Please provide bank transfer details.';
            return false;
        }

        if (form.payment_method === 'credit_card' && (!form.card_name?.trim() || !form.card_last4?.trim())) {
            stepError.value = 'Please provide card details.';
            return false;
        }
    }

    return true;
}

function goToStep(step) {
    if (step < 1 || step > 3) return;
    if (step > currentStep.value && !validateStep(currentStep.value)) return;
    stepError.value = '';
    currentStep.value = step;
}

function resetDonationForm() {
    isCustom.value = false;
    customAmount.value = '';
    selectedAmount.value = 50;
    currentStep.value = 1;
    stepError.value = '';
    form.reset(
        'name',
        'last_name',
        'email',
        'anonymous',
        'payment_method',
        'gcash_number',
        'gcash_ref',
        'paypal_email',
        'bank_name',
        'bank_ref',
        'card_name',
        'card_last4'
    );
    form.clearErrors();
    form.amount = 50;
}

function submitDonation() {
    if (!validateStep(2)) {
        currentStep.value = Math.min(currentStep.value, 2);
        return;
    }

    form.amount = getAmountValue();
    form.post('/donate', {
        onSuccess: (pageData) => {
            showModal.value = false;
            const flashMessage = pageData?.props?.flash?.success;
            successMessage.value = flashMessage || `Thank you! Your donation of P${Number(form.amount).toLocaleString()} has been processed successfully.`;
            showSuccessPopup.value = true;
            resetDonationForm();
        }
    });
}

watch(
    () => form.anonymous,
    (isAnonymous) => {
        if (isAnonymous) {
            form.name = '';
            form.last_name = '';
            form.email = '';
        }
    }
);

watch(
    () => form.payment_method,
    () => {
        clearPaymentDetails();
        stepError.value = '';
    }
);
</script>

<style scoped>
.donation-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 22px 14px;
    background: rgba(15, 23, 42, 0.58);
    backdrop-filter: blur(4px);
}

.donation-dialog {
    width: min(96vw, 1020px);
    max-height: 92vh;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    box-shadow: 0 34px 90px rgba(2, 6, 23, 0.36);
    overflow: hidden;
    display: grid;
    grid-template-rows: auto minmax(0, 1fr);
}

.donation-checkout-head {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    border-bottom: 1px solid #e2e8f0;
    background: #fff;
}

.brand-mini {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding-right: 8px;
    white-space: nowrap;
}

.brand-mini-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: #9a3412;
    background: linear-gradient(180deg, #fff7ed 0%, #ffedd5 100%);
    border: 1px solid #fed7aa;
    box-shadow: 0 6px 14px rgba(249, 115, 22, 0.16);
}

.brand-mini-text {
    display: grid;
    line-height: 1.1;
    gap: 2px;
}

.brand-mini-text strong {
    color: #1e293b;
    font-size: 0.95rem;
    font-weight: 800;
}

.brand-mini-text small {
    color: #64748b;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.checkout-steps {
    margin-left: auto;
    margin-right: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.step {
    border: 1px solid #e2e8f0;
    background: #ffffff;
    border-radius: 12px;
    min-height: 42px;
    padding: 8px 12px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.82rem;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    transition: all 0.18s ease;
}

.step:hover {
    border-color: #cbd5e1;
    transform: translateY(-1px);
}

.step-number {
    width: 22px;
    height: 22px;
    border-radius: 999px;
    border: 1px solid #cbd5e1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 800;
    background: #f8fafc;
    color: #64748b;
}

.step-label {
    line-height: 1;
}

.step.is-active {
    border-color: #fb923c;
    background: linear-gradient(180deg, #fff7ed 0%, #ffedd5 100%);
    color: #c2410c;
    box-shadow: 0 12px 24px rgba(249, 115, 22, 0.2);
}

.step.is-active .step-number {
    border-color: #fb923c;
    background: #f97316;
    color: #ffffff;
}

.step.is-done {
    border-color: #86efac;
    background: #f0fdf4;
    color: #166534;
}

.step.is-done .step-number {
    border-color: #22c55e;
    background: #22c55e;
    color: #ffffff;
}

.donation-close {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #475569;
    font-size: 1.15rem;
    cursor: pointer;
}

.donation-layout {
    display: grid;
    grid-template-columns: 1.35fr 1fr;
    min-height: 0;
}

.donation-form-pane {
    padding: 18px 18px 16px;
    overflow-y: auto;
}

.donation-form-pane h2 {
    margin: 0;
    font-size: 1.65rem;
    color: #0f172a;
}

.form-sub {
    margin: 6px 0 14px;
    color: #64748b;
}

.step-alert {
    margin-bottom: 10px;
    border-radius: 10px;
    padding: 10px 12px;
    border: 1px solid #fecaca;
    background: #fff1f2;
    color: #991b1b;
    font-weight: 700;
}

.form-block {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px;
    background: #fff;
    margin-bottom: 12px;
}

.form-block h3 {
    margin: 0 0 10px;
    color: #1e293b;
    font-size: 1rem;
}

.amount-options-clean {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
}

.amount-btn-clean {
    min-height: 48px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #f8fafc;
    font-weight: 800;
    color: #334155;
    cursor: pointer;
}

.amount-btn-clean.active {
    border-color: #f97316;
    background: #fff7ed;
    color: #c2410c;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.16);
}

.custom-input-wrapper {
    margin-top: 10px;
}

.custom-input-wrapper label {
    display: block;
    margin-bottom: 6px;
    font-size: 0.86rem;
    font-weight: 700;
    color: #64748b;
}

.form-row-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.form-group-clean {
    margin-bottom: 10px;
}

.form-group-clean label {
    display: block;
    margin-bottom: 6px;
    font-size: 0.86rem;
    font-weight: 700;
    color: #64748b;
}

.form-input-clean {
    width: 100%;
    min-height: 44px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    padding: 10px 12px;
    background: #fff;
}

.form-input-clean:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.14);
}

.checkbox-row {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #475569;
    font-size: 0.9rem;
    font-weight: 600;
}

.checkbox-row input {
    accent-color: #f97316;
}

.pay-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.pay-btn {
    min-height: 44px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    font-weight: 700;
    color: #334155;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: border-color 0.16s ease, box-shadow 0.16s ease, transform 0.16s ease, color 0.16s ease;
}

.pay-btn:hover {
    border-color: #fb923c;
    color: #9a3412;
}

.pay-btn.active {
    border-color: #f97316;
    background: #fff7ed;
    color: #9a3412;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.16);
    transform: translateY(-1px);
}

.pay-icon {
    width: 26px;
    height: 26px;
    border-radius: 7px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 1px solid #dbe3ee;
    color: currentColor;
    flex-shrink: 0;
}

.pay-icon svg {
    width: 15px;
    height: 15px;
}

.pay-icon--gcash {
    color: #0f60d1;
    background: linear-gradient(145deg, #eef6ff 0%, #ffffff 100%);
}

.pay-icon--paypal {
    color: #003087;
    background: linear-gradient(145deg, #e9f2ff 0%, #ffffff 100%);
}

.pay-icon--bank {
    color: #475569;
    background: linear-gradient(145deg, #f8fafc 0%, #ffffff 100%);
}

.pay-icon--card {
    color: #1d4ed8;
    background: linear-gradient(145deg, #eef2ff 0%, #ffffff 100%);
}

.donation-actions {
    margin-top: 12px;
    display: flex;
    gap: 10px;
}

.btn-donate-final {
    flex: 1;
    min-height: 50px;
    border: 0;
    border-radius: 12px;
    background: linear-gradient(135deg, #fb923c 0%, #f97316 70%, #ea580c 100%);
    color: #fff;
    font-weight: 800;
    font-size: 1rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 16px 34px rgba(249, 115, 22, 0.3);
}

.btn-donate-final:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    box-shadow: none;
}

.btn-primary-step,
.btn-secondary-step {
    min-height: 50px;
    border-radius: 12px;
    padding: 0 18px;
    font-weight: 800;
    cursor: pointer;
}

.btn-primary-step {
    flex: 1;
    border: 0;
    color: #fff;
    background: linear-gradient(135deg, #fb923c 0%, #f97316 70%, #ea580c 100%);
}

.btn-secondary-step {
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #334155;
}

.review-list {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #f8fafc;
    padding: 12px;
    display: grid;
    gap: 8px;
}

.review-list div {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.review-list span {
    color: #64748b;
    font-size: 0.9rem;
}

.review-list strong {
    color: #0f172a;
}

.pay-detail-grid {
    margin-top: 12px;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.donation-summary-pane {
    position: relative;
    overflow: hidden;
    padding: 24px 20px;
    background: linear-gradient(165deg, #fff7ed 0%, #ffedd5 58%, #fed7aa 100%);
    border-left: 1px solid #fde68a;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.summary-glow {
    position: absolute;
    width: 260px;
    height: 260px;
    right: -80px;
    top: -70px;
    border-radius: 999px;
    background: radial-gradient(circle, rgba(249, 115, 22, 0.28) 0%, rgba(249, 115, 22, 0) 70%);
    pointer-events: none;
}

.summary-kicker {
    margin: 0;
    position: relative;
    font-size: 0.75rem;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    font-weight: 800;
    color: #9a3412;
}

.donation-summary-pane h3 {
    margin: 0;
    position: relative;
    font-size: 1.65rem;
    line-height: 1.15;
    color: #7c2d12;
}

.summary-copy {
    margin: 0;
    position: relative;
    color: #7c2d12;
    line-height: 1.6;
}

.summary-items {
    position: relative;
    border: 1px solid rgba(194, 65, 12, 0.2);
    background: rgba(255, 255, 255, 0.72);
    border-radius: 14px;
    padding: 10px;
    display: grid;
    gap: 8px;
}

.summary-items div {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.summary-items span {
    font-size: 0.84rem;
    color: #9a3412;
    font-weight: 700;
}

.summary-items strong {
    color: #7c2d12;
    font-size: 0.95rem;
}

.trust-list {
    position: relative;
    margin: 0;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 8px;
}

.trust-list li {
    color: #7c2d12;
    font-size: 0.9rem;
    font-weight: 600;
    padding-left: 18px;
    position: relative;
}

.trust-list li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0.48em;
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #f97316;
}

.donation-page-alert {
    margin-top: 14px;
    border-radius: 10px;
    padding: 10px 12px;
    font-weight: 700;
}

.donation-page-alert--success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.donation-page-alert--error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.login-required-dialog {
    width: min(92vw, 420px);
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    box-shadow: 0 24px 60px rgba(2, 6, 23, 0.3);
    padding: 22px 20px 18px;
    text-align: center;
}

.login-required-dialog h2 {
    margin: 0;
    font-size: 1.4rem;
    color: #0f172a;
}

.login-required-dialog p {
    margin: 10px 0 0;
    color: #475569;
}

.login-required-actions {
    margin-top: 18px;
    display: flex;
    gap: 10px;
    justify-content: center;
}

.success-dialog {
    width: min(92vw, 440px);
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    box-shadow: 0 24px 60px rgba(2, 6, 23, 0.3);
    padding: 22px 20px 18px;
    text-align: center;
}

.success-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    min-height: 26px;
    padding: 0 10px;
    border-radius: 999px;
    border: 1px solid #fed7aa;
    background: #fff7ed;
    color: #c2410c;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.success-dialog h2 {
    margin: 0;
    color: #0f172a;
}

.success-dialog p {
    margin: 10px 0 16px;
    color: #475569;
}

@media (max-width: 960px) {
    .donation-layout {
        grid-template-columns: 1fr;
    }

    .donation-summary-pane {
        border-left: 0;
        border-top: 1px solid #fde68a;
    }
}

@media (max-width: 680px) {
    .checkout-steps {
        display: none;
    }

    .amount-options-clean,
    .pay-grid,
    .form-row-grid,
    .pay-detail-grid {
        grid-template-columns: 1fr;
    }

    .donation-actions {
        flex-direction: column;
    }
}
</style>
