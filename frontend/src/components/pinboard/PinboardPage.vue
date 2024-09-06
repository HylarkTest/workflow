<template>
    <component
        :is="layoutComponent"
        class="o-pinboard-page"
        v-bind="layoutProps"
    >
        <FeatureMain
            v-if="!isLoading"
            :lists="pinboards"
            :featureStats="pinboardStats"
            :enableFileDrop="true"
            v-bind="featureMainProps"
        >
        </FeatureMain>
    </component>
</template>

<script>

import interactsWithFeaturePage from '@/vue-mixins/features/interactsWithFeaturePage.js';
import providesPinboardPageEssentials from '@/vue-mixins/pinboard/providesPinboardPageEssentials.js';

export default {
    name: 'PinboardPage',
    components: {
    },
    mixins: [
        interactsWithFeaturePage,
        providesPinboardPageEssentials,
    ],
    props: {

    },
    data() {
        return {

        };
    },
    computed: {
        featureType() {
            return 'PINBOARD';
        },
        historyPageType() {
            return 'Pinboard';
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
.o-pinboard-page {

}
 */

</style>
