<template>
    <div class="c-top-bar">
        <div
            class="c-top-bar__circle circle-center"
            @click="$emit('back')"
        >
            <i class="fal fa-arrow-left"></i>
        </div>
        <h1 class="font-semibold text-xl">
            <slot></slot>
        </h1>
    </div>
</template>

<script>
export default {

    name: 'TopBar',
    components: {

    },
    mixins: [
    ],
    props: {

    },
    emits: ['back'],
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
.c-top-bar {
    @apply
        bg-primary-100
        flex
        items-center
        p-6
        w-full
    ;

    &__circle {
        @apply
            bg-cm-00
            cursor-pointer
            h-8
            mr-8
            text-primary-600
            w-8
        ;

        &:hover {
            @apply
                bg-primary-600
                text-cm-00
            ;
        }
    }
}
</style>
