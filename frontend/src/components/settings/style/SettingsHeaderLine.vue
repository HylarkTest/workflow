<template>
    <div class="o-settings-header-line">
        <h5
            class="header-3"
        >
            <slot
                name="header"
            >
            </slot>
        </h5>
        <p
            v-if="$slots.description"
            class="mt-1 text-sm text-cm-600"
        >
            <slot
                name="description"
            >
            </slot>
        </p>

        <div class="mt-2">
            <slot>
            </slot>
        </div>
    </div>
</template>

<script>

export default {
    name: 'SettingsHeaderLine',
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

/*.o-settings-header-line {

} */

</style>
