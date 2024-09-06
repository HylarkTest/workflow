<template>
    <component
        :is="layoutComponent"
        class="o-attachments-page"
        v-bind="layoutProps"
    >
        <FeatureMain
            v-if="!isLoading"
            :lists="drives"
            :featureStats="documentStats"
            :enableFileDrop="true"
            v-bind="featureMainProps"
        >
        </FeatureMain>
    </component>
</template>

<script>
import providesDocumentsPageEssentials from '@/vue-mixins/documents/providesDocumentsPageEssentials.js';
import interactsWithFeaturePage from '@/vue-mixins/features/interactsWithFeaturePage.js';

export default {
    name: 'DocumentsPage',
    components: {
    },
    mixins: [
        providesDocumentsPageEssentials,
        interactsWithFeaturePage,
    ],
    props: {

    },
    data() {
        return {
            modalProps: {},
        };
    },
    computed: {
        featureType() {
            return 'DOCUMENTS';
        },
        historyPageType() {
            return 'Document';
        },
    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>

/*.o-attachments-page {
}
*/
</style>
