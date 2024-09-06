<template>
    <div class="o-pricing-full-section">
        <h5 class="text-xl font-bold mb-10">
            {{ $t(sectionPath + '.title') }}
        </h5>

        <PricingLine
            v-for="item in featuresList"
            :key="item.id"
            class="o-pricing-full-section__band"
            :plans="plans"
            :showInfo="true"
        >
            <template
                #description
            >
                {{ $t(sectionPath + '.' + item.id + '.title') }}
            </template>

            <template
                v-if="item.comingSoon"
                #tag
            >
                <div
                    v-t="'common.comingSoon'"
                    class="o-pricing-full-section__soon tag-sm bg-azure-600"
                >

                </div>
            </template>

            <template
                #explanation
            >
                {{ $t(sectionPath + '.' + item.id + '.explanation') }}
            </template>

            <template
                #default="slotProps"
            >
                <div
                    v-if="isBooleanData(slotProps.plan[section][item.id])"
                    class="text-lg"
                    :class="textClass(slotProps.plan.color)"
                >
                    <i
                        v-if="slotProps.plan[section][item.id]"
                        class="fas fa-check-circle"
                    >
                    </i>
                </div>

                <div
                    v-else
                    class="text-sm text-gray-800"
                >
                    {{ slotProps.plan[section][item.id] }}
                </div>
            </template>
        </PricingLine>

    </div>
</template>

<script>

import { allFeatures } from '@/core/data/plans.js';

import PricingLine from '@/components/landing/PricingLine.vue';

import interactsWithPricingLayout from '@/vue-mixins/landing/interactsWithPricingLayout.js';

export default {
    name: 'PricingFullSection',
    components: {
        PricingLine,
    },
    mixins: [
        interactsWithPricingLayout,
    ],
    props: {
        section: {
            type: String,
            required: true,
        },
        plans: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        featuresObj() {
            return _.find(allFeatures, { id: this.section });
        },
        featuresList() {
            return this.featuresObj?.features;
        },
        sectionPath() {
            return `landing.pricing.full.sections.${this.section}`;
        },
    },
    methods: {
        isBooleanData(value) {
            return _.isBoolean(value);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-pricing-full-section {
    &__band:nth-child(odd) {
        @apply
            bg-gray-100
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
}

</style>
