<template>
    <component
        :is="rootEl"
        class="c-displayer-container min-w-0 flex flex-col"
        :class="containerClasses"
        @click="openSpecificEdit"
        @keydown.enter="openSpecificEdit"
    >
        <span
            v-if="fieldName"
            class="label-data mb-1 block"
        >
            {{ fieldName }}
        </span>

        <DisplayerLabel
            v-if="showContentName"
            class="mb-0.5 flex items-start"
            styleClass="label-data--intense"
        >
            <i
                v-if="isList"
                class="fa-regular fa-bars mr-1 mt-1"
            >
            </i>

            <DataNameDisplay
                :dataObj="dataInfo"
            >
            </DataNameDisplay>

            <div
                v-if="seesSelfEditEditOptions"
                class="c-displayer-container__edit transition-2eio"
            >
                Edit full list
            </div>
        </DisplayerLabel>

        <div
            v-for="(dataVal, index) in dataValues"
            :key="index"
            class="min-w-0 flex items-baseline max-w-full"
            :class="listClasses"
        >
            <div
                v-if="isNumberedList && !isListCount"
                class="mr-2 font-bold bg-primary-100 text-primary-600 rounded-md py-0.5 px-1.5 text-xs"
            >
                {{ index + 1 }}
            </div>

            <div
                class="min-w-0 w-full"
                :class="{ 'pointer-events-none': !canModifyValues }"
                @click="selfEditCheck"
            >
                <DisplayerLabel
                    v-if="showValueLabel(dataVal)"
                >
                    {{ dataVal.label }}
                </DisplayerLabel>

                <component
                    :is="displayerComponent"
                    :dataInfo="dataInfo"
                    :item="item"
                    :index="isList && !isListCount ? index : null"
                    :isModifiable="canModifyValues"
                    v-bind="propsForChildren"
                    :mapping="mapping"
                    :showMock="showMock"
                    :fieldInfo="fieldInfo"
                    :dataValue="fetchData(dataVal)"
                    @saveField="saveField"
                >
                </component>
            </div>

            <div
                v-if="isList && dataVal.main && !isSummaryView"
                class="ml-2 bg-primary-200 text-primary-600 button-rounded--sm"
            >
                Main
            </div>
        </div>

        <DisplayerEditModal
            v-if="showSpecificEdit"
            :item="item"
            :fieldInfo="fieldInfo"
            :processing="processing"
            :hideSave="hideSave"
            :mapping="mapping"
            :bypassForm="bypassForm"
            @closeModal="closeSpecificEdit"
            @saveField="saveField"
        >
        </DisplayerEditModal>
    </component>
</template>

<script>

import DataNameDisplay from '@/components/customize/DataNameDisplay.vue';

import interactsWithDisplayerContainers from '@/vue-mixins/displayers/interactsWithDisplayerContainers.js';

import { getDesignInfo } from '@/core/display/displayerInstructions.js';
import { updateItemField } from '@/core/repositories/itemRepository.js';
import { isValueFilled, areSomeValuesFilled } from '@/core/utils.js';
import { isSelfEditField } from '@/core/display/fullViewFunctions.js';

export default {
    name: 'DisplayerContainer',
    components: {
        DataNameDisplay,
    },
    mixins: [
        interactsWithDisplayerContainers,
    ],
    props: {
        // The information to be displayed, e.g. field, relationship, etc...
        // The flat and formatted object
        dataInfo: {
            type: [Object, null],
            default: null,
        },
        // The data item
        item: {
            type: [Object, null],
            default: null,
        },
        dataValue: {
            type: [String, Object, Number, Boolean, null],
            default: null,
        },
        prefix: {
            type: String,
            default: null,
        },
        isModifiable: Boolean,
        mapping: {
            type: [Object, null],
            default: null,
        },
        containerPropClass: {
            type: String,
            default: '',
        },
        fieldName: {
            type: String,
            default: '',
        },
        showNecessaryLabels: Boolean,
        alwaysShowLabels: Boolean,
        isMultiChild: Boolean,
        isSummaryView: Boolean,
    },
    data() {
        return {
            showSpecificEdit: false,
            processing: false,
        };
    },
    computed: {
        rootEl() {
            return this.canOpenSpecificEdit ? 'ButtonEl' : 'div';
        },

        // Style classes
        containerClasses() {
            return [
                { 'pointer-events-none': !this.isSelectable },
                { unclickable: this.processing },
                this.containerPropClass,
            ];
        },
        listClasses() {
            return this.isList && !this.isSummaryView && !this.isListCount
                ? 'odd:bg-primary-50 px-2 py-1 rounded-lg'
                : '';
        },

        // Props properties
        markerProps() {
            return {
                showInSelected: true,
            };
        },
        propsForChildren() {
            // DisplayerFeatureItem components use cAPI and <script setup> where "prefix" is a reserved term.
            // Therefore, we cannot pass "prefix" as a prop to Feature Items even if it is unused.
            return {
                ...this.markerProps,
                ...this.$attrs,
                ...(this.dataInfo.dataType !== 'FEATURES' ? { prefix: this.prefix } : {}),
            };
        },

        // Design and behavior properties
        designInfo() {
            return getDesignInfo(this.dataInfo);
        },
        displayerComponent() {
            return getDesignInfo(this.dataInfo)?.component || '';
        },
        isSelectable() {
            return !this.showMock;
        },
        hasEditComponent() {
            return !!this.designInfo?.editComponent;
        },
        hasEditCondition() {
            return _.has(this.designInfo, 'editCondition');
        },
        editCondition() {
            return this.designInfo?.editCondition;
        },
        hasEditModal() {
            if (this.isList || this.hasParent) {
                return true;
            }
            if (this.hasEditCondition) {
                return this.editCondition(this.infoOptions);
            }
            return this.hasEditComponent;
        },
        hasSaveOnEvent() {
            return !!this.designInfo.saveOnEvent;
        },
        bypassForm() {
            return !!this.designInfo.bypassForm;
        },
        hideSave() {
            return this.hasSaveOnEvent && !this.isLabeled;
        },
        bypassFormOnEdit() {
            return this.hasBypassForm;
        },
        canModifyValues() {
            return this.isModifiable && !this.processing;
        },
        canOpenSpecificEdit() {
            // Multi children should use the multi edit, therefore are excluded in here
            return this.isModifiable && this.hasEditModal && !this.isMultiChild;
        },
        isSelfEdit() {
            return this.infoObj
                ? isSelfEditField(this.infoObj.fieldType)
                : false;
        },
        isSelfEditList() {
            return this.isSelfEdit && this.isList;
        },
        seesSelfEditEditOptions() {
            return this.isSelfEditList
                && this.canOpenSpecificEdit
                && !this.isListCount
                && !this.isSummaryView;
        },
        isListCount() {
            return this.displayOption === 'LIST_COUNT';
        },

        // Item properties
        itemId() {
            return this.item.id;
        },
        itemValue() {
            // CHANGE HERE TO PICK OUT DIFFERENT TYPES OF DATA
            if (this.dataType === 'SYSTEM') {
                const formatted = _.camelCase(this.dataInfo.id);
                return this.item[formatted];
            }
            const deadlines = this.item?.deadlines;
            if (this.displayOption === 'TIME_PHASE') {
                return deadlines?.status;
            }
            if (this.displayOption === 'TIME_DUE') {
                return deadlines?.dueBy;
            }
            if (this.displayOption === 'TIME_START') {
                return deadlines?.startAt;
            }
            if (this.displayOption === 'PRIORITIES') {
                return this.item.priority;
            }
            if (this.displayOption === 'FAVORITES') {
                return this.item.isFavorite;
            }
            if (this.dataType === 'RELATIONSHIPS') {
                const relations = this.item.relations?.[this.dataInfo.id];
                if (relations) {
                    const record = relations.node;
                    if (record) {
                        return record;
                    }
                    const total = relations.pageInfo.total;
                    if (total) {
                        return total;
                    }
                }
            }
            if (this.displayOption === 'FEATURE_COUNT') {
                return this.item.features?.[this.dataInfo.formattedId];
            }
            if (this.displayOption === 'FEATURE_NEW') {
                return null;
            }
            if (this.dataType === 'FEATURES') {
                return this.item.features?.[this.dataInfo.formattedId] || null;
            }
            if (this.dataType === 'MARKERS') {
                if (this.item.markers) {
                    return this.item.markers[this.dataInfo.info.groupId];
                }
                return null;
            }
            if (this.displayOption === 'ASSIGNEES') {
                return this.item.assigneeGroups;
            }
            if (this.fields) {
                return this.getFieldValue();
            }
            return null;
        },
        dataValues() {
            // This takes the value above and does the last bit of modification to it
            const source = this.showMock ? this.dataValue : this.itemValue;

            if (this.isList) {
                if (this.displayOption === 'LIST_FIRST') {
                    const first = source?.listValue?.[0];
                    return first ? [first] : null;
                }
                if (this.displayOption === 'LIST_MAIN') {
                    const main = source?.listValue?.find((item) => item.main);
                    return main ? [main] : null;
                }
                if (this.isListCount) {
                    return [this.getListLength() || 0];
                }

                return source?.listValue;
            }
            return [source];
        },

        // Name properties
        showContentName() {
            return this.alwaysShowLabels
                || (this.showNecessaryLabels && this.showableContentName);
        },
        showableContentName() {
            // RELATIONSHIPS
            const hideIfNoContent = ['RELATIONSHIP_COUNT'];
            if (hideIfNoContent.includes(this.displayOption)) {
                return this.itemValue;
            }

            // FEATURES
            const showDisplayOptions = ['FEATURE_COUNT'];
            if (showDisplayOptions.includes(this.displayOption)) {
                return true;
            }

            const doNotShowDisplayOptionTypes = [
                'FEATURE_NEW',
                'FEATURE_GO',
                'FAVORITES',
                'PRIORITIES',
            ];
            if (doNotShowDisplayOptionTypes.includes(this.displayOption)) {
                return false;
            }

            // const hideIfNoContentType = ['FEATURES'];
            // if (hideIfNoContentType.includes(this.dataType)) {
            //     return this.itemValue;
            // }

            // COLLABORATION
            const doNotShowTypes = ['COLLABORATION'];
            if (doNotShowTypes.includes(this.dataType)) {
                return false;
            }

            // FIELDS
            if (this.dataType === 'FIELDS') {
                const doNotShowFields = ['SYSTEM_NAME', 'IMAGE'];

                if (doNotShowFields.includes(this.infoObj.fieldType)) {
                    return false;
                }
                if (this.isSelfEdit && !this.isList) {
                    return true;
                }
                return areSomeValuesFilled(this.dataValues);
            }
            return true;
        },

        // Fields properties
        mappingFields() {
            return this.mapping?.fields;
        },
        fields() {
            return this.item?.data;
        },
        isFieldWithDisplayOption() {
            return this.dataType === 'FIELDS' && this.displayOption;
        },

        // Parent properties
        hasParent() {
            return !!this.infoParent;
        },
        infoParent() {
            return this.infoObj?.parent;
        },
        parentId() {
            return this.infoParent.id;
        },
        fieldParent() {
            // For multis
            return this.infoParent || null;
        },
        isFieldParentList() {
            return this.infoParent?.info?.options?.list;
        },
        fieldParentDisplay() {
            return this.infoParent?.displayOption;
        },

        // Data properties
        fieldInfo() {
            return {
                dataValue: this.fullItemValue,
                dataInfo: this.fullDataInfo,
            };
        },
        fullItemValue() {
            if (this.fieldParent) {
                return this.getFieldValue(null, this.fieldParent.id);
            }
            return this.itemValue;
        },
        fullDataInfo() {
            // This is used for editing
            if (this.hasParent) {
                return {
                    ...this.fieldParent,
                    formattedId: this.fieldParent.id,
                    displayOption: null,
                };
            }
            if (this.isFieldWithDisplayOption) {
                return {
                    ...this.dataInfo,
                    formattedId: this.dataInfo.id,
                    displayOption: null,
                };
            }
            return this.dataInfo;
        },

        // Prefix properties
        displayPrefix() {
            if (this.prefix) {
                // Prefix passed from displayerMultifield parent
                return this.prefix;
            }

            // Prefix calculated for multi-fields
            if (this.fieldParent) {
                const parentId = this.fieldParent.id;

                if (this.isFieldParentList) {
                    let index = null;

                    if (this.fieldParentDisplay === 'LIST_FIRST') {
                        index = 0;
                    }

                    if (this.fieldParentDisplay === 'LIST_MAIN') {
                        index = this.fields[this.fieldParent.id]?.listValue
                            .findIndex((item) => item.main);
                    }
                    return `${parentId}.listValue.${index}.fieldValue.`;
                }

                return `${parentId}.fieldValue.`;
            }
            // No prefix
            return null;
        },
        savePrefix() {
            if (this.hasParent) {
                return this.parentId;
            }
            if (this.displayPrefix) {
                return `${this.displayPrefix}${this.dataInfo.id}`;
            }
            return this.dataInfo.id;
        },
    },
    methods: {
        fetchData(dataVal) {
            if (this.showMock) {
                return dataVal;
            }
            return this.getDataValue(dataVal);
        },
        closeSpecificEdit() {
            this.showSpecificEdit = false;
        },
        saveField(form) {
            this.updateDisplayerField(form);
        },
        async updateDisplayerField(form) {
            this.processing = true;
            try {
                await updateItemField(this.mapping, this.itemId, this.savePrefix, form);
                if (!this.hideSave) {
                    this.closeSpecificEdit();
                }
            } finally {
                this.processing = false;
            }
        },
        openSpecificEdit() {
            // Multi children should use the multi edit, therefore are excluded in here
            if (this.canOpenSpecificEdit) {
                this.showEdit();
            }
        },
        showEdit() {
            this.showSpecificEdit = true;
        },
        getFieldValue(prefix = this.displayPrefix, id = this.dataInfo.id) {
            let path = id;
            if (prefix) {
                path = `${this.displayPrefix}${path}`;
            }

            const relevantObj = _.get(this.fields, path);

            const listValue = relevantObj?.listValue;
            const listValueLength = listValue?.length;
            if (listValueLength) {
                return relevantObj;
            }

            const hasValue = isValueFilled(relevantObj);

            if (hasValue) {
                return relevantObj;
            }

            return null;
        },
        getListLength() {
            let path = this.dataInfo.id;
            if (this.displayPrefix) {
                path = `${this.displayPrefix}${path}`;
            }

            const relevantObj = _.get(this.fields, path);

            return relevantObj?.listValue.length;
        },
        selfEditCheck(event) {
            if (this.dataType === 'FIELDS' && this.isSelfEdit) {
                event.stopPropagation();
            }
        },
        showValueLabel(dataVal) {
            return dataVal
                && dataVal.label
                && (this.alwaysShowLabels || !this.isImage);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-displayer-container {
    &__edit {
        @apply
            bg-primary-100
            font-medium
            hover:bg-primary-200
            ml-1
            px-1
            rounded-full
            text-primary-600
            text-xxsxs
        ;
    }
}

</style>
