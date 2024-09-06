<template>
    <component
        :is="outerEl"
        class="c-info-box shadow-secondary-500/50"
    >

        <QuarterCircle
            class="c-info-box__shape c-info-box__shape--1"
            point="top-right"
            oppositeRadius="0.5rem"
        >

        </QuarterCircle>

        <QuarterCircle
            class="c-info-box__shape c-info-box__shape--2"
            point="top-right"
            oppositeRadius="0.5rem"
        >

        </QuarterCircle>

        <div
            class="relative"
        >
            <slot>
            </slot>
        </div>
    </component>
</template>

<script>

export default {
    name: 'InfoBox',
    components: {

    },
    mixins: [
    ],
    props: {
        color: {
            type: String,
            default: 'gold',
        },
        outerEl: {
            type: String,
            default: 'div',
        },
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

.c-info-box {
    @apply
        bg-secondary-100
        relative
        rounded-xl
        shadow-md
    ;

    &__shape {
        height: 60%;
        width:  60%;

        @apply
            absolute
            right-0
            top-0
            z-0
        ;

        &--1 {
            height: 60%;
            width:  60%;

            @apply
                bg-secondary-200
            ;
        }

        &--2 {
            height: 40%;
            width:  40%;

            @apply
                bg-secondary-300
            ;
        }
    }
}

</style>
