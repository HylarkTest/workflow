<template>
    <div
        class="o-mapping-edit-markers"
        :class="{ unclickable: processing }"
    >
        <LoaderFetch
            v-if="$isLoadingFirstTime"
            :isFull="true"
            class="py-10"
            :sphereSize="50"
        >
        </LoaderFetch>

        <div
            v-else
            class="flex flex-wrap gap-4"
        >
            <div
                v-for="type in markerTypes"
                :key="type"
                class="w-full lg:flex-1"
            >
                <MappingEditMarkerList
                    :type="type"
                    :markerGroups="getAllMarkerGroups(type)"
                    :activatedMarkerGroups="getActivatedMarkerGroups(type)"
                    :mappingName="mapping.name"
                    @updateMarkerGroups="updateMarkerGroups($event, type)"
                >
                </MappingEditMarkerList>
            </div>
        </div>
    </div>
</template>

<script>

import MappingEditMarkerList from '@/components/customize/MappingEditMarkerList.vue';

import TAG_GROUPS from '@/graphql/markers/queries/MarkerGroups.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

import { updateMappingMarkerGroups } from '@/core/repositories/mappingRepository.js';
import { reportValidationError } from '@/core/uiGenerators/userFeedbackGenerators.js';

import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';

const markerTypes = ['TAG', 'STATUS', 'PIPELINE'];

export default {
    name: 'MappingEditMarkers',
    components: {
        MappingEditMarkerList,
    },
    mixins: [
        interactsWithApolloQueries,
    ],
    props: {
        mapping: {
            type: Object,
            required: true,
        },
    },
    apollo: {
        tagGroupsConnection: {
            query: TAG_GROUPS,
            update: initializeConnections,
        },
    },
    data() {
        return {
            processing: false,
        };
    },
    computed: {
        mappingMarkerGroups() {
            return this.mapping.markerGroups;
        },
        mappingMarkerGroupIds() {
            return this.mappingMarkerGroups.map((markerGroup) => markerGroup.group.id);
        },
        markerGroups() {
            return this.tagGroupsConnection?.markerGroups;
        },
    },
    methods: {
        getAllMarkerGroups(type) {
            return _.filter(this.markerGroups, { type });
        },
        getActivatedMarkerGroups(type) {
            return this.getAllMarkerGroups(type).filter(({ id }) => {
                return this.mappingMarkerGroupIds.includes(id);
            });
        },
        async updateMarkerGroups(newList, groupType) {
            this.processing = true;

            const newMarkerGroups = [];
            this.markerTypes.forEach((type) => {
                let markerGroups = [];
                if (type === groupType) {
                    markerGroups = newList;
                } else {
                    markerGroups = this.getActivatedMarkerGroups(type);
                }
                newMarkerGroups.push(...markerGroups);
            });
            try {
                await updateMappingMarkerGroups(this.mapping, _.map(newMarkerGroups, 'id'));
                this.$debouncedSaveFeedback();
            } catch (error) {
                reportValidationError(error, 'input.id');
            } finally {
                this.processing = false;
            }
        },
    },
    created() {
        this.markerTypes = markerTypes;
    },
};
</script>

<style scoped>

/*.o-mapping-edit-markers {

} */

</style>
