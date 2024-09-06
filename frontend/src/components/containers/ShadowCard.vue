<template>
    <div
        class="c-shadow-card"
        :class="icon ? 'pr-8' : 'pr-3'"
    >
        <slot>
        </slot>

        <div
            v-if="icon"
            class="c-shadow-card__icon"
            :class="iconClasses"
        >
            <i
                class="fal"
                :class="icon"
            >
            </i>
        </div>
    </div>
</template>

<script>

import providesColors from '@/vue-mixins/style/providesColors.js';

export default {
    name: 'ShadowCard',
    components: {

    },
    mixins: [
        providesColors,
    ],
    props: {
        icon: {
            type: String,
            default: '',
        },
        color: {
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        iconClasses() {
            return [this.getTextColor(this.color, '700'), this.getBgColor(this.color, '100')];
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-shadow-card {
    @apply
        pl-3
        py-3
        relative
        rounded-lg
    ;

    &__icon {
        @apply
            absolute
            flex
            h-7
            items-center
            justify-center
            right-0
            rounded-tr-lg
            top-0
            w-7
        ;
    }
}

</style>
