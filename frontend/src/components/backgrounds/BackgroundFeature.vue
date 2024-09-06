<template>
    <div class="c-background-feature">
        <HalfCircle
            class="c-background-feature__half"
            flat="TOP"
        >
        </HalfCircle>

        <HalfCircle
            class="c-background-feature__accent"
            flat="RIGHT"
        >
        </HalfCircle>
    </div>
</template>

<script>

export default {
    name: 'BackgroundFeature',
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

.c-background-feature {
    &__accent {
        height: 40vh;
        width: 20vh;

        @apply
            absolute
            bg-secondary-200
            opacity-80
            right-0
            top-16
        ;
    }

    &__half {
        height: 110px;
        width: 220px;

        @apply
            absolute
            bg-primary-200
            left-8
            opacity-70
            top-0
        ;
    }
}

</style>
