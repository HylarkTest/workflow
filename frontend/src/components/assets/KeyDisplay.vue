<template>
    <div class="c-key-display center">
        {{ symbol }}
    </div>
</template>

<script>

export default {
    name: 'KeyDisplay',
    components: {

    },
    mixins: [
    ],
    props: {
        symbol: {
            type: String,
            required: true,
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

.c-key-display {
    @apply
        bg-cm-200
        font-normal
        h-4
        rounded
        text-cm-700
        text-xs
        w-4
    ;
}

</style>
