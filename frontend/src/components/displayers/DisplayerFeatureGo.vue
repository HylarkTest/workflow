<template>
    <ButtonEl
        class="c-displayer-feature-new cardDesigns__button"
        :class="[displayClasses, cantModifyClass]"
        @click.exact="openFull"
        @click.alt="openPage"
    >
        <i
            class="far mr-2"
            :class="icon"
        >
        </i>

        <span
            v-t="buttonTextPath"
        >
        </span>

        <FullViewModal
            v-if="isModalOpen"
            :item="item"
            :page="page"
            :defaultTab="tabKey"
            @closeModal="closeModal"
        >
        </FullViewModal>
    </ButtonEl>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import interactsWithViewItemsDisplayers from '@/vue-mixins/displayers/interactsWithViewItemsDisplayers.js';

import { featureIcons } from '@/core/display/featureIcons.js';

export default {
    name: 'DisplayerFeatureGo',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
        interactsWithViewItemsDisplayers,
    ],
    props: {
    },
    data() {
        return {
            typeKey: 'FEATURE_GO',
        };
    },
    computed: {
        icon() {
            return featureIcons[this.tabKey]?.icon;
        },
        buttonTextPath() {
            return `features.${this.featureFormatted}.go`;
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-feature-go {

} */

</style>
