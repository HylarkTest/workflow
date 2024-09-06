<template>
    <div
        class="h-full md:flex md:justify-start"
    >
        <div class="w-full max-w-lg">

            <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 shadow-primary-600/20">
                <template
                    v-if="!updating && !renewing"
                >
                    <h3 class="font-bold text-xl text-primary-800 mb-4">
                        I want to upgrade to <span class="text-primary-600">{{ upgradePlanName }}</span>!
                    </h3>

                    <div class="mb-4">
                        <button
                            class="button--sm mr-2"
                            :class="period === 'MONTH' ? 'button-primary' : 'button-primary--light'"
                            type="button"
                            @click="period = 'MONTH'"
                        >
                            Monthly
                        </button>

                        <button
                            class="button--sm"
                            :class="period === 'YEAR' ? 'button-primary' : 'button-primary--light'"
                            type="button"
                            @click="period = 'YEAR'"
                        >
                            Yearly
                        </button>
                    </div>

                    <div class="mb-4">
                        <span
                            class="text-4xl text-primary-600"
                        >
                            ${{ totalAmount }}
                        </span>
                        <span
                            class="ml-1 font-semibold text-cm-500"
                        >
                            / {{ periodLabel }} {{ remainingString }}
                        </span>

                        <div
                            v-if="isCollaborativeBase"
                            class="text-cm-500"
                        >
                            ${{ seatAmount }} x {{ seats }} seats
                        </div>
                    </div>
                </template>

                <h3
                    v-else
                    class="font-bold text-xl text-primary-700 mb-4"
                >
                    Update your card details
                </h3>

                <!-- Payment Method Form -->
                <div
                    class="mb-3"
                >
                    <div
                        v-if="paymentElement && !isFullDiscount"
                        class="relative"
                    >
                        <!-- Stripe Payment Element -->
                        <label
                            for="payment-element"
                            class="inline-block text-sm text-gray-700 font-semibold mb-2"
                        >
                            Payment details
                        </label>

                        <div
                            ref="paymentElement"
                            id="payment-element"
                            class="bg-cm-100 rounded-lg p-4 mb-6"
                        ></div>

                        <AlertTooltip
                            v-if="errorMessage"
                        >
                            {{ errorMessage }}
                        </AlertTooltip>
                    </div>
                    <div v-if="!updating && !renewing">
                        <form
                            @submit.prevent="applyCoupon"
                        >
                            <label
                                for="coupon"
                                class="inline-block text-sm text-gray-700 font-semibold mb-2"
                            >
                                Promotional code
                            </label>
                            <div
                                class="flex"
                            >
                                <InputBox
                                    id="coupon"
                                    v-model="couponCode"
                                    bgColor="gray"
                                    name="coupon"
                                    placeholder="Enter a promotional code"
                                    class="flex-grow mr-1"
                                    :error="couponError"
                                />
                                <button
                                    type="submit"
                                    class="button button-primary"
                                    :class="{ 'opacity-50': loadingCoupon }"
                                    :disabled="loadingCoupon"
                                >
                                    Apply
                                </button>
                            </div>
                            <div
                                v-if="coupon"
                                class="bg-green-100 border border-green-200 rounded-lg p-2 mt-2"
                            >
                                <p class="text-sm text-gray-700">
                                    Coupon {{ coupon.code }} applied!
                                </p>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Confirm Payment Method Button -->
                <button
                    type="button"
                    class="button--lg button-primary"
                    :disabled="isPaymentProcessing"
                    @click="upgrade"
                >
                    <!-- <span v-if="isPaymentProcessing">
                        Processing...
                    </span> -->
                    <span>
                        {{ (updating || renewing) ? 'Update details' : 'Upgrade now' }}
                    </span>
                </button>

                <p
                    v-if="!isFullDiscount"
                    class="text-xs mt-4"
                >
                    Payments processed by Stripe.
                    Hylark does not store or have access to your full card number, expiry date, or CVC.
                </p>
            </div>
        </div>
        <div
            v-if="isPaymentProcessing"
            class="fixed w-full h-full bottom-0 left-0 pointer-events-none centered"
        >
            <div class="absolute opacity-75 bg-cm-00 w-full h-full">

            </div>

            <LoaderProcessing
                class="z-over relative"
            >
            </LoaderProcessing>

        </div>
    </div>
</template>

<script>

import axios from 'axios';
import User from '@/core/models/User.js';

import LoaderProcessing from '@/components/loaders/LoaderProcessing.vue';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';

import { isActiveBaseCollaborative } from '@/core/repositories/baseRepository.js';

const stripe = window.Stripe(import.meta.env.VITE_STRIPE_KEY);

export default {
    name: 'SubscriptionForm',
    components: {
        LoaderProcessing,
        AlertTooltip,
    },
    props: {
        user: {
            type: User,
            required: true,
        },
        updating: Boolean,
        renewing: Boolean,
        seatsNumber: {
            type: Number,
            default: 1,
        },
    },
    emits: [
        'subscribe',
    ],
    data() {
        return {
            paymentMethod: {
                title: 'Card',
                type: 'card',
                remember: true,
                redirects: false,
                element: 'card',
            },
            paymentElement: null,
            remember: false,
            isPaymentProcessing: false,
            errorMessage: '',
            period: 'MONTH',
            couponError: '',
            couponCode: '',
            coupon: null,
            loadingCoupon: false,
            seats: this.seatsNumber,
            planPrice: {
                month: 6,
                year: 60,
            },
        };
    },
    computed: {
        upgradePlanName() {
            return this.isCollaborativeBase ? 'Soar' : 'Ascend';
        },
        isCollaborativeBase() {
            return isActiveBaseCollaborative();
        },
        totalAmount() {
            return this.period === 'MONTH'
                ? this.monthlyAmountSeats
                : this.yearlyAmountSeats;
        },
        seatAmount() {
            return this.period === 'MONTH'
                ? this.monthlyAmount
                : this.yearlyAmount;
        },
        periodLabel() {
            const labelKey = this.period === 'MONTH' ? 'monthly' : 'yearly';
            return this.$t(`common.dates.suffixes.${labelKey}`);
        },
        monthlyAmount() {
            return this.applyDiscount(this.planPrice.month).toFixed(2);
        },
        monthlyAmountSeats() {
            return (this.monthlyAmount * this.seats).toFixed(2);
        },
        remainingString() {
            if (this.coupon && this.coupon.duration) {
                if (this.period === 'YEAR') {
                    if (this.coupon.duration < 12) {
                        const durationInYears = Math.ceil(this.coupon.duration / 12);
                        return `for ${durationInYears} year(s) and then $60 per year`;
                    }
                    const discountedYears = Math.floor(this.coupon.duration / 12);
                    const discountedMonths = this.coupon.duration % 12;
                    let remainingString = `for ${discountedYears} year(s)`;
                    if (discountedMonths) {
                        const discount = this.coupon.amountOff
                            ? this.coupon.amountOff / 100
                            : 60 * (this.coupon.percentOff / 100);
                        const partialYearAmount = (60 - discount * (discountedMonths / 12)).toFixed(2);
                        remainingString += ` and then $${partialYearAmount} for 1 year`;
                    }
                    return `${remainingString} and then $60 per year}`;
                }
                return `for ${this.coupon.duration} month(s) and then $6 per month`;
            }
            return '';
        },
        yearlyAmount() {
            return this.applyDiscount(this.planPrice.year).toFixed(2);
        },
        yearlyAmountSeats() {
            return (this.yearlyAmount * this.seats).toFixed(2);
        },
        isFullDiscount() {
            return this.coupon && this.coupon.percentOff === 100 && _.isNull(this.coupon.duration);
        },
    },
    methods: {
        applyDiscount(amount) {
            const coupon = this.coupon;
            if (!coupon) {
                return amount;
            }
            if (coupon.amountOff) {
                if (this.period === 'YEAR' && coupon.duration && coupon.duration < 12) {
                    return (amount - ((amount - (coupon.amountOff / 100)) * (coupon.duration / 12)));
                }
                return amount - (coupon.amountOff / 100);
            }
            const percentOff = coupon.percentOff / 100;
            if (this.period === 'YEAR' && coupon.duration && coupon.duration < 12) {
                return (amount - ((amount * percentOff) * (coupon.duration / 12)));
            }
            return (amount - (amount * percentOff));
        },
        configureStripeElements() {
            // Create the Stripe element based on the currently selected payment method...
            const elements = stripe.elements();

            this.paymentElement = elements.create('card');

            this.$nextTick(() => {
                // Clear the payment element first, otherwise Stripe Elements will emit a warning...
                document.getElementById('payment-element').innerHTML = '';

                this.paymentElement.mount('#payment-element');
            });
        },
        async getIntent() {
            const period = this.period;
            const updating = this.updating ? '1' : '0';
            const coupon = this.coupon?.code || '0';
            const { data: { intent } } = await axios.get(
                `/billing/intent?plan=${period}&update=${updating}&coupon=${coupon}`
            );
            return intent;
        },
        async setupCard() {
            const intent = await this.getIntent();

            const isSetup = intent.object === 'setup_intent';
            const method = isSetup ? 'confirmCardSetup' : 'confirmCardPayment';
            const response = await stripe[method](intent.client_secret, {
                payment_method: {
                    billing_details: { name: this.user.name, email: this.user.email },
                    card: this.paymentElement,
                },
            });

            const error = response.error;
            const setupIntent = response[isSetup ? 'setupIntent' : 'paymentIntent'];

            if (error) {
                throw error;
            }

            return setupIntent;
        },
        async upgrade() {
            if (this.couponCode) {
                this.couponError = 'Make sure to apply the promotional code first';
                return;
            }

            this.isPaymentProcessing = true;
            this.errorMessage = '';

            let response;
            try {
                let setupIntent = null;
                if (!this.isFullDiscount) {
                    setupIntent = await this.setupCard();
                }

                const method = (this.updating || this.renewing) ? 'put' : 'post';
                response = await axios[method]('/billing/subscription', {
                    coupon: this.coupon?.code,
                    plan: this.period,
                    paymentMethodId: setupIntent?.payment_method,
                });
                this.$saveFeedback();
            } catch (e) {
                this.errorMessage = e.message;
                const handledErrors = ['card_error', 'validation_error'];
                if (!handledErrors.includes(e.type)) {
                    throw e;
                } else {
                    return;
                }
            } finally {
                this.isPaymentProcessing = false;
            }
            this.$emit('subscribe');

            if (response.redirect) {
                window.location.href = response.redirect;
            }
        },
        async applyCoupon() {
            this.loadingCoupon = true;
            try {
                const { data: { data: coupon } } = await axios.get(
                    `/billing/coupon/${this.period}/${this.couponCode}`
                );
                this.coupon = coupon;
                this.couponError = '';
                this.couponCode = '';
            } catch (e) {
                if (e.response.status === 404) {
                    this.couponError = 'Coupon not found';
                } else {
                    throw e;
                }
            } finally {
                this.loadingCoupon = false;
            }
        },
        downgrade() {
            return axios.delete('/billing/subscription');
        },
    },
    mounted() {
        this.configureStripeElements();
    },
};
</script>
