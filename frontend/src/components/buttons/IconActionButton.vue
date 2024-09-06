<template>
    <button
        class="c-icon-action-button"
        :class="buttonClasses"
        type="button"
        :title="textString"
    >
        <i
            :class="buttonSpecs.icon"
        >
        </i>

        <span
            v-if="showText"
            class="ml-1"
        >
            {{ textString }}
        </span>
    </button>
</template>

<script>

import providesColors from '@/vue-mixins/style/providesColors.js';

export default {
    name: 'IconActionButton',
    components: {

    },
    mixins: [
        providesColors,
    ],
    props: {
        showText: Boolean,
        size: {
            type: String,
            default: 'base',
        },
        buttonSpecs: {
            type: Object,
            required: true,
            validator(val) {
                return _.has(val, 'bgColor')
                    || _.has(val, 'colorClasses')
                    || _.has(val, 'icon');
            },
        },
        shape: {
            type: String,
            default: 'box',
        },
        textPath: {
            type: String,
            default: 'common.edit',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        buttonClasses() {
            return [
                `c-icon-action-button--${this.size}`,
                this.colorClasses,
                `c-icon-action-button--${this.shape}`,
            ];
        },
        color() {
            return this.buttonSpecs.bgColor;
        },
        colorOverride() {
            return this.buttonSpecs.colorClasses;
        },
        colorClasses() {
            if (this.colorOverride) {
                return this.colorOverride;
            }
            return `${this.getBgColor(this.color)} ${this.getHoverBgColor(this.color)} text-cm-00`;
        },
        textString() {
            return this.$t(this.textPath);
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-icon-action-button {
    transition: background-color 0.2s ease-in-out;

    @apply
        flex
        items-center
        justify-center
    ;

    &--box {
        @apply
            rounded
        ;
    }

    &--circle {
        @apply
            rounded-full
        ;
    }

    &--base {
        @apply
            h-6
            px-2
        ;
    }

    &--sm {
        font-size: 13px;
        height: 19px;
        width: 19px;

        @apply
            p-1
        ;
    }

    &--xs {
        font-size: 11px;
        height: 17px;
        min-width: 17px;
        width: 17px;
    }

    /*&:hover {
        @apply
            shadow
        ;
    }*/
}

</style>
