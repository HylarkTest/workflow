<template>
    <div
        class="o-pricing-summary"
    >
        <div class="o-pricing-summary__periods">
            <OptionsToggle
                v-model="period"
                :options="periodButtons"
                :display="periodSelectionDisplay"
            >
            </OptionsToggle>
        </div>

        <div
            class="o-pricing-summary__container"
        >
            <div
                v-for="plan in plans"
                :key="plan.id"
                class="o-pricing-summary__column"
            >
                <div
                    class="o-pricing-summary__accent"
                    :class="bgClass(plan.color)"
                >
                </div>

                <div
                    v-if="plan.bubble"
                    v-t="'landing.pricing.plans.' + plan.id + '.bubble'"
                    class="o-pricing-summary__bubble"
                    :class="bgClass(plan.color, '200')"
                >

                </div>

                <div
                    class="o-pricing-summary__section"
                >
                    <h3
                        v-t="'landing.pricing.plans.' + plan.id + '.title'"
                        class="o-pricing-summary__title"
                    >
                    </h3>

                    <div class="mb-6">
                        <div
                            v-if="plan.free"
                            class="o-pricing-summary__price"
                        >
                            {{ $t('common.free') }}
                        </div>

                        <div
                            v-else
                            class="o-pricing-summary__line"
                        >
                            <div
                                class="o-pricing-summary__price mr-2"
                                :class="textClass(plan.color)"
                            >
                                {{ currency }}{{ plan.price[period] }}
                            </div>
                            <div>
                                {{ periodDisplay }}
                            </div>
                        </div>
                    </div>

                    <router-link
                        v-t="plan.button"
                        class="button mb-10 inline-block"
                        :class="buttonClasses(plan.color)"
                        :to="{ name: 'register.initial' }"
                    >
                    </router-link>

                    <p
                        v-t="'landing.pricing.plans.' + plan.id + '.intro'"
                        class="o-pricing-summary__intro"
                    >
                    </p>

                </div>

                <div class="o-pricing-summary__section">
                    <p
                        v-t="plan.includes"
                        class="font-semibold mb-6"
                    >
                    </p>

                    <ul
                        class="text-sm text-gray-700"
                    >
                        <li
                            v-for="(feature, index) in plan.features"
                            :key="index"
                            class="o-pricing-summary__feature"
                        >

                            <p
                                v-t="feature.text"
                            >
                            </p>

                            <div
                                v-if="feature.comingSoon"
                                v-t="'common.comingSoon'"
                                class="o-pricing-summary__soon tag-sm"
                                :class="bgClass(plan.color)"
                            >

                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="o-pricing-summary__future bg-emerald-100"
            >
                <h3
                    v-t="'landing.pricing.future.title'"
                    class="o-pricing-summary__title"
                >
                </h3>

                <div class="mb-16 text-sm text-gray-700">
                    <ul>
                        <li
                            v-for="feature in futureFeatures"
                            :key="feature"
                            class="o-pricing-summary__upcoming"
                        >
                            <i
                                class="fal fa-feather mr-4"
                            >
                            </i>

                            <span
                                v-t="'landing.pricing.future.features.' + feature"
                            >
                            </span>
                        </li>
                    </ul>

                    <p>
                    </p>
                </div>

                <p
                    v-t="'landing.pricing.future.connection'"
                    class="o-pricing-summary__connection"
                >
                </p>

                <button
                    v-t="'common.subscribe'"
                    type="button"
                    class="button mb-10 inline-block border-emerald-600 text-emerald-600"
                    :class="hoverBgClass('emerald', '200')"
                >
                </button>

            </div>

        </div>
    </div>
</template>

<script>

import { plans } from '@/core/data/plans.js';

import OptionsToggle from '@/components/inputs/OptionsToggle.vue';

import interactsWithPricingLayout from '@/vue-mixins/landing/interactsWithPricingLayout.js';

const futureFeatures = [
    'teams',
    'integrations',
    'automation',
    'imports',
    'reporting',
    'analytics',
];

const periodButtons = [
    'monthly',
    'yearly',
];

export default {
    name: 'PricingSummary',
    components: {
        OptionsToggle,
    },
    mixins: [
        interactsWithPricingLayout,
    ],
    props: {

    },
    data() {
        return {
            period: 'monthly',
        };
    },
    computed: {
        currency() {
            return '£';
        },
        periodDisplay() {
            const lang = this.$t(`landing.pricing.period.${this.period}`);
            return `/ ${lang}`;
        },
    },
    methods: {

    },
    created() {
        this.plans = plans;
        this.futureFeatures = futureFeatures;

        this.periodButtons = periodButtons;
        this.periodSelectionDisplay = (option) => this.$t(`common.dates.${option}`);
    },
};
</script>

<style scoped>

.o-pricing-summary {
    &__periods {
        @apply
            flex
            justify-end
            mb-6
        ;
    }

    &__container {
        @apply
            flex
            justify-center
        ;
    }

    &__column {
        width: 300px;

        @apply
            border
            border-gray-400
            border-solid
            mx-2
            relative
            rounded-lg
        ;
    }

    &__future {
        width: 300px;

        @apply
            mx-2
            px-6
            py-10
            relative
            rounded-lg
        ;
    }

    &__accent {
        @apply
            h-2
            rounded-t-lg
            w-full
        ;
    }

    &__section {
        @apply
            px-6
            py-10
            relative
        ;
    }

    &__title {
        @apply
            font-bold
            mb-8
            text-xl
        ;
    }

    &__price {
        font-size: 50px;

        @apply
            font-semibold
        ;
    }

    &__line {
        @apply
            flex
            items-end
        ;
    }

    &__bubble {
        right: -10px;
        top: -14px;

        @apply
            absolute
            font-semibold
            p-3
            rounded-lg
            text-center
            text-sm
        ;
    }

    &__intro {
        @apply
            leading-normal
            text-sm
        ;
    }

    &__feature {
        @apply
            flex
            items-start
            justify-between
            leading-snug
            my-4
        ;
    }

    &__soon {
        @apply
            flex-shrink-0
            font-bold
            rounded
            text-white
        ;
    }

    &__upcoming {
        @apply
            flex
            items-center
            leading-snug
            my-2
        ;
    }

    &__connection {
        @apply
            leading-snug
            mb-4
        ;
    }
}

</style>
