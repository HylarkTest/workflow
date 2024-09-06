<template>
    <FeatureMain
        v-if="!isLoading"
        featureType="DOCUMENTS"
        :page="page"
        :lists="drives"
        :deleteListFunction="deleteListFunction"
        :featureStats="documentStats"
        :sourceLists="sourceLists"
        :createListFromObjectFunction="createListFromObjectFunction"
        :updateListFunction="updateListFunction"
        :createListFunction="createListFunction"
        :moveListFunction="moveListFunction"
        :forceResponsiveDisplay="true"
        :forceNoDrag="true"
        defaultFilter="all"
        :node="item"
        :defaultAssociations="defaultAssociations"
        :hasReducedPadding="true"
        :topHeaderClass="topHeaderClass"
        :enableFileDrop="true"
    >
    </FeatureMain>
</template>

<script>
import providesDocumentsPageEssentials from '@/vue-mixins/documents/providesDocumentsPageEssentials.js';
import interactsWithEntityFeaturePanel from '@/vue-mixins/features/interactsWithEntityFeaturePanel.js';

export default {
    name: 'EntityDocuments',
    components: {
    },
    mixins: [
        providesDocumentsPageEssentials,
        interactsWithEntityFeaturePanel,
    ],
    props: {
    },
    data() {
        return {
        };
    },
    computed: {
        featureType() {
            return 'DOCUMENTS';
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-entity-documents {

} */

</style>
