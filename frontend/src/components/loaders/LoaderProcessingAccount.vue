<template>
    <div class="c-loader-processing-account centered">
        <div class="bg-cm-00 opacity-75 w-full h-full absolute top-0 left-0">
        </div>
        <slot>
        </slot>
    </div>
</template>

<script>

export default {
    name: 'LoaderProcessingAccount',
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

.c-loader-processing-account {
    @apply
        bottom-0
        fixed
        h-full
        left-0
        pointer-events-none
        w-full
        z-mega
    ;
}

</style>
