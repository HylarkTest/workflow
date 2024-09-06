<template>
    <div class="o-preview-tile centered">
        <component
            :is="templateComponent"
            class="o-preview-tile__tile"
            :showNecessaryLabels="true"
            v-bind="$attrs"
        >
        </component>
    </div>
</template>

<script>

export default {
    name: 'PreviewTile',
    components: {

    },
    mixins: [
    ],
    props: {
        templateComponent: {
            type: String,
            required: true,
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

.o-preview-tile {
    &__tile {
        width: 400px;
    }
}

</style>
