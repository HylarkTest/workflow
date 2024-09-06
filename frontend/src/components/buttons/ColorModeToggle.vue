<template>
    <div class="c-color-mode-toggle">
        <IconToggle
            :options="modeOptions"
            v-bind="$attrs"
        >
        </IconToggle>
    </div>
</template>

<script>

import IconToggle from './IconToggle.vue';

const modeOptions = [
    {
        val: 'LIGHT',
        icon: 'fal fa-sun',
        langPath: 'settings.colorMode.light',
    },
    {
        val: 'DARK',
        icon: 'fal fa-moon-stars',
        langPath: 'settings.colorMode.dark',
    },
];

export default {
    name: 'ColorModeToggle',
    components: {
        IconToggle,
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
        this.modeOptions = modeOptions;
    },
};
</script>

<style scoped>

.c-color-mode-toggle {
    @apply
        inline-flex
        relative
    ;
}

</style>
