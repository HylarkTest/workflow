<template>
    <div class="c-background-entities">
        <HalfCircle
            class="c-background-entities__accent"
            flat="LEFT"
        >
        </HalfCircle>

        <HalfCircle
            class="c-background-entities__half"
            flat="TOP"
        >
        </HalfCircle>
    </div>
</template>

<script>

export default {
    name: 'BackgroundEntities',
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

.c-background-entities {
    &__accent {
        height: 30vh;
        width: 15vh;

        @apply
            absolute
            bg-secondary-200
            bottom-10
            left-0
            opacity-100
        ;
    }

    &__half {
        height: 20vh;
        width: 40vh;

        @apply
            absolute
            bg-primary-300
            opacity-50
            right-10
            top-0
        ;
    }
}

</style>
