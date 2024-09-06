<template>
    <div class="c-feature-form-extras">
        <LoaderFetch
            v-if="isLoading && showLoader"
            :sphereSize="26"
            :isFull="true"
        >
        </LoaderFetch>
        <template
            v-else
        >
            <FeatureFormTogglable
                v-if="!isExternal && isCollaborativeBase"
                class="mb-3"
                :class="{ unclickable: processingAssociations }"
                :isOpenAlways="true"
            >
                <template
                    #label
                >
                    Assignees
                </template>

                <div
                    class="mt-1"
                >
                    <AssigneesPicker
                        :assigneeGroups="assigneeGroups"
                        bgColor="white"
                        @update:assigneeGroups="updateValue('assigneeGroups', $event)"
                    >
                    </AssigneesPicker>
                </div>
            </FeatureFormTogglable>

            <FeatureFormTogglable
                v-if="possibleMarkersLength && !areMarkersHidden"
                class="flex-1 mb-3"
                :isOpenAlways="areAllOpen"
                :isOpenByDefault="!!markersLength"
            >
                <template
                    #label
                >
                    Markers
                </template>

                <MarkersForm
                    :markerValues="markers"
                    :featureType="featureType"
                    :markerGroupBeingProcessed="markerGroupBeingProcessed"
                    :markerGroups="markerGroups"
                    @addMarker="addMarker"
                    @removeMarker="removeMarker"
                >
                </MarkersForm>

            </FeatureFormTogglable>

            <FeatureFormTogglable
                v-if="canAssociate"
                class="flex-1 mb-3"
                :class="{ unclickable: processingAssociations }"
                :isOpenAlways="areAllOpen"
                :isOpenByDefault="!!associationsLength"
            >
                <template
                    #label
                >
                    Associations
                </template>

                <div
                    class="mb-2"
                >
                    <EntitiesPicker
                        class="max-w-xs"
                        :modelValue="associations"
                        :bgColor="bgColor"
                        :withFeatures="[featureType]"
                        :entityVal="null"
                        :mappingId="mappingId"
                        :spaceId="entitiesSpaceId"
                        @update:modelValue="updateValue('associations', $event)"
                    >
                    </EntitiesPicker>

                    <div
                        v-if="associations.length"
                        class="flex gap-1 mt-2 flex-wrap"
                    >
                        <div
                            v-for="item in associations"
                            :key="item.id"
                            class="min-w-0"
                        >
                            <ConnectedRecord
                                class="max-w-full"
                                :item="item"
                                :showClear="true"
                                :bgColor="bgColor"
                                imageSize="sm"
                                @removeItem="removeAssociation(item)"
                            >
                            </ConnectedRecord>
                        </div>
                    </div>
                </div>
            </FeatureFormTogglable>

            <FeatureFormTogglable
                v-if="listId"
                class="flex-1 mb-3"
                :isOpenAlways="true"
            >
                <template
                    #label
                >
                    {{ $t(listName) }}
                </template>

                <div
                    v-if="chosenListSpaceObj"
                    class="mb-1"
                >
                    <span class="text-xssm mr-1">
                        {{ isNew ? 'Adding to:' : 'Found in:' }}
                    </span>
                    <SpaceNameLabel
                        :spaceName="spaceName"
                    >
                    </SpaceNameLabel>
                </div>

                <div class="w-48 mb-2">
                    <ListPicker
                        :modelValue="{ id: listId }"
                        comparator="id"
                        :spaceIds="spaceIdsForLists"
                        :integrationAccountId="integrationAccountId"
                        class="w-full"
                        :bgColor="bgColor"
                        :disabled="processingList || isListUnisModifiable"
                        :type="featureType"
                        :page="page"
                        @update:modelValue="updateValue('listId', $event)"
                    >
                    </ListPicker>
                </div>
            </FeatureFormTogglable>
        </template>
    </div>
</template>

<script>

import FeatureFormTogglable from './FeatureFormTogglable.vue';
import AssigneesPicker from '@/components/pickers/AssigneesPicker.vue';
import ListPicker from '@/components/pickers/ListPicker.vue';
import EntitiesPicker from '@/components/pickers/EntitiesPicker.vue';
import SpaceNameLabel from '@/components/display/SpaceNameLabel.vue';
import MarkersForm from '@/components/markers/MarkersForm.vue';

import assistsWithEntityQueries from '@/vue-mixins/features/assistsWithEntityQueries.js';

import ENTITIES_EXIST from '@/graphql/items/EntitiesExist.gql';

import { isActiveBaseCollaborative } from '@/core/repositories/baseRepository.js';

export default {
    name: 'FeatureFormExtras',
    components: {
        FeatureFormTogglable,
        ListPicker,
        EntitiesPicker,
        AssigneesPicker,
        SpaceNameLabel,
        MarkersForm,
    },
    mixins: [
        assistsWithEntityQueries,
    ],
    props: {
        featureType: {
            type: String,
            required: true,
            validator(val) {
                return [
                    'LINKS',
                    'TODOS',
                    'EVENTS',
                    'EMAILS',
                    'PINBOARD',
                    'DOCUMENTS',
                    'NOTES',
                ].includes(val);
            },
        },
        integrationAccountId: {
            type: String,
            default: '',
        },
        spaceIdsForLists: {
            type: [Array, null],
            default: null,
        },
        spaceIdForExtras: {
            type: [String, null],
            required: true,
        },
        page: {
            type: [Object, null],
            default: null,
        },
        listId: {
            type: String,
            required: true,
        },
        associations: {
            type: Array,
            required: true,
        },
        markers: {
            type: Array,
            required: true,
        },
        assigneeGroups: {
            type: Array,
            required: true,
        },
        areAllOpen: Boolean,
        bgColor: {
            type: String,
            default: 'gray',
        },
        processingList: Boolean,
        processingAssociations: Boolean,
        processingAssignees: Boolean,
        markerGroupBeingProcessed: {
            type: [String, null],
            default: null,
        },
        showLoader: Boolean,
        hiddenSections: {
            type: [Array, null],
            default: null,
        },
        cantModifySections: {
            type: [Array, null],
            default: null,
        },
        isLoadingMarkerGroups: Boolean,
        markerGroups: {
            type: [Array, null],
            default: null,
        },
        chosenListSpaceObj: {
            type: [Object, null],
            default: null,
        },
        isNew: Boolean,
    },
    emits: [
        'update:listId',
        'update:associations',
        'update:assigneeGroups',
        'removeAssociation',
        'addMarker',
        'removeMarker',
    ],
    apollo: {
        hasEntities: {
            query: ENTITIES_EXIST,
            variables() {
                return this.entitiesVariables;
            },
            update: (data) => !!data.allItems.pageInfo.total,
            fetchPolicy: 'no-cache',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        // General
        entitiesVariables() {
            return this.getRequestVariables({
                withFeatures: [this.featureType],
                spaceId: this.spaceIdForExtras,
            });
        },
        featureTypeFormatted() {
            return _.camelCase(this.featureType);
        },
        isCollaborativeBase() {
            return isActiveBaseCollaborative();
        },
        isExternal() {
            return !!this.integrationAccountId;
        },

        // Loading
        isLoading() {
            return this.isLoadingMarkerGroups || this.isLoadingEntitiesCheck;
        },
        isLoadingEntitiesCheck() {
            return this.$apollo.queries.hasEntities.loading;
        },

        // Spaces and mappings
        entitiesSpaceId() {
            return this.spaceIdForExtras;
        },
        // chosenListSpaceId() {
        //     return '';
        // },
        mappingId() {
            return this.page?.mapping?.id;
        },

        // Markers
        areMarkersHidden() {
            return !!this.hiddenSections?.includes('MARKERS');
        },
        markersLength() {
            // Length of markers on this item
            return this.markers.length;
        },
        possibleMarkersLength() {
            // Length of marker groups available to this feature
            return !!this.markerGroups?.length;
        },

        // Associations
        canAssociate() {
            // !this.isExternal &&
            return this.hasEntities;
        },
        hasPossibleAssociations() {
            // Whether there are potential associations
            return this.hasEntities;
        },
        associationsLength() {
            // How many associations are currently selected
            return this.associations.length;
        },

        // Lists
        listName() {
            return `features.${this.featureTypeFormatted}.listNameSingular`;
        },
        isListUnisModifiable() {
            return this.cantModifySections?.includes('LIST');
        },
        spaceName() {
            return this.chosenListSpaceObj?.name;
        },
    },
    methods: {
        updateValue(valueType, val) {
            this.$emit(`update:${valueType}`, val);
        },
        removeAssociation(item) {
            this.$emit('removeAssociation', item);
        },
        addMarker(marker, group) {
            this.$emit('addMarker', marker, group);
        },
        removeMarker(marker, group) {
            this.$emit('removeMarker', marker, group);
        },
        unclickableGroupClass(id) {
            return { unclickable: id === this.markerGroupBeingProcessed };
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-feature-form-extras {

}*/

</style>
