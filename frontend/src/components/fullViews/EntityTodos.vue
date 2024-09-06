<template>
    <div class="o-entity-todos">
        <FeatureMain
            v-if="!isLoading"
            featureType="TODOS"
            :page="page"
            :currentView="currentView"
            :lists="todoLists"
            :integrations="integrations"
            :integrationLists="integrationLists"
            :renewals="renewals"
            :deleteListFunction="deleteListFunction"
            :featureStats="todoStats"
            :sourceLists="sourceLists"
            :createListFromObjectFunction="createListFromObjectFunction"
            :updateListFunction="updateListFunction"
            :createListFunction="createListFunction"
            :moveListFunction="moveListFunction"
            :forceResponsiveDisplay="true"
            :forceNoDrag="true"
            defaultFilter="all"
            :node="item"
            :defaultAssociations="[moddedItem]"
            :hasReducedPadding="true"
            :topHeaderClass="topHeaderClass"
        >
        </FeatureMain>
    </div>
</template>

<script>

import interactsWithEntityFeaturePanel from '@/vue-mixins/features/interactsWithEntityFeaturePanel.js';
import providesTodosPageEssentials from '@/vue-mixins/todos/providesTodosPageEssentials.js';

export default {
    name: 'EntityTodos',
    components: {
    },
    mixins: [
        providesTodosPageEssentials,
        interactsWithEntityFeaturePanel,
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

/*.o-entity-todos {

} */

</style>
