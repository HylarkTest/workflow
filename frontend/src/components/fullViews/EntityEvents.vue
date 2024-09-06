<template>
    <div
        class="o-entity-events"
    >
        <FeatureMain
            v-if="!isLoading"
            featureType="EVENTS"
            :page="page"
            :currentView="currentView"
            :lists="calendars"
            :integrations="integrations"
            :integrationLists="integrationLists"
            :renewals="renewals"
            :deleteListFunction="deleteListFunction"
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
import providesEventsPageEssentials from '@/vue-mixins/events/providesEventsPageEssentials.js';

export default {
    name: 'EntityEvents',
    components: {
    },
    mixins: [
        interactsWithEntityFeaturePanel,
        providesEventsPageEssentials,
    ],
    props: {
    },
    emits: [
    ],
    apollo: {
    },
    data() {
        return {
            currentView: {
                id: 'LINE',
                viewType: 'LINE',
            },
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

/*.o-entity-events {
} */

</style>
