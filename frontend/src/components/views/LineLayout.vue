<template>
    <div class="c-line-layout">
        <DraggableList
            :items="items"
            itemKey="id"
        >
            <template
                #item="{ item }"
            >
                <div class="mb-2">
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
                </div>
            </template>
        </DraggableList>
    </div>
</template>

<script>

import LineItem from './LineItem.vue';
import interactsWithViewsLayouts from '@/vue-mixins/views/interactsWithViewsLayouts.js';

export default {
    name: 'LineLayout',
    components: {
        LineItem,
    },
    mixins: [
        interactsWithViewsLayouts,
    ],
    props: {

    },
    data() {
        return {
            viewType: 'LINE',
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

/*
.c-line-layout {

}
*/

</style>
