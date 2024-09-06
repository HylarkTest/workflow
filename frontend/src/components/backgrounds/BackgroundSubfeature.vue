<template>
    <div class="c-background-subfeature">
        <QuarterCircle
            class="c-background-subfeature__accent"
            point="bottom-right"
        >
        </QuarterCircle>

        <HalfCircle
            class="c-background-subfeature__half"
            flat="LEFT"
        >
        </HalfCircle>
    </div>
</template>

<script>

export default {
    name: 'BackgroundSubfeature',
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

.c-background-subfeature {
    &__accent {
        height: 20vh;
        width: 20vh;

        @apply
            absolute
            bg-secondary-200
            bottom-0
            opacity-90
            right-0
        ;
    }

    &__half {
        height: 40vh;
        width: 20vh;

        @apply
            absolute
            bg-primary-300
            left-0
            opacity-30
            top-12
        ;
    }
}

</style>
