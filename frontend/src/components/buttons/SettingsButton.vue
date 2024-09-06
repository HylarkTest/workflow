<template>
    <button
        class="c-settings-button circle-center transition-2eio"
        type="button"
        @click="$emit('showSettings')"
    >
        <i
            class="fa-regular fa-sliders-simple text-sm"
        >
        </i>
    </button>
</template>

<script>

export default {
    name: 'SettingsButton',
    components: {

    },
    mixins: [
    ],
    props: {
    },
    emits: [
        'showSettings',
    ],
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

.c-settings-button {

    @apply
        absolute
        bg-cm-100
        h-6
        right-2
        text-cm-600
        top-2
        w-6
        z-over
    ;

    &:hover {
        @apply
            bg-primary-100
            text-primary-600
        ;
    }
}

</style>
