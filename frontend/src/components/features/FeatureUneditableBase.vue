<template>
    <div class="o-feature-uneditable-base">
        <div class="mb-6">
            <label
                v-t="'labels.name'"
                class="header-form mb-3"
            >
            </label>
            <div>{{ featureItem.name }}</div>
        </div>

        <slot></slot>
    </div>
</template>

<script>

export default {
    name: 'FeatureUneditableBase',
    components: {
    },
    mixins: [
    ],
    props: {
        featureItem: {
            type: Object,
            default: () => {
                return {};
            },
        },
    },
    emits: [
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
/* .o-feature-uneditable-base {

} */
</style>
