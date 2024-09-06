<template>
    <div
        class="c-hylark-simplified"
    >
        <QuarterCircle
            class="c-hylark-simplified__beak"
            :class="beakColor"
        >

        </QuarterCircle>
        <QuarterCircle
            class="c-hylark-simplified__body"
            :class="bodyColor"
            point="bottom-left"
        >

        </QuarterCircle>
    </div>
</template>

<script>

export default {
    name: 'HylarkSimplified',
    components: {

    },
    mixins: [
    ],
    props: {
        beakColor: {
            type: String,
            default: 'bg-gold-400',
        },
        bodyColor: {
            type: String,
            default: 'bg-azure-600',
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

.c-hylark-simplified {
    @apply
        flex
        flex-col
        justify-end
    ;

    &__beak {
        height: 30%;
        transition:  background-color 0.2s ease-in-out;
        width: 30%;

        @apply
            absolute
            right-0
            top-0
        ;
    }

    &__body {
        height: 100%;
        transition:  background-color 0.2s ease-in-out;
        width: 100%;
    }
}

</style>
