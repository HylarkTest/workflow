<template>
    <button
        type="button"
        class="c-favorite-button centered"
    >
        <i
            class="fas fa-heart z-over text-cm-00 text-xs pointer-events-none"
            :class="{ 'text-violet-500': isFavorite }"
        >
        </i>
        <span
            class="h-full w-full rounded-full opacity-70 bg-cm-300 absolute hover:opacity-100 transition-2eio"
        >
        </span>
    </button>
</template>

<script>

export default {
    name: 'FavoriteButton',
    components: {

    },
    mixins: [
    ],
    props: {
        isFavorite: Boolean,
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

.c-favorite-button {
    height: 25px;
    width: 25px;

    @apply
        rounded-full
    ;
}

</style>
