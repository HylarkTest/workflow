<template>
    <div class="c-background-general">
        <QuarterCircle
            class="c-background-general__accent"
            point="top-left"
        >
        </QuarterCircle>

        <HalfCircle
            class="c-background-general__half"
            flat="RIGHT"
        >
        </HalfCircle>
    </div>
</template>

<script>

export default {
    name: 'BackgroundGeneral',
    components: {

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

.c-background-general {
    &__accent {
        height: 20vh;
        width: 20vh;

        @apply
            absolute
            bg-secondary-200
            left-0
            opacity-90
            top-0
        ;
    }

    &__half {
        height: 40vh;
        width: 20vh;

        @apply
            absolute
            bg-primary-300
            bottom-12
            opacity-30
            right-0
        ;
    }
}

</style>
