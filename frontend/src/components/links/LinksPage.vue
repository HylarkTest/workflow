<template>
    <component
        :is="layoutComponent"
        class="o-links-page"
        v-bind="layoutProps"
    >
        <FeatureMain
            v-if="!isLoading"
            :lists="lists"
            :featureStats="linkStats"
            v-bind="featureMainProps"
        >
        </FeatureMain>
    </component>
</template>

<script>

import interactsWithFeaturePage from '@/vue-mixins/features/interactsWithFeaturePage.js';
import providesLinksPageEssentials from '@/vue-mixins/links/providesLinksPageEssentials.js';

export default {
    name: 'LinksPage',
    components: {
    },
    mixins: [
        interactsWithFeaturePage,
        providesLinksPageEssentials,
    ],
    props: {

    },
    data() {
        return {

        };
    },
    computed: {
        featureType() {
            return 'LINKS';
        },
        historyPageType() {
            return 'Link';
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
.o-links-page {

}
 */

</style>
