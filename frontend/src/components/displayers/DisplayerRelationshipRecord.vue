<template>
    <div
        class="c-displayer-relationship-record"
        :class="cantModifyClass"
    >
        <div
            v-if="dataValue"
            class="inline-flex max-w-full flex-col"
        >
            <div class="flex justify-end mb-0.5">
                <ButtonEl
                    class="button-rounded--xs button-primary--medium"
                    @click.exact="openFull"
                    @click.alt="goToTab(relationshipId)"
                >
                    <i class="fa-light fa-pencil-alt mr-2">
                    </i>

                    <span
                        v-t="'common.edit'"
                    >

                    </span>
                </ButtonEl>
            </div>

            <ConnectedRecord
                :item="dataValue"
                imageSize="sm"
            >
            </ConnectedRecord>
        </div>

        <div
            v-else
            class="displayer__prompt inline-flex items-center"
            @click.exact="openFull"
            @click.alt="goToTab(relationshipId)"
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
            :page="mapping.pages[0]"
            :defaultTab="relationshipId"
            @closeModal="closeModal"
        >
        </FullViewModal>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import interactsWithViewItemsDisplayers from '@/vue-mixins/displayers/interactsWithViewItemsDisplayers.js';

export default {
    name: 'DisplayerRelationshipRecord',
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
            typeKey: 'RELATIONSHIP_RECORD',
        };
    },
    computed: {
        relationshipName() {
            return this.dataInfo.info?.name;
        },
        relationshipId() {
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

/*.c-displayer-relationship-record {

} */

</style>
