import FeatureFormBase from '@/components/features/FeatureFormBase.vue';

import assistsWithEntityQueries from '@/vue-mixins/features/assistsWithEntityQueries.js';
import interactsWithFeatureListLoading from '@/vue-mixins/features/interactsWithFeatureListLoading.js';

import initializeConnections from '@/http/apollo/initializeConnections.js';

import MARKER_GROUPS from '@/graphql/markers/queries/MarkerGroups.gql';

export default {
    components: {
        FeatureFormBase,
    },
    mixins: [
        assistsWithEntityQueries,
        interactsWithFeatureListLoading,
    ],
    props: {
        spaceId: {
            type: [String, null],
            default: null,
        },
        page: {
            type: [Object, null],
            default: null,
        },
        defaultAssociations: {
            type: [Array, null],
            default: null,
        },
        isNew: Boolean,
    },
    apollo: {
        lists: {
            query() {
                return this.getListQuery(this.featureType);
            },
            variables() {
                return this.spaceIdsForLists ? { spaceIds: this.spaceIdsForLists } : {};
            },
            skip() {
                return this.shouldSkipListQuery || false;
            },
            update(data) {
                const firstKey = _.keys(data)[0];
                if (this.featureType.startsWith('EXTERNAL_')) {
                    return data[firstKey].data;
                }
                return initializeConnections(data)[firstKey];
            },
        },
        markerGroups: {
            query() {
                return MARKER_GROUPS;
            },
            skip() {
                return this.areMarkersHidden;
            },
            variables() {
                return {
                    usedByFeatures: [this.featureType],
                    spaceIds: this.spaceIdForValidExtras ? [this.spaceIdForValidExtras] : null,
                    types: ['STATUS', 'PIPELINE', 'TAG'],
                };
            },
            update: (data) => initializeConnections(data).markerGroups,
            fetchPolicy: 'network-only',
        },
    },
    data() {
        return {
            listKey: '', // Add in component
            listObjKey: '', // Add in component
            featureType: '', // Add in component
            processing: false,
            processingDelete: false,
        };
    },
    computed: {
        baseProps() {
            return {
                featureType: this.featureType,
                isNew: this.isNew,
                processing: this.processing,
                savedItem: this.savedItem,
                nonFormAssociations: this.savedAssociations,
                nonFormListId: this.savedListId,
                nonFormMarkers: this.savedMarkers,
                nonFormAssigneeGroups: this.savedAssigneeGroups,
                spaceIdForExtras: this.spaceIdForValidExtras,
                spaceIdsForLists: this.spaceIdsForLists,
                hiddenSections: this.hiddenSections,
                cantModifySections: this.cantModifySections,
                markerGroups: this.markerGroups,
                isLoadingMarkerGroups: this.isLoadingMarkerGroups,
                chosenListSpaceObj: this.chosenListSpaceObj,
                page: this.page,
            };
        },
        isLoadingMarkerGroups() {
            return this.$apollo.queries.markerGroups.loading;
        },
        areMarkersHidden() {
            return !!this.hiddenSections?.includes('MARKERS');
        },
        basicEntityVariables() {
            return this.getRequestVariables({
                withFeatures: [this.featureType],
                spaceId: this.spaceId || this.pageSpaceId,
            });
        },
        availableLists() {
            return this.lists;
        },
        pageLists() {
            return this.page?.lists || [];
        },
        firstList() {
            if (this.pageLists?.length) {
                const firstId = this.pageLists[0];
                return _.find(this.availableLists, { id: firstId });
            }
            return _.find(this.availableLists, 'isDefault');
        },
        mappingId() {
            return this.page?.mapping?.id;
        },
        pageSpaceId() {
            return this.page?.space?.id;
        },
        formListId() {
            return this.form?.[this.listKey];
        },
        listIdPointer() {
            return this.formListId || this.savedListId;
        },
        associationsPointer() {
            return this.form?.associations || this.savedAssociations;
        },
        chosenListObject() {
            return this.availableLists?.find((list) => {
                return this.listIdPointer === list.id;
            });
        },
        chosenListSpaceObj() {
            return this.chosenListObject?.space;
        },
        chosenListSpaceId() {
            return this.chosenListSpaceObj?.id;
        },
        spaceIdIfAssociation() {
            if (this.associationsPointer?.length) {
                return this.associationsPointer[0].spaceId;
            }
            return null;
        },
        markersPointer() {
            return this.form?.markers || this.savedMarkers;
        },
        spaceIdIfMarkers() {
            if (this.markersPointer?.length) {
                const groupIds = this.markersPointer.map((group) => group.groupId);
                const groupObj = this.markerGroups?.find((group) => {
                    return groupIds.includes(group.id);
                });
                const spacesWithFeature = groupObj?.usedByFeatures?.filter((item) => {
                    return item.features.includes(this.featureType);
                });
                const spaceIds = spacesWithFeature?.map((item) => {
                    return item.space.id;
                });
                return spaceIds;
            }
            return null;
        },
        shouldSkipListQuery() {
            return false;
        },
        savedAssociations() {
            return this.savedItem?.associations || null;
        },
        savedMarkers() {
            return this.savedItem?.markerGroups || null;
        },
        savedAssigneeGroups() {
            return this.savedItem?.assigneeGroups || [];
        },
        savedListId() {
            return this.savedItem?.[this.listObjKey]?.id;
        },
        isLoading() {
            return this.$apollo.loading;
        },
        // spaceIdIfAssociationOrMarkers() {
        //     return this.spaceIdIfAssociation || this.spaceIdIfMarkers;
        // },
        allSpaceIdChecks() {
            return this.spaceIdIfAssociation
                || this.chosenListSpaceId
                || this.pageSpaceId
                || null;
        },
        spaceIdForValidExtras() {
            return this.allSpaceIdChecks;
        },
        ultimateSpaceId() {
            if (this.spaceId) {
                return this.spaceId;
            }
            // If it is new, we want to scope by the space of the association
            // or the mapping. But not by the space of the list itself.

            if (this.isNew) {
                return this.spaceIdIfAssociation || this.pageSpaceId || null;
            }
            return this.allSpaceIdChecks;
        },
        spaceIdsForLists() {
            let ids = [];
            if (this.spaceIdIfMarkers) {
                ids = ids.concat(this.spaceIdIfMarkers);
            }
            if (this.ultimateSpaceId) {
                ids.push(this.ultimateSpaceId);
            }
            if (ids.length) {
                return _.uniq(ids);
            }
            return null;
        },
        hiddenSections() {
            return []; // In components
        },
        cantModifySections() {
            return []; // In components
        },
    },
    methods: {
        async saveItem(hasCloseModal = false) {
            this.processing = true;
            try {
                if (this.isNew) {
                    await this.createFunction(this.form);
                } else {
                    await this.updateFunction(this.form);
                }
                if (hasCloseModal) {
                    this.$emit('closeModal');
                }
            } finally {
                this.processing = false;
            }
        },
        updateSourceId(sourceId) {
            this.form.sourceId = sourceId;
        },
        async deleteItem() {
            this.processing = true;
            try {
                await this.deleteFunction(this.savedItem);
                this.$emit('closeModal');
            } finally {
                this.processing = false;
            }
        },
    },
    watch: {
        firstList: {
            immediate: true,
            handler(val) {
                if (this.isNew && this.form && val && this.listKey && !this.listIdPointer) {
                    this.form[this.listKey] = val.id;
                }
            },
        },
    },
};
