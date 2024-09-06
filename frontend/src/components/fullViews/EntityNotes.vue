<template>
    <div class="o-entity-notes">
        <FeatureMain
            v-if="!isLoading"
            featureType="NOTES"
            :page="page"
            :lists="notebooks"
            :deleteListFunction="deleteListFunction"
            :featureStats="noteStats"
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
import providesNotesPageEssentials from '@/vue-mixins/notes/providesNotesPageEssentials.js';

export default {
    name: 'EntityNotes',
    components: {
    },
    mixins: [
        interactsWithEntityFeaturePanel,
        providesNotesPageEssentials,
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

/*.o-entity-notes {

} */

</style>
