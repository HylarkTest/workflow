<template>
    <div class="o-entity-links">
        <FeatureMain
            v-if="!isLoading"
            featureType="LINKS"
            :page="page"
            :lists="lists"
            :deleteListFunction="deleteListFunction"
            :featureStats="linkStats"
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
import providesLinksPageEssentials from '@/vue-mixins/links/providesLinksPageEssentials.js';

export default {
    name: 'EntityLinks',
    components: {
    },
    mixins: [
        providesLinksPageEssentials,
        interactsWithEntityFeaturePanel,
    ],
    props: {
    },
    apollo: {
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

/*.o-entity-links {

} */

</style>
