<template>
    <div class="c-label-header flex flex-wrap gap-2">
        <div class="flex items-center flex-1">
            <div
                v-if="labelPath"
                v-t="labelPath"
                class="c-label-header__label text-sm md:text-base"
            >
            </div>

            <h1
                class="header-1 text-primary-800 leading-none u-hyphen"
            >
                <slot>
                </slot>
            </h1>
        </div>

        <div
            class="shrink-0"
        >
            <slot name="extra">
            </slot>
        </div>
    </div>
</template>

<script>

export default {
    name: 'LabelHeader',
    components: {

    },
    mixins: [
    ],
    props: {
        labelPath: {
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

.c-label-header {
    @apply
        bg-primary-100
        flex
        items-center
        justify-between
        pb-6
        pt-4
        px-4
        rounded-t-2xl
    ;

    &__label {
        @apply
            bg-cm-00
            mr-3
            px-3
            py-1
            rounded-full
            text-primary-600
            uppercase
        ;
    }

    @media (min-width: 768px) {
        @apply
            pb-3
        ;
    }
}

</style>
