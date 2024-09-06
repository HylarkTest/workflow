<template>
    <div class="o-feature-item">
        <div
            class="o-feature-item__box flex justify-center items-center"
            :class="getBgColor(color, '600')"
        >
            <i
                class="far"
                :class="icon"
            >
            </i>
        </div>

        <div
            v-if="includeText"
            class="flex-1 ml-2 text-sm"
        >
            <p
                v-t="title"
                class="o-feature-item__title"
            >
            </p>

            <p
                v-t="description"
                class="o-feature-item__description"
            >
            </p>
        </div>
    </div>
</template>

<script>

import { featureIcons } from '@/core/display/featureIcons.js';

import providesColors from '@/vue-mixins/style/providesColors.js';

export default {
    name: 'FeatureItem',
    components: {

    },
    mixins: [
        providesColors,
    ],
    props: {
        feature: {
            type: Object,
            required: true,
        },
        includeText: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        type() {
            return this.feature.val;
        },
        featureObj() {
            return featureIcons[this.type];
        },
        icon() {
            return this.featureObj?.icon;
        },
        color() {
            return this.featureObj?.color;
        },
        title() {
            return `features.${_.camelCase(this.type)}.title`;
        },
        description() {
            return `features.${_.camelCase(this.type)}.description`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.o-feature-item {
    @apply
        flex
    ;

    &__box {
        height: 40px;
        width: 40px;

        @apply
            rounded-lg
            text-cm-00
            text-xl
        ;
    }

    &__title {
        @apply
            font-semibold
        ;
    }
}

</style>
