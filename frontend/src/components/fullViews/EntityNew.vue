<template>
    <div
        class="c-entity-new"
        v-bind="$attrs"
    >
        <div
            v-if="isLoadingContent"
            class="centered px-4 py-8"
        >
            <LoaderFetch
                :sphereSize="40"
            >
            </LoaderFetch>
        </div>

        <template
            v-if="!isLoadingContent"
        >
            <h2
                class="c-entity-new__header text-primary-600"
            >
                {{ $t('common.new') }} - {{ mappingName }}
            </h2>
        </template>

        <template
            v-if="!isLoadingContent && form.data"
        >
            <FormWrapper
                class="mb-8"
                :form="form"
            >
                <FormFields
                    v-if="fullMapping"
                    v-model:form="form.data"
                    :focusInitially="true"
                    :errors="form.errors()"
                    :prepopulatedFields="firstOfPrepopulatedType"
                    :formattedFields="formattedFields"
                >
                </FormFields>

                <div
                    v-if="additionalFieldsLength"
                    class=""
                >
                    <p class="px-4 py-2 bg-secondary-100 font-semibold text-secondary-600 mb-2 mt-8">
                        Additional fields
                    </p>
                    <FormFields
                        v-model:form="form.data"
                        :errors="form.errors()"
                        :formattedFields="formattedAdditional"
                        :prepopulatedFields="firstOfPrepopulatedType"
                    >
                    </FormFields>
                </div>

                <div
                    v-if="possibleMarkersLength"
                    class="mt-4"
                >
                    <h4
                        v-t="'labels.markers'"
                        class="mb-2 font-bold text-lg"
                    >
                    </h4>
                    <MarkersForm
                        :markerGroups="filteredMarkerGroups"
                        :markerValues="markerValues"
                        groupByPath="group.type"
                        @addMarker="toggleMarker"
                        @removeMarker="removeMarker"
                    >
                    </MarkersForm>
                </div>
            </FormWrapper>

            <div
                class="sticky bottom-0 mt-2 pointer-events-none"
            >
                <div
                    v-if="isEntities"
                    class="mt-2 bg-cm-00 w-full pointer-events-auto"
                >
                    <div class="centered bg-secondary-100 rounded-lg py-1 px-3">
                        <p class="text-xs">
                            Customize the fields that appear on this form when you create a new "{{ mappingName }}"
                        </p>

                        <button
                            class="button--xs button-secondary ml-2"
                            type="button"
                            @click.stop="openModal"
                        >
                            Customize
                        </button>
                    </div>
                </div>

                <div class="flex justify-end mt-2">
                    <SaveOptions
                        :buttons="saveButtons"
                        @submit="saveOption"
                    >
                    </SaveOptions>

                    <!-- <SaveButton
                        v-if="!buttonsLength"
                        v-bind="$attrs"
                        @save="$emit('save', 'close')"
                    >
                    </SaveButton>
                    <SaveButton
                        :disabled="processing"
                        titleString="Create a new record"
                        @save="saveItem"
                    >
                    </SaveButton> -->
                </div>
            </div>
        </template>

        <DataEditModal
            v-if="isModalOpen"
            :page="usedPage"
            defaultTab="FORM"
            @closeModal="closeModal"
        >
        </DataEditModal>
    </div>
</template>

<script>

import DataEditModal from '@/components/customize/DataEditModal.vue';
import MarkersForm from '@/components/markers/MarkersForm.vue';
import SaveOptions from '@/components/buttons/SaveOptions.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import {
    getBasicFormattedData,
    provideFieldValue,
} from '@/core/display/theStandardizer.js';

import { createItem } from '@/core/repositories/itemRepository.js';
import { arrRemove } from '@/core/utils.js';

import MAPPING from '@/graphql/mappings/queries/Mapping.gql';
import PAGE from '@/graphql/pages/queries/Page.gql';

export default {
    name: 'EntityNew',
    components: {
        DataEditModal,
        MarkersForm,
        SaveOptions,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        page: {
            type: [Object, null],
            required: true,
        },
        mapping: {
            type: [Object, String],
            required: true,
        },
        // This needs to have a very specific format
        // With the field type to be prepopulated as the key (info.fieldType).
        // Then it will populate the first field with that type.
        prepopulatedValues: {
            type: [Object, null],
            default: null,
        },
        fullForm: Boolean,
        includeAddAnother: Boolean,
    },
    emits: [
        'saved',
        'closeModal',
    ],
    apollo: {
        fullMapping: {
            query: MAPPING,
            variables() {
                return {
                    id: this.mapping?.id || this.mapping,
                };
            },
            update: (data) => data.mapping,
            fetchPolicy: 'cache-first',
        },
        fullPage: {
            query: PAGE,
            variables() {
                return {
                    id: this.mainMappingPage?.id,
                };
            },
            skip() {
                return this.page || !this.mainMappingPage;
            },
            update: _.property('page'),
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
            processing: false,
            form: this.$apolloForm(() => {
                const formKeys = {
                    data: null,
                    markers: [],
                };

                const subsetMarkerObj = this.getSubsetMarkerObj();
                if (subsetMarkerObj) {
                    formKeys.markers.push(subsetMarkerObj);
                }
                return formKeys;
            }),
        };
    },
    computed: {
        isEntities() {
            return this.usedPage.type === 'ENTITIES';
        },
        isLoadingContent() {
            return this.isLoadingMapping || this.isLoadingPage;
        },
        isLoadingMapping() {
            return this.$apollo.queries.fullMapping?.loading || !this.fullMapping;
        },
        isLoadingPage() {
            return this.$apollo.queries.fullPage?.loading || !this.usedPage;
        },
        usedPage() {
            return this.page || this.fullPage;
        },
        mappingName() {
            return this.fullMapping?.singularName || this.fullMapping?.name;
        },
        newFields() {
            return this.usedPage?.newData.fields;
        },
        newFieldsLength() {
            return this.newFields?.length;
        },
        fields() {
            return this.fullMapping?.fields;
        },
        fieldsIds() {
            return _.map(this.fields, 'id');
        },
        newFieldsFull() {
            if (this.fullForm) {
                return this.fields;
            }
            return this.newFields?.map((field) => {
                return _.find(this.fields, { id: field });
            });
        },
        newFieldsFallback() {
            if (!this.fullForm && !this.newFieldsLength) {
                return [_.find(this.fields, { type: 'SYSTEM_NAME' })];
            }
            return this.newFieldsFull;
        },
        formattedFields() {
            return this.fullMapping
                ? getBasicFormattedData(this.newFieldsFallback, 'FIELDS')
                : [];
        },
        mappingPages() {
            return this.fullMapping?.pages;
        },
        mainMappingPage() {
            return this.mappingPages?.[0];
        },
        formattedFieldsIds() {
            return _.map(this.formattedFields, 'id');
        },
        allFieldsFieldTypes() {
            return this.fields.map((field) => {
                return {
                    fieldType: field.type,
                    id: field.id,
                };
            });
        },
        firstOfPrepopulatedType() {
            return _(this.prepopulatedKeys).map((keyVal) => {
                const matchingField = _.find(this.allFieldsFieldTypes, { fieldType: keyVal });
                const hasValue = this.prepopulatedValues[keyVal];
                return hasValue && matchingField;
            }).compact().value();
        },
        prepopulatedKeys() {
            const fieldKeys = _.keys(this.prepopulatedValues);
            return _.without(fieldKeys, 'id');
        },
        prepopulatedRelevantFields() {
            return this.fields.filter((field) => {
                return this.prepopulatedKeys.includes(field.type);
            });
        },
        additionalFields() {
            return this.prepopulatedRelevantFields.filter((field) => {
                return !this.formattedFieldsIds.includes(field.id);
            });
        },
        additionalFieldsLength() {
            return this.additionalFields?.length;
        },
        formattedAdditional() {
            return getBasicFormattedData(this.additionalFields, 'FIELDS');
        },

        // Markers
        newMarkers() {
            return this.usedPage?.newData.markers;
        },
        mappingMarkerGroups() {
            return this.fullMapping?.markerGroups || [];
        },
        filteredMarkerGroups() {
            return this.markerGroups.filter((group) => {
                return this.newMarkers.includes(group.blueprintGroupId);
            });
        },
        markerGroups() {
            return this.mappingMarkerGroups.map((group) => {
                return {
                    ...group.group,
                    blueprintGroupId: group.id,
                };
            });
        },
        possibleMarkersLength() {
            return this.filteredMarkerGroups?.length;
        },
        markerValues() {
            // Passed down to MarkersForm
            return this.form.markers.map((group) => {
                let globalId = group.globalId;
                if (!globalId) {
                    const globalMarker = this.markerGroups.find((markerGroup) => {
                        return markerGroup.blueprintGroupId === group.blueprintGroupId;
                    });
                    globalId = globalMarker.id;
                }

                return {
                    groupId: globalId,
                    blueprintGroupId: group.blueprintGroupId,
                    markers: group.markers,
                };
            });
        },
        saveButtons() {
            if (this.includeAddAnother) {
                return [
                    'another',
                ];
            }
            return [];
        },
    },
    methods: {
        getSubsetMarkerObj() {
            // Cannot use page computed property as method is called in data
            // which is run before computed properties.
            const firstMarker = (this.page || this.fullPage)?.markerFilters?.[0];
            if (firstMarker && firstMarker.operator === 'IS') {
                return {
                    blueprintGroupId: firstMarker.context,
                    markers: [firstMarker.markerId],
                };
            }
            return null;
        },
        toggleMarker(marker, group) {
            const blueprintGroupId = group.blueprintGroupId;
            const groupIndex = _.findIndex(this.form.markers, { blueprintGroupId });
            const markerExists = this.form.markers[groupIndex]?.markers.includes(marker.id);

            if (markerExists) {
                this.removeMarker(marker, group, groupIndex);
            } else {
                this.addMarker(marker, group, groupIndex);
            }
        },
        addMarker(marker, group, groupIndex) {
            const blueprintGroupId = group.blueprintGroupId;
            if (~groupIndex) {
                if (group.type === 'STATUS') {
                    this.form.markers[groupIndex].markers = [marker.id];
                } else {
                    this.form.markers[groupIndex].markers.push(marker.id);
                }
            } else {
                this.form.markers.push({
                    blueprintGroupId,
                    globalId: group.id,
                    markers: [marker.id],
                });
            }
        },
        removeMarker(marker, group, groupIndex) {
            let index = groupIndex;
            if (!index) {
                const blueprintGroupId = group.blueprintGroupId;
                index = _.findIndex(this.form.markers, { blueprintGroupId });
            }

            if (~index) {
                const groupMarkers = this.form.markers[index].markers;
                if (groupMarkers.length === 1) {
                    this.form.markers.splice(index, 1);
                } else {
                    this.form.markers[index].markers = arrRemove(groupMarkers, marker.id);
                }
            }
        },
        initializeFields() {
            let formFields = this.formattedFields;

            if (this.additionalFieldsLength) {
                formFields = this.formattedFields.concat(this.formattedAdditional);
            }

            return _(formFields).map((field) => {
                return this.getFieldVal(field);
            }).fromPairs().value();
        },
        getFieldVal(field) {
            let fieldValue = null;
            // Picks it off the first of the values of that type in the form
            const isPrepopulated = _.find(this.firstOfPrepopulatedType, { id: field.id });

            if (isPrepopulated) {
                fieldValue = this.addDefaultValue(field) || null;
            }
            return [
                field.id,
                fieldValue,
            ];
        },
        addDefaultValue(field) {
            const fieldVal = this.prepopulatedValues[field.info.fieldType];
            return provideFieldValue(field, fieldVal);
        },
        async saveOption(action) {
            await this.saveItem();
            if (action === 'another') {
                this.form.reset();
                this.setFields();
            } else {
                this.$emit('closeModal');
            }
        },
        async saveItem() {
            this.processing = true;
            try {
                const item = await createItem(this.form, this.fullMapping);
                const itemWithFullMapping = {
                    ...item,
                    mapping: this.fullMapping,
                };
                this.$emit('saved', itemWithFullMapping);
                this.$saveFeedback();
            } finally {
                this.processing = false;
            }
        },
        subsetField() {
            const firstField = this.usedPage?.fieldFilters?.[0];
            return firstField || false;
        },
        addSubsetField(subsetField) {
            const id = subsetField.fieldId;
            const val = subsetField.match;
            const is = subsetField.operator === 'IS';
            const field = _.find(this.allFieldsFieldTypes, { id });
            if (!field) {
                return;
            }
            if (field.fieldType === 'BOOLEAN') {
                this.form.data[id] = { fieldValue: is ? !!val : !val };
            } else if (field.fieldType === 'SELECT' && is) {
                this.form.data[id] = { fieldValue: val };
            }
        },
        removeFieldsFromForm(oldFields) {
            oldFields.forEach((fieldId) => {
                delete this.form.data[fieldId];
            });
        },
        addFieldsToForm(newFields) {
            const formatted = newFields.map((fieldId) => {
                return _.find(this.formattedFields, { id: fieldId });
            });
            formatted.forEach((field) => {
                const fieldVal = this.getFieldVal(field);
                this.form.data[fieldVal[0]] = fieldVal[1];
            });
        },
        setFields() {
            this.form.data = this.initializeFields();

            const subsetField = this.subsetField();
            if (subsetField) {
                this.addSubsetField(subsetField);
            }
        },
    },
    watch: {
        isLoadingContent(isLoading) {
            if (!isLoading) {
                this.setFields();
            }
        },
        newFields(newFields, oldFields) {
            if (this.oldFields?.length) {
                const newFieldsDifference = _.difference(newFields, oldFields);
                const oldFieldsDifference = _.difference(oldFields, newFields);

                // If an id is in oldFields but not newFields, remove from the form
                if (oldFieldsDifference.length) {
                    this.removeFieldsFromForm(oldFieldsDifference);
                }
                // If an id is in newFields but not oldFields, add to the form
                if (newFieldsDifference.length) {
                    this.addFieldsToForm(newFieldsDifference);
                }
            }
        },
        fieldsIds(newIds, oldIds) {
            // This looks at the mapping, if a field got deleted
            const oldFieldsDifference = _.difference(oldIds, newIds);

            // If an id is in oldIds but not newIds, remove from the form as it got deleted
            if (oldFieldsDifference.length) {
                this.removeFieldsFromForm(oldFieldsDifference);
            }
        },
    },
    created() {
    },
};
</script>

<style scoped>

.c-entity-new {
    &__header {
        @apply
            font-semibold
            mb-10
            px-8
            text-2xl
            text-center
        ;
    }
}

</style>
