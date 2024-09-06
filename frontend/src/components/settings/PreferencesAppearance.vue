<template>
    <div class="o-preferences-appearance">
        <SettingsHeaderLine>
            <template
                #header
            >
                {{ $t('settings.colorMode.colorMode') }}
            </template>

            <template
                #description
            >
                Select light or dark mode for your Hylark experience.
            </template>

            <ColorModeToggle
                :modelValue="colorMode"
                @update:modelValue="$emit('update:colorMode', $event)"
            >
            </ColorModeToggle>

        </SettingsHeaderLine>
    </div>
</template>

<script>

import ColorModeToggle from '@/components/buttons/ColorModeToggle.vue';

export default {
    name: 'PreferencesAppearance',
    components: {
        ColorModeToggle,
    },
    mixins: [
    ],
    props: {
        colorMode: {
            type: String,
            required: true,
            validate: (value) => value === 'LIGHT' || value === 'DARK',
        },
    },
    emits: [
        'update:colorMode',
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

/*.o-preferences-appearance {

} */

</style>
