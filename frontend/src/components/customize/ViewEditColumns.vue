<!-- The repetition in this component is tribute to the code gods to finish multi-fields faster.
Refactor when used again or as soon as can be. Please forgive me.
Create one component that displays the data and then smaller ones inside that call themselves.
-->

<template>
    <div class="o-view-edit-columns">
        <p class="mb-2 text-sm rounded-lg bg-cm-100 p-2 text-cm-600">
            * Hylark data only. Does not include data from integrations.
        </p>
        <FormWrapper
            :form="form"
        >
            <div class="flex flex-wrap gap-4">
                <div class="h-full w-full lg:flex-1 lg:sticky lg:top-0">
                    <div class="flex items-baseline">
                        <h3 class="header-uppercase mb-2">
                            Column options
                        </h3>
                        <button
                            class="button-rounded--sm bg-cm-100 hover:bg-cm-200 ml-3"
                            type="button"
                            @click="toggleAll"
                        >
                            {{ allSelected ? 'Uncheck all' : 'Check all' }}
                        </button>
                        <button
                            v-if="!isDefaultColumns"
                            class="button-rounded--sm bg-cm-100 hover:bg-cm-200 ml-3"
                            type="button"
                            @click="resetDefaults"
                        >
                            Reset to default
                        </button>
                    </div>

                    <div
                        class="o-view-edit-columns__options"
                    >
                        <div
                            v-for="(item, key) in allOptionsFormatted"
                            :key="key"
                            class="mb-8 last:mb-0"
                        >
                            <h4
                                v-t="dataHeader(key)"
                                class="mb-1 font-semibold"
                            >
                            </h4>

                            <div>
                                <div
                                    v-for="(group, groupKey) in getGroupedDataFromSections(item, key)"
                                    :key="groupKey"
                                    class="mb-4 last:mb-0"
                                >
                                    <h5
                                        v-if="groupKey !== 'undefined'"
                                        class="text-cm-400 font-semibold text-sm"
                                    >
                                        <i
                                            v-if="isList(group)"
                                            class="fa-regular fa-bars mr-1"
                                        >
                                        </i>
                                        {{ getGroupHeader(groupKey, group) }}
                                    </h5>

                                    <div
                                        v-for="(sub, index) in getSubSourceForSelection(group)"
                                        :key="index"
                                        :class="{ 'mb-3': isGrouped(sub) }"
                                    >
                                        <DataNameDisplay
                                            v-if="hasSubSections(sub)"
                                            class="my-1 font-semibold text-xssm bg-cm-100 rounded py-0.5 px-2"
                                            :dataObj="sub"
                                        >
                                        </DataNameDisplay>

                                        <div>
                                            <div
                                                v-for="option in getSubOptionsForSelection(sub)"
                                                :key="option.formattedId"
                                                class="my-1"
                                            >
                                                <CheckHolder
                                                    v-model="form.visibleData"
                                                    :val="option"
                                                    predicate="formattedId"
                                                    size="sm"
                                                    :disabled="!canModifyData(option)"
                                                >
                                                    <DataNameDisplay
                                                        class="text-cm-600"
                                                        :dataObj="option"
                                                        :isParentHidden="true"
                                                        :isDisplayOptionFocused="true"
                                                    >
                                                        <template
                                                            v-if="showDataDisclaimer(option)"
                                                        >
                                                            *
                                                        </template>
                                                    </DataNameDisplay>
                                                </CheckHolder>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:flex-1">
                    <h3 class="header-uppercase">
                        Order your columns
                    </h3>

                    <Draggable
                        v-model="form.visibleData"
                        itemKey="formattedId"
                        group="columns"
                        handle=".drag-this"
                        :move="({ relatedContext: { index } }) => canModifyData(form.visibleData[index])"
                    >
                        <template #item="{ element, index }">
                            <DataInfo
                                class="o-view-edit-columns__item"
                                :class="isModifiableClasses(form.visibleData[index])"
                                :item="element"
                            >
                            </DataInfo>
                        </template>
                    </Draggable>
                </div>
            </div>
            <SaveButtonSticky
                :disabled="processing"
                @click.stop="saveColumns"
            >
            </SaveButtonSticky>
        </FormWrapper>
    </div>
</template>

<script>

import Draggable from 'vuedraggable';
import DataInfo from './DataInfo.vue';
import DataNameDisplay from '@/components/customize/DataNameDisplay.vue';

import {
    isHylarkOnlyData,
    getColumnDefaults,
} from '@/core/display/getAllEntityData.js';

import {
    getGroupHeader,
    getGroupedDataFromSections,
    getSubSourceForSelection,
    convertToFunctionalFormat,
    visibleDataFlatAndFormatted,
    getSubOptionsForSelection,
    getExpandedData,
    getPickerOptions,
} from '@/core/display/theStandardizer.js';

import { updatePageView } from '@/core/repositories/pageRepository.js';

// import INTEGRATIONS from '@/graphql/account-integrations/AccountIntegrations.gql';

export default {
    name: 'ViewEditColumns',
    components: {
        DataNameDisplay,
        Draggable,
        DataInfo,
    },
    mixins: [
    ],
    props: {
        allAvailableData: {
            type: Object,
            required: true,
        },
        page: {
            type: Object,
            required: true,
        },
        view: {
            type: Object,
            required: true,
        },
    },
    // apollo: {
    //     integrations: {
    //         query: INTEGRATIONS,
    //     },
    // },
    data() {
        const allColumnsFlat = getExpandedData(this.allAvailableData);
        const defaultColumns = this.getDefaultColumnsFlat(allColumnsFlat);

        let formColumns;
        if (this.view.visibleData) {
            formColumns = visibleDataFlatAndFormatted(this.view.visibleData, allColumnsFlat);
        } else {
            formColumns = defaultColumns;
        }

        return {
            form: this.$apolloForm(() => {
                return {
                    ...this.view,
                    visibleData: formColumns,
                };
            }, {
                formatData: (data) => ({
                    ...data,
                    visibleData: this.isDefaultColumns ? null : data.visibleData,
                }),
                reportValidation: true,
            }),
            defaultColumns,
            allColumnsFlat,
            allOptionsFormatted: this.getGroupedColumns(),
            processing: false,
        };
    },
    computed: {
        // integrationsLength() {
        //     return this.integrations?.length;
        // },
        allColumnsFlatLength() {
            return this.allColumnsFlat.length;
        },
        formColumnsLength() {
            return this.form.visibleData.length;
        },
        allColumnsFlatMap() {
            return _.map(this.allColumnsFlat, 'formattedId');
        },
        formColumnsMap() {
            return _.map(this.form.visibleData, 'formattedId');
        },
        defaultColumnsMap() {
            return _.map(this.defaultColumns, 'formattedId');
        },
        allSelected() {
            // Checking if all selected
            return this.allColumnsFlatLength === this.formColumnsLength;
        },
        isDefaultColumns() {
            // Checks if form value is the default order and columns
            return _.isEqual(this.defaultColumnsMap, this.formColumnsMap);
        },
    },
    methods: {
        getGroupHeader(groupKey, group) {
            return getGroupHeader(groupKey, group);
        },
        showDataDisclaimer(option) {
            return isHylarkOnlyData(option.formattedId);
            // this.integrationsLength && isHylarkOnlyData(val);
        },
        isModifiableClasses(val) {
            return this.canModifyData(val)
                ? 'drag-this cursor-move border-primary-400'
                : 'border-cm-200 cursor-not-allowed';
        },
        canModifyData(data) {
            if (_.has(data, 'info')) {
                return data.info.fieldType !== 'SYSTEM_NAME';
            }
            return data.type !== 'SYSTEM_NAME';
        },
        dataHeader(viewType) {
            return `labels.${_.camelCase(viewType)}`;
        },
        setItem(dataType, option, displayOption) {
            const item = convertToFunctionalFormat(dataType, option, displayOption);
            const index = _.findIndex(this.form.visibleData, { formattedId: item.formattedId });
            if (~index) {
                this.form.visibleData.splice(index, 1);
            } else {
                this.form.visibleData.push(item);
            }
        },

        getGroupedColumns() {
            return getPickerOptions(this.allAvailableData);
        },

        getDefaultColumnsFlat(columns) {
            return getColumnDefaults(columns);
        },

        async saveColumns() {
            this.processing = true;
            try {
                await updatePageView(this.form, this.page);
                this.$saveFeedback();
            } finally {
                this.processing = false;
            }
        },
        toggleAll() {
            if (this.allSelected) {
                this.form.visibleData = [_.find(this.allColumnsFlat, _.negate(this.canModifyData))];
            } else {
                this.addRemainingColumns();
            }
        },
        addRemainingColumns() {
            if (!this.formColumnsLength) {
                this.form.visibleData = this.allColumnsFlat;
            } else {
                const unused = this.allColumnsFlat.filter((column) => {
                    return !_.find(this.form.visibleData, { formattedId: column.formattedId });
                });
                this.form.visibleData = this.form.visibleData.concat(unused);
            }
        },
        getGroupedDataFromSections(data, dataType) {
            return getGroupedDataFromSections(data, dataType);
        },
        getSubSourceForSelection(group) {
            return getSubSourceForSelection(group);
        },
        getSubOptionsForSelection(group) {
            return getSubOptionsForSelection(group);
        },
        isList(group) {
            const first = group[0];
            return first.info?.options?.list;
        },
        hasSubSections(sub) {
            return sub.info?.options?.hasSubSections;
        },
        isGrouped(sub) {
            return sub.info?.options?.isGrouped;
        },
        resetDefaults() {
            this.form.visibleData = this.defaultColumns;
        },
    },
    created() {
        this.convertToFunctionalFormat = convertToFunctionalFormat;
    },
};
</script>

<style scoped>

.o-view-edit-columns {

    &__options {
        @media (min-width: 1024px) {
            max-height: 500px;
            @apply
                overflow-y-auto
            ;
        }
    }

    &__item {
        @apply
            bg-cm-00
            border
            border-dashed
            my-2
            px-4
            py-2
            rounded-xl
        ;
    }
}

</style>
