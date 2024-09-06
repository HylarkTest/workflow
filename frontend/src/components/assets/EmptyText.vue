<template>
    <div class="c-empty-text">

        <i
            class="mb-6 text-cm-400 text-6xl"
            :class="icon"
        >
        </i>

        <p class="font-semibold mb-3 text-cm-600">
            {{ title }}
        </p>

        <p class="leading-snug text-cm-600 text-sm">
            {{ subtitle }}
        </p>
    </div>
</template>

<script>
export default {

    name: 'EmptyText',
    components: {

    },
    mixins: [
    ],
    props: {
        title: {
            type: String,
            default: '',
        },
        subtitle: {
            type: String,
            default: '',
        },
        icon: {
            type: String,
            default: '',
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
.c-empty-text {
    @apply
        max-w-sm
        mx-auto
        my-12
        text-center
    ;
}
</style>
