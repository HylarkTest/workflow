<template>
    <div
        class="c-notice-overlay top-0 left-0 centered"
        :class="selectedModeClasses"
    >
        <div
            class="absolute h-screen w-screen top-0 left-0 bg-cm-00 opacity-70"
        >
        </div>
        <div
            class="c-notice-overlay__box shadow-primary-600/30"
        >
            <slot>
            </slot>
        </div>
    </div>
</template>

<script>

const modeClasses = {
    FULL: 'fixed h-screen w-screen z-mega',
    CONTAINER: 'absolute h-full w-full z-cover',
};

export default {
    name: 'NoticeOverlay',
    components: {

    },
    mixins: [
    ],
    props: {
        mode: {
            type: String,
            default: 'FULL',
            validator(val) {
                return val.includes('FULL', 'CONTAINER');
            },
        },
    },
    data() {
        return {

        };
    },
    computed: {
        selectedModeClasses() {
            return modeClasses[this.mode];
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-notice-overlay {
    &__box {
        @apply
            bg-cm-00
            border
            border-primary-100
            border-solid
            m-8
            relative
            rounded-xl
            shadow-lg
            z-over
        ;
    }
}

</style>
