<template>
    <div class="o-entity-info">
        <EntityMarkers
            v-if="markerGroupsLength"
            class="mb-8"
            :mapping="mapping"
            :item="item"
        >
        </EntityMarkers>

        <FullInfo
            :item="item"
            :page="page"
            :mapping="mapping"
        >
        </FullInfo>
    </div>
</template>

<script>
export default {
    name: 'EntityInfo',
    components: {

    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
        page: {
            type: [Object, null],
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        markerGroupsLength() {
            return this.mapping.markerGroups?.length;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.o-entity-info {

} */

</style>
