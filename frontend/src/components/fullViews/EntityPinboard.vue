<template>
    <div class="o-entity-pinboard">
        <FeatureMain
            v-if="!isLoading"
            featureType="PINBOARD"
            :page="page"
            :lists="pinboards"
            :deleteListFunction="deleteListFunction"
            :featureStats="pinboardStats"
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
            :enableFileDrop="true"
        >
        </FeatureMain>
    </div>
</template>

<script>

import interactsWithEntityFeaturePanel from '@/vue-mixins/features/interactsWithEntityFeaturePanel.js';
import providesPinboardPageEssentials from '@/vue-mixins/pinboard/providesPinboardPageEssentials.js';

export default {
    name: 'EntityPinboard',
    components: {
    },
    mixins: [
        providesPinboardPageEssentials,
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

/*.o-entity-pinboard {

} */

</style>
