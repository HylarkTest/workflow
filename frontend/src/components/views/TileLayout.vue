<template>
    <DraggableList
        class="c-tile-layout"
        :items="items"
        itemKey="id"
        itemClass="w-full sm:w-1/2 lg:w-1/3 xl:w-1/4 p-2"
    >
        <template
            #item="{ item }"
        >
            <component
                :is="templateComponent"
                :dataValueObject="item"
                :dataStructure="validData"
                :page="page"
                :item="item"
                :showNecessaryLabels="true"
                :mapping="mapping"
                :isSummaryView="true"
            >
            </component>
        </template>
    </DraggableList>
</template>

<script>

// import TileItem from './TileItem.vue';
import interactsWithViewsLayouts from '@/vue-mixins/views/interactsWithViewsLayouts.js';

export default {
    name: 'TileLayout',
    components: {
        // TileItem,
    },
    mixins: [
        interactsWithViewsLayouts,
    ],
    props: {

    },
    data() {
        return {
            viewType: 'TILE',
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

.c-tile-layout {
    @apply
        flex
        flex-wrap
        -m-2
    ;
}

</style>
