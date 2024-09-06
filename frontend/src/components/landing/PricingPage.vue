<template>
    <div class="o-pricing-page landing-page-layout">
        <div class="o-pricing-page__main center">
            <h1
                v-t="'landing.pricing.title'"
                class="o-pricing-page__title"
            >
            </h1>

            <p
                v-t="'landing.pricing.subtitle'"
                class="o-pricing-page__subtitle"
            >
            </p>

            <CtaButton>
            </CtaButton>
        </div>

        <PricingSummary
            class="mb-24"
        >
        </PricingSummary>

        <PricingFull>
        </PricingFull>
    </div>
</template>

<script>

import PricingSummary from '@/components/landing/PricingSummary.vue';
import PricingFull from '@/components/landing/PricingFull.vue';
import CtaButton from '@/components/landing/CtaButton.vue';

export default {
    name: 'PricingPage',
    components: {
        PricingSummary,
        PricingFull,
        CtaButton,
    },
    mixins: [
    ],
    props: {

    },
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.o-pricing-page {
    &__main {
        height: 300px;

        @apply
            flex-col
            mb-10
        ;
    }

    &__title {
        font-size: 28px;

        @apply
            font-bold
            mb-4
        ;
    }

    &__subtitle {
        max-width: 600px;

        @apply
            leading-normal
            mb-2
            text-center
            text-gray-600
        ;
    }
}

</style>
