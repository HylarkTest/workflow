<template>
    <div
        v-if="!isLoading"
        class="o-entity-brief"
    >
        <div class="o-entity-brief__header">
            <div class="font-bold text-2xl mr-4">
                {{ fullItem.name }}
            </div>

            <button
                v-t="'common.view'"
                class="button button-primary"
                type="button"
                title="Click to view dialog, Click + Alt to view page"
                @click.exact="openModal"
                @click.alt.prevent="goToPage"
            >
            </button>
        </div>

        <EntityMarkers
            v-if="hasMarkerGroups"
            class="mb-8"
            :item="fullItem"
            :mapping="mapping"
        >
        </EntityMarkers>

        <FeatureCount
            class="mb-8"
            :features="features"
            @openViewModal="openViewModal"
        >
        </FeatureCount>

        <FullInfo
            :item="fullItem"
            :mapping="mapping"
            :page="page"
        >
        </FullInfo>

        <Modal
            v-if="isModalOpen"
            containerClass="w-10/12"
            :containerStyle="{ height: '80vh' }"
            @closeModal="closeViewModal"
        >
            <FullView
                :item="fullItem"
                :page="page"
                :defaultTab="selectedPanel"
                @closeModal="closeViewModal"
            >
            </FullView>
        </Modal>
    </div>
</template>

<script>
import providesApolloFullItem from '@/vue-mixins/providesApolloFullItem.js';
import providesEntityConnectionsInfo from '@/vue-mixins/providesEntityConnectionsInfo.js';
import providesColors from '@/vue-mixins/style/providesColors.js';
import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import FeatureCount from '@/components/features/FeatureCount.vue';

import { allData } from '@/core/display/getAllEntityData.js';

export default {
    name: 'EntityBrief',
    components: {
        FeatureCount,
    },
    mixins: [
        providesApolloFullItem,
        providesEntityConnectionsInfo,
        providesColors,
        interactsWithModal,
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
        showClear: Boolean,
    },
    data() {
        return {
            deleteProcessing: false,
            selectedPanel: null,
        };
    },
    computed: {
        isLoading() {
            return this.$apollo.loading;
        },
        page() {
            return (this.fullItem.pages && this.fullItem.pages[0])
                || (this.mapping?.pages && this.mapping.pages[0]);
        },
        hasMarkerGroups() {
            return this.mapping.markerGroups?.length;
        },
        allAvailableData() {
            return this.mapping ? allData(this.mapping) : {};
        },
        allAvailableFeatures() {
            return this.allAvailableData.FEATURES;
        },
        filteredFeatures() {
            return this.allAvailableFeatures?.filter((feature) => {
                return !['PRIORITIES', 'FAVORITES'].includes(feature.val);
            });
        },
        features() {
            return this.getSectionInfo(this.filteredFeatures, 'FEATURES');
        },
        itemRoute() {
            return {
                name: 'fullView',
                params: { itemId: this.fullItem.id, pageId: this.page.id },
            };
        },
    },
    methods: {
        openViewModal(value) {
            this.selectedPanel = value;
            this.openModal();
        },
        closeViewModal() {
            this.closeModal();
            this.selectedPanel = null;
        },
        goToPage() {
            this.$router.push(this.itemRoute);
        },
    },
};
</script>

<style scoped>
.o-entity-brief {
    &__container {
        @apply
            m-3
            p-4
            rounded-xl
        ;
    }

    &__header {
        @apply
            flex
            items-center
            justify-between
            mb-4
            py-4
            rounded-lg
    }
}
</style>
