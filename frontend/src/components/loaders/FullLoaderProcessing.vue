<template>
    <div
        class="c-full-loader-processing centered flex-col z-cover"
        :class="positionClass"
    >
        <div class="absolute opacity-75 bg-cm-00 w-full h-full">

        </div>

        <!-- Add margin to the slot when using for spacing -->
        <slot>
        </slot>
        <LoaderProcessing
            class="z-over relative"
            :class="loaderClass"
            :heightWidthClass="heightWidthClass"
        >
        </LoaderProcessing>

        <slot name="post">
        </slot>
    </div>
</template>

<script>

import LoaderProcessing from '@/components/loaders/LoaderProcessing.vue';

export default {
    name: 'FullLoaderProcessing',
    components: {
        LoaderProcessing,
    },
    mixins: [
    ],
    props: {
        positionClass: {
            type: String,
            default: 'fixed',
        },
        loaderClass: {
            type: String,
            default: '',
        },
        heightWidthClass: {
            type: String,
            default: 'h-8 w-8 mx-2',
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

.c-full-loader-processing {
    @apply
        bottom-0
        h-full
        left-0
        pointer-events-none
        w-full
    ;
}

</style>
