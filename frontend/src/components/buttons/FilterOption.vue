<template>
    <ButtonEl
        class="c-filter-option transition-2eio"
        :class="colorClasses"
    >
        <div class="flex items-center">
            <div
                class="c-filter-option__circle circle-center"
                :class="circleClasses"
            >
                <i
                    class="far"
                    :class="iconClasses"
                >
                </i>
            </div>

            <span
                v-t="option.textPath"
                class="font-medium text-cm-800 text-sm"
            >
            </span>
        </div>

        <div
            v-if="filterCount"
            class="c-filter-option__count centered"
        >
            {{ filterCount }}
        </div>
    </ButtonEl>
</template>

<script>

import providesColors from '@/vue-mixins/style/providesColors.js';

export default {
    name: 'FilterOption',
    components: {

    },
    mixins: [
        providesColors,
    ],
    props: {
        option: {
            type: Object,
            required: true,
        },
        filterCount: {
            type: Number,
            required: true,
        },
        isActive: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        color() {
            return this.option.color;
        },
        colorClasses() {
            return this.isActive ? this.getBgColor(this.color, 50) : 'hover:bg-cm-100';
        },
        iconClasses() {
            return [this.option.icon, this.getTextColor(this.color, 700)];
        },
        circleClasses() {
            return [this.getBgColor(this.color, 100), this.getShadowColor(this.color)];
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-filter-option {
    @apply
        flex
        items-center
        justify-between
        my-0.5
        p-2
        rounded-lg
    ;

    &__circle {
        @apply
            h-7
            mr-3
            shadow
            text-xs
            w-7
        ;
    }

    &__count {
        height:  21px;
        width: 21px;

        @apply
            bg-cm-200
            font-semibold
            rounded-md
            text-cm-500
            text-xs
        ;
    }
}

</style>
