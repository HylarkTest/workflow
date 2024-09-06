<template>
    <DraggableList
        class="c-kanban-layout"
        :items="items"
        itemClass="mb-2 last:mb-0"
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

import KanbanItem from './KanbanItem.vue';

import interactsWithViewsLayouts from '@/vue-mixins/views/interactsWithViewsLayouts.js';

export default {
    name: 'KanbanLayout',
    components: {
        KanbanItem,
    },
    mixins: [
        interactsWithViewsLayouts,
    ],
    props: {

    },
    data() {
        return {
            viewType: 'KANBAN',
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

/*.c-kanban-layout {
}*/

</style>
