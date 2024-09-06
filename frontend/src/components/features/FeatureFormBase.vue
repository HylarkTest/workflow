<template>
    <div
        class="c-feature-form-base"
        :class="{ unclickable: processing }"
    >
        <template
            v-if="isNew || savedItem"
        >
            <FormWrapper
                v-if="form"
                :form="form"
                @submit="saveItem"
            >

                <div
                    v-if="!isNameHidden"
                    class="mb-4"
                >
                    <label class="header-form">
                        {{ $t('labels.name') }}*
                    </label>
                    <InputBox
                        ref="nameInput"
                        bgColor="gray"
                        formField="name"
                        placeholder="Add a name"
                    >
                    </InputBox>
                </div>

                <slot>
                </slot>

                <FeatureFormTogglable
                    v-if="!isDescriptionHidden"
                    class="flex-1 mb-4"
                    :isOpenAlways="hasSavedDescription"
                >
                    <template
                        #label
                    >
                        {{ $t('labels.description') }}
                    </template>

                    <TextareaField
                        formField="description"
                        boxStyle="plain"
                        bgColor="gray"
                        :placeholder="$t(descriptionPlaceholder)"
                    >
                    </TextareaField>
                </FeatureFormTogglable>

                <FeatureFormExtras
                    v-if="isNew"
                    v-model:listId="listId"
                    v-model:associations="associations"
                    v-model:assigneeGroups="assigneeGroups"
                    :hiddenSections="hiddenSections"
                    :markers="markers"
                    :isNew="true"
                    :featureType="featureType"
                    :spaceIdsForLists="spaceIdsForLists"
                    :spaceIdForExtras="spaceIdForExtras"
                    :integrationAccountId="integrationAccountId"
                    :markerGroupBeingProcessed="markerGroupBeingProcessed"
                    :markerGroups="markerGroups"
                    :isLoadingMarkerGroups="isLoadingMarkerGroups"
                    :chosenListSpaceObj="chosenListSpaceObj"
                    :page="page"
                    @removeAssociation="removeAssociation"
                    @removeMarker="removeMarker"
                    @addMarker="addMarker"
                >
                </FeatureFormExtras>

                <SaveButtonSticky
                    :bottomClass="saveBottomClass"
                    :textPath="saveTextPath"
                    :titlePath="saveTextPath"
                    :disabled="processing"
                    colorClass="button-secondary"
                    buttonClass="button"
                >
                </SaveButtonSticky>
            </FormWrapper>

            <template
                v-if="!isNew"
            >
                <div
                    v-if="form"
                    class="h-divider my-4"
                >
                </div>
                <div
                    class="bg-primary-100 rounded-xl p-3"
                >
                    <FeatureFormExtras
                        v-model:listId="listId"
                        v-model:associations="associations"
                        v-model:assigneeGroups="assigneeGroups"
                        :markers="markers"
                        :featureType="featureType"
                        bgColor="white"
                        :spaceIdsForLists="spaceIdsForLists"
                        :spaceIdForExtras="spaceIdForExtras"
                        :integrationAccountId="integrationAccountId"
                        :showLoader="true"
                        :hiddenSections="hiddenSections"
                        :cantModifySections="cantModifySections"
                        :processingAssociations="processingAssociations"
                        :processingAssignees="processingAssignees"
                        :processingList="processingList"
                        :markerGroupBeingProcessed="markerGroupBeingProcessed"
                        :markerGroups="markerGroups"
                        :chosenListSpaceObj="chosenListSpaceObj"
                        :isLoadingMarkerGroups="isLoadingMarkerGroups"
                        :page="page"
                        @removeAssociation="removeAssociation"
                        @removeMarker="removeMarker"
                        @addMarker="addMarker"
                    >
                    </FeatureFormExtras>
                </div>
            </template>

            <div
                v-if="!isNew"
                class="c-feature-form-base__footer"
            >
                <DeleteButton
                    @click="deleteItem"
                >
                </DeleteButton>

                <div
                    v-if="createdAt || updatedAt"
                    class="flex flex-wrap gap-x-6 gap-y-2"
                >
                    <DateLabel
                        v-if="updatedAt"
                        :date="updatedAt"
                        :includeLabel="true"
                        :fullTime="true"
                        mode="UPDATED_AT"
                        :performer="updatePerformer"
                    >
                    </DateLabel>
                    <DateLabel
                        v-if="createdAt"
                        :date="createdAt"
                        :includeLabel="true"
                        :fullTime="true"
                        :performer="createPerformer"
                    >
                    </DateLabel>
                </div>
            </div>
        </template>

        <LoaderFetch
            v-else
            class="py-10"
            :isFull="true"
            :sphereSize="40"
            bgColorClass="bg-secondary-200"
        >
        </LoaderFetch>
    </div>
</template>

<script>

import FeatureFormTogglable from './FeatureFormTogglable.vue';
import FeatureFormExtras from '@/components/features/FeatureFormExtras.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';

import { removeMarker, setMarker } from '@/core/repositories/markerRepository.js';
import { associateItem, removeItem } from '@/core/repositories/itemRepository.js';
import { arrRemoveId, arrRemove } from '@/core/utils.js';
import updateAssignees from '@/core/repositories/assigningRepository.js';

import usePerformers from '@/composables/usePerformers.js';

export default {
    name: 'FeatureFormBase',
    components: {
        FeatureFormTogglable,
        FeatureFormExtras,
        DeleteButton,
    },
    mixins: [
    ],
    props: {
        processing: Boolean,
        form: {
            type: [Object, null],
            required: true,
        },
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
        isNew: Boolean,
        savedItem: {
            type: [Object, null],
            required: true,
        },
        nonFormAssociations: {
            type: [Array, null],
            default: null,
        },
        nonFormMarkers: {
            type: [Array, null],
            default: null,
        },
        nonFormListId: {
            type: [String, null],
            default: null,
        },
        changeListFunction: {
            type: Function,
            required: true,
        },
        formAssociations: {
            type: [Array, null],
            default: null,
        },
        formMarkers: {
            type: [Array, null],
            default: null,
        },
        formAssigneeGroups: {
            type: [Array, null],
            default: null,
        },
        nonFormAssigneeGroups: {
            type: [Array, null],
            default: null,
        },
        formListId: {
            type: [String, null],
            default: null,
        },
        integrationAccountId: {
            type: String,
            default: '',
        },
        spaceIdsForLists: {
            type: [Array, null],
            required: true,
        },
        spaceIdForExtras: {
            type: [String, null],
            required: true,
        },
        hiddenSections: {
            type: [Array, null],
            default: null,
        },
        cantModifySections: {
            type: [Array, null],
            default: null,
        },
        markerGroups: {
            type: [Array, null],
            default: null,
        },
        isLoadingMarkerGroups: Boolean,
        chosenListSpaceObj: {
            type: [Object, null],
            default: null,
        },
        page: {
            type: [Object, null],
            required: true,
        },
    },
    emits: [
        'saveItem',
        'deleteItem',
        'update:form',
        'update:formAssociations',
        'update:formMarkers',
        'update:formListId',
        'update:formAssigneeGroups',
        'updateSourceId',
    ],
    setup() {
        const {
            getPerformerObj,
        } = usePerformers();

        return {
            getPerformerObj,
        };
    },
    data() {
        return {
            showDescription: false,
            processingList: false,
            processingAssociations: false,
            processingAssignees: false,
            markerGroupBeingProcessed: null,
        };
    },
    computed: {
        saveTextPath() {
            return this.isNew ? `features.${this.featureTypeFormatted}.add` : 'common.save';
        },
        featureTypeFormatted() {
            return _.camelCase(this.featureType);
        },
        createdAt() {
            return this.savedItem?.createdAt;
        },
        updatedAt() {
            return this.savedItem?.updatedAt;
        },
        createdFormatted() {
            return this.$dayjs(this.createdAt).format('lll');
        },
        updatedFormatted() {
            return this.$dayjs(this.updatedAt).format('lll');
        },
        listId: {
            get() {
                return this.formListId || this.nonFormListId || '';
            },
            set(newVal) {
                const id = newVal.id;
                if (this.formListId) {
                    const accountId = newVal.account?.id;
                    if (this.form.sourceId || accountId) {
                        this.$emit('updateSourceId', accountId);
                    } else {
                        this.$emit('updateSourceId', null);
                    }
                    this.$emit('update:formListId', id);
                } else {
                    this.saveList(id);
                }
            },
        },
        assigneeGroups: {
            get() {
                return this.formAssigneeGroups || this.nonFormAssigneeGroups || [];
            },
            set(newVal) {
                if (this.formAssociations) {
                    this.$emit('update:formAssigneeGroups', newVal);
                } else {
                    this.updateAssignees(newVal);
                }
            },
        },
        associations: {
            get() {
                return this.formAssociations || this.nonFormAssociations || [];
            },
            set(newVal) {
                const associationIds = _.map(this.associations, 'id');
                const newItem = newVal.find((item) => {
                    return !associationIds.includes(item.id);
                });
                if (newItem) {
                    if (this.formAssociations) {
                        this.$emit('update:formAssociations', newVal);
                    } else {
                        this.addAssociation(newItem);
                    }
                }
            },
        },
        markers() {
            return this.formMarkers || this.nonFormMarkers || [];
        },
        descriptionPlaceholder() {
            return `features.${this.featureTypeFormatted}.form.placeholders.description`;
        },
        hasSavedDescription() {
            return !!this.savedItem?.description;
        },
        isDescriptionHidden() {
            return this.hiddenSections?.includes('DESCRIPTION');
        },
        isNameHidden() {
            return this.hiddenSections?.includes('NAME');
        },
        saveBottomClass() {
            return this.isNew ? 'bottom-2' : 'bottom-12';
        },
        createPerformer() {
            const performer = this.savedItem.createAction?.performer;
            if (!performer) {
                return null;
            }
            return this.getPerformerObj(performer);
        },
        updatePerformer() {
            const performer = this.savedItem.latestAction?.performer;
            if (!performer) {
                return null;
            }
            return this.getPerformerObj(performer);
        },
    },
    methods: {
        saveItem() {
            this.$emit('saveItem');
        },
        async saveList(newListId) {
            this.processingList = true;
            try {
                await this.changeListFunction(this.savedItem, newListId);
                this.$saveFeedback();
            } finally {
                this.processingList = false;
            }
        },
        async addAssociation(item) {
            this.processingAssociations = true;
            try {
                await associateItem(this.savedItem, item);
            } finally {
                this.processingAssociations = false;
            }
        },
        async updateAssignees(assignees) {
            this.processingAssignees = true;
            try {
                await updateAssignees(this.savedItem, assignees);
            } finally {
                this.processingAssignees = false;
            }
        },
        async addAssignee(assignee) {
            this.processingAssignees = true;
            return assignee; // TODO
        },
        async removeAssignee(assignee) {
            this.processingAssignees = true;
            return assignee; // TODO
        },
        removeAssociation(item) {
            if (this.formAssociations) {
                const remainingAssociations = arrRemoveId(this.form.associations, item.id);
                this.$emit('update:formAssociations', remainingAssociations);
            } else {
                this.removeAssociatedItem(item);
            }
        },
        async removeAssociatedItem(item) {
            this.processingAssociations = true;
            try {
                await removeItem(this.savedItem, item);
            } finally {
                this.processingAssociations = false;
            }
        },
        getMarkerGroup(group) {
            return this.markers.find((markerGroup) => {
                const groupId = markerGroup.groupId || markerGroup.group.id;
                return groupId === group.id;
            });
        },
        doesMarkerExist(marker, markerGroup) {
            if (markerGroup.marker) {
                return markerGroup.marker?.id === marker.id;
            }
            if (_.isString(markerGroup.markers?.[0])) {
                return markerGroup.markers.includes(marker.id);
            }
            return _.find(markerGroup.markers, { id: marker.id });
        },
        async addMarker(marker, group) {
            const markerGroup = this.getMarkerGroup(group);
            if (markerGroup && this.doesMarkerExist(marker, markerGroup)) {
                this.removeMarker(marker, group);
            } else if (this.formMarkers) {
                this.addFormMarker(marker, group);
            } else {
                this.markerGroupBeingProcessed = group.id;
                await setMarker(this.savedItem, marker);
                this.markerGroupBeingProcessed = null;
            }
        },
        addFormMarker(marker, group) {
            const newMarkers = _.cloneDeep(this.markers);
            const groupIndex = _.findIndex(this.markers, { groupId: group.id });
            if (~groupIndex) {
                newMarkers[groupIndex].markers.push(marker.id);
            } else {
                newMarkers.push({
                    groupId: group.id,
                    markers: [marker.id],
                });
            }
            this.$emit('update:formMarkers', newMarkers);
        },
        async removeMarker(marker, group) {
            if (this.formMarkers) {
                this.removeFormMarker(marker, group);
            } else {
                this.markerGroupBeingProcessed = group.id;
                await removeMarker(this.savedItem, marker);
                this.markerGroupBeingProcessed = null;
            }
        },
        removeFormMarker(marker, group) {
            const newMarkers = _.cloneDeep(this.markers);
            const groupIndex = _.findIndex(this.markers, { groupId: group.id });
            if (~groupIndex) {
                const groupMarkers = newMarkers[groupIndex].markers;
                if (groupMarkers.length === 1) {
                    newMarkers.splice(groupIndex, 1);
                } else {
                    newMarkers[groupIndex].markers = arrRemove(groupMarkers, marker.id);
                }
            }
            this.$emit('update:formMarkers', newMarkers);
        },
        deleteItem() {
            this.$emit('deleteItem');
        },
    },
    created() {
    },
    mounted() {
        if (this.isNew) {
            this.$refs.nameInput?.select();
        }
    },
};
</script>

<style scoped>

.c-feature-form-base {
    &__footer {
        @apply
            bg-cm-00
            bottom-0
            flex
            flex-wrap
            items-center
            justify-between
            px-4
            py-3
            rounded-b-xl
            sticky
            z-over
        ;
    }
}

</style>
