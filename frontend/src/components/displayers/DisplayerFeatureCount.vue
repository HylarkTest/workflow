<template>
    <ButtonEl
        class="c-displayer-feature-count inline-block"
        :class="cantModifyClass"
        @click.exact="openFull"
        @click.alt="openPage"
    >
        <div
            :class="displayClasses"
        >
            {{ count }}
        </div>
    </ButtonEl>

    <FullViewModal
        v-if="isModalOpen"
        :item="item"
        :page="page"
        :defaultTab="tabKey"
        @closeModal="closeModal"
    >
    </FullViewModal>

</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import interactsWithViewItemsDisplayers from '@/vue-mixins/displayers/interactsWithViewItemsDisplayers.js';

export default {
    name: 'DisplayerFeatureCount',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
        interactsWithViewItemsDisplayers,
    ],
    props: {
        dataValue: {
            type: Number,
            default: 0,
        },
    },
    data() {
        return {
            typeKey: 'FEATURE_COUNT',
        };
    },
    computed: {
        count() {
            return this.dataValue || 0;
        },
        tabKey() {
            return this.dataInfo.id;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-feature-count {

} */

</style>
