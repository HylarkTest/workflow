<template>
    <component
        :is="layoutComponent"
        class="o-notes-page"
        v-bind="layoutProps"
    >
        <FeatureMain
            v-if="!isLoading"
            :lists="notebooks"
            :featureStats="noteStats"
            v-bind="featureMainProps"
        >
        </FeatureMain>
    </component>
</template>

<script>

import interactsWithFeaturePage from '@/vue-mixins/features/interactsWithFeaturePage.js';
import providesNotesPageEssentials from '@/vue-mixins/notes/providesNotesPageEssentials.js';

export default {
    name: 'NotesPage',
    components: {
    },
    mixins: [
        interactsWithFeaturePage,
        providesNotesPageEssentials,
    ],
    props: {

    },
    data() {
        return {

        };
    },
    computed: {
        featureType() {
            return 'NOTES';
        },
        historyPageType() {
            return 'Note';
        },
    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>

/*
.o-notes-page {

}
 */

</style>
