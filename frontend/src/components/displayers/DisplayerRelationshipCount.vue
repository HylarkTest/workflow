<template>
    <ButtonEl
        class="c-displayer-relationship-count"
        :class="cantModifyClass"
        @click.exact="openFull"
        @click.alt="goToTab(relationshipId)"
    >
        <div
            v-if="dataValue"
            :class="[displayClasses, clickableClass]"
        >
            {{ dataValue }}
        </div>

        <div
            v-else
            class="displayer__prompt inline-flex items-center"
        >
            <i class="fa-light fa-draw-circle mr-2">
            </i>

            <span class="u-ellipsis">
                Add "{{ relationshipName }}"
            </span>
        </div>

        <FullViewModal
            v-if="isModalOpen"
            :item="item"
            :page="page"
            :defaultTab="relationshipId"
            @closeModal="closeModal"
        >
        </FullViewModal>
    </ButtonEl>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import interactsWithViewItemsDisplayers from '@/vue-mixins/displayers/interactsWithViewItemsDisplayers.js';

export default {
    name: 'DisplayerRelationshipCount',
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
            typeKey: 'RELATIONSHIP_COUNT',
        };
    },
    computed: {
        relationshipName() {
            return this.dataInfo.info?.name;
        },
        relationshipId() {
            return this.dataInfo.id;
        },
        clickableClass() {
            return { 'cursor-pointer': this.isModifiable };
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-relationship-count {

} */

</style>
