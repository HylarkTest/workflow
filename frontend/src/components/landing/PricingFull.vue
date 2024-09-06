<template>
    <div
        class="o-pricing-full"
    >
        <h2
            v-t="'landing.pricing.full.title'"
            class="o-pricing-full__title"
        >

        </h2>

        <div
            class="o-pricing-full__main"
            :class="{ 'o-pricing-full__main--partial': !showAll }"
        >
            <div>
                <PricingLine
                    class="mb-8"
                    :plans="plans"
                >
                    <template
                        #default="{ plan }"
                    >
                        <h4
                            v-t="'landing.pricing.plans.' + plan.id + '.title'"
                            class="o-pricing-full__plan"
                            :class="textClass(plan.color)"
                        >

                        </h4>

                        <router-link
                            v-t="plan.button"
                            class="button text-white inline-block z-over pointer-events-auto"
                            :class="buttonClasses(plan.color)"
                            :to="{ name: 'register.initial' }"
                        >
                        </router-link>
                    </template>
                </PricingLine>
            </div>

            <PricingFullSection
                v-for="section in sections"
                :key="section"
                class="mb-24"
                :section="section"
                :plans="plans"
            >
            </PricingFullSection>

            <div
                v-if="!showAll"
                class="o-pricing-full__overlay"
            >

            </div>
        </div>

        <div
            v-if="!showAll"
            class="flex justify-center"
        >
            <button
                v-t="'landing.pricing.full.seeAll'"
                type="button"
                class="button bg-azure-600 hover:bg-azure-500"
                @click="showAll = true"
            >
            </button>
        </div>
    </div>
</template>

<script>

import { plans, allFeatures } from '@/core/data/plans.js';

import PricingLine from '@/components/landing/PricingLine.vue';
import PricingFullSection from '@/components/landing/PricingFullSection.vue';

import interactsWithPricingLayout from '@/vue-mixins/landing/interactsWithPricingLayout.js';

// const sections = [
//     'fundamentals',
//     'customizations',
//     'product',
//     'collaboration',
//     'productivity',
//     'security',
//     'support',
// ];

export default {
    name: 'PricingFull',
    components: {
        PricingLine,
        PricingFullSection,
    },
    mixins: [
        interactsWithPricingLayout,
    ],
    props: {

    },
    data() {
        return {
            showAll: false,
        };
    },
    computed: {
        sections() {
            return _.map(allFeatures, 'id');
        },
    },
    methods: {

    },
    created() {
        this.plans = plans;
        // this.sections = sections;
    },
};
</script>

<style scoped>

.o-pricing-full {
    &__main {
        @apply
            relative
        ;

        &--partial {
            max-height: 500px;
            overflow: hidden;
        }
    }

    &__overlay {
        @apply
            absolute
            bg-gradient-to-t
            from-white
            h-full
            left-0
            pointer-events-none
            top-0
            w-full
            z-over
        ;
    }

    &__title {
        font-size: 30px;

        @apply
            font-bold
            mb-12
            text-center
        ;
    }

    &__plan {
        @apply
            font-bold
            mb-4
            text-2xl
        ;
    }
}

</style>
