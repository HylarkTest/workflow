<template>
    <div class="o-general-appearance">
        <SettingsHeaderLine
            class="mb-10"
        >
            <template
                #header
            >
                Base theme
            </template>

            <AppearanceAccent
                :modelValue="accentColor"
                @update:modelValue="$emit('update:accentColor', $event)"
            >
            </AppearanceAccent>

        </SettingsHeaderLine>
    </div>
</template>

<script>

import AppearanceAccent from './AppearanceAccent.vue';

export default {
    name: 'GeneralAppearance',
    components: {
        AppearanceAccent,
    },
    mixins: [
    ],
    props: {
        accentColor: {
            type: String,
            required: true,
        },
    },
    emits: [
        'update:accentColor',
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

/*.o-general-appearance {

} */

</style>
