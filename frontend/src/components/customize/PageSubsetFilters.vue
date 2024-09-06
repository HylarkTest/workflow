<template>
    <div
        v-if="mapping && !isLoadingMarkers"
        class="o-page-subset-filters flex flex-col relative"
    >
        <div
            v-if="hasMultipleOptions"
            class="mb-6"
        >
            <label
                class="o-page-wizard-subset__label label-data"
            >
                Filter type
            </label>

            <div class="flex">
                <div
                    v-for="option in filterOptions"
                    :key="option.val"
                    class="mr-4 last:mr-0"
                >
                    <CheckHolder
                        :modelValue="formBy"
                        :val="option.val"
                        type="radio"
                        @update:modelValue="updateForm('by', option.val)"
                    >
                        {{ option.name }}
                    </CheckHolder>
                </div>
            </div>
        </div>

        <div
            v-if="hasContent"
        >
            <label
                v-if="hasMultipleOptions"
                class="o-page-wizard-subset__label label-data"
            >
                Filter value
            </label>

            <div
                class="flex items-center"
            >
                <div
                    v-if="formBy === 'FIELD'"
                    class="mr-4"
                >
                    <FieldPicker
                        :modelValue="pageFilter.fieldId"
                        class="w-40"
                        :bgColor="bgColor"
                        textColor="brand"
                        placeholderClass="text-cm-600"
                        property="id"
                        :mappingId="blueprintId"
                        :filterFieldTypes="allowedFieldTypes"
                        @update:modelValue="updateForm('fieldId', $event)"
                    >
                    </FieldPicker>
                </div>

                <div
                    v-if="!hasMultipleOptions"
                    class="mr-4 font-medium"
                >
                    {{ filterOptions[0].name }}
                </div>

                <div class="">
                    <button
                        class="button-rounded--sm mr-2"
                        :class="matchButtonClass('IS')"
                        type="button"
                        @click="updateForm('match', 'IS')"
                    >
                        Is
                    </button>
                    <button
                        class="button-rounded--sm"
                        :class="matchButtonClass('IS_NOT')"
                        type="button"
                        @click="updateForm('match', 'IS_NOT')"
                    >
                        Is not
                    </button>
                </div>

                <div class="ml-4">
                    <MarkerPicker
                        v-if="isByMarker"
                        :modelValue="filterMarker"
                        showClear
                        class="w-40"
                        :bgColor="bgColor"
                        textColor="brand"
                        :mapping="mapping"
                        @select="updateMarkers"
                        @clear="updateMarkers(null)"
                    >
                    </MarkerPicker>
                    <DropdownBox
                        v-else
                        :modelValue="pageFilter.matchValue"
                        showClear
                        class="w-40"
                        :bgColor="bgColor"
                        textColor="brand"
                        placeholder="Select an option"
                        displayRule="display"
                        property="value"
                        :options="fieldOptions"
                        @update:modelValue="updateForm('matchValue', $event)"
                    >
                    </DropdownBox>
                </div>
            </div>
        </div>

        <NoContentText
            v-if="showNoContent && noContentDisplayable"
            class="mt-8"
            :hideIcon="true"
            customHeaderPath="customizations.pageWizard.blueprint.subset.cannotCreate.header"
            customMessagePath="customizations.pageWizard.blueprint.subset.cannotCreate.description"
        >
        </NoContentText>

        <CloseButton
            v-if="showClose"
            class="absolute top-0.5 right-0.5"
            @click="updateForm(null, null)"
        >
        </CloseButton>

        <!-- <IWantThis
            v-if="showWantThis && showNoContent"
            class="mt-5 max-w-sm"
            featureMessage="Expanding subset criteria (in page wizard)"
            @wantedThis="showWantThis = false"
        >
        </IWantThis> -->
    </div>
</template>

<script>

import FieldPicker from '@/components/pickers/FieldPicker.vue';
import MarkerPicker from '@/components/pickers/MarkerPicker.vue';
import CloseButton from '@/components/buttons/CloseButton.vue';

import interactsWithPageSubsets from '@/vue-mixins/customizations/interactsWithPageSubsets.js';
import DropdownBox from '@/components/dropdowns/DropdownBox.vue';

export default {
    name: 'PageSubsetFilters',
    components: {
        DropdownBox,
        FieldPicker,
        MarkerPicker,
        CloseButton,
    },
    mixins: [
        interactsWithPageSubsets,
    ],
    props: {
        pageForm: {
            type: Object,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
        noContentDisplayable: Boolean,
        showClose: Boolean,
        bgColor: {
            type: String,
            default: 'gray',
        },
    },
    emits: [
        'updateForm',
    ],
    data() {
        return {
            // showWantThis: true,
        };
    },
    computed: {
        blueprintId() {
            return this.mapping.id;
        },

        showNoContent() {
            return !this.hasFilterOptions && this.hasByKey;
        },

        hasContent() {
            return this.hasFilterOptions;
        },
        formBy() {
            return this.pageFilter?.by;
        },
        formField() {
            return this.pageFilter?.fieldId;
        },
        hasByKey() {
            return _.has(this.pageFilter, 'by');
        },
        pageFilter() {
            return this.pageForm.filter;
        },
        formMatch() {
            return this.pageFilter?.match;
        },
        isByMarker() {
            return this.formBy === 'MARKER';
        },
        filterMarker() {
            if (!this.isByMarker) {
                return null;
            }
            const id = this.pageFilter.matchValue;
            if (!id) {
                return null;
            }
            return _(this.markerGroupsArr).flatMap('items')
                .find({ id });
        },
        fieldOptions() {
            const field = _.find(this.filteredFields, ['id', this.formField]);
            if (!field) {
                return [];
            }
            if (field.type === 'BOOLEAN') {
                return [{ display: 'True', value: true }, { display: 'False', value: false }];
            }
            if (field.type === 'SELECT') {
                return _.map(field.options.valueOptions, (display, value) => ({ display, value }));
            }
            return [];
        },
        firstField() {
            return this.filteredFields[0];
        },
    },
    methods: {
        updateForm(valKey, val) {
            this.$emit('updateForm', { valKey, val });
        },
        matchButtonClass(val) {
            return this.isMatchSelected(val)
                ? 'bg-primary-600 text-cm-00'
                : 'button-primary--light';
        },
        isMatchSelected(val) {
            return this.formMatch === val;
        },
        updateMarkers(event) {
            const payload = event?.value?.id || null;
            this.updateForm('matchValue', payload);
            if (event) {
                const context = event.group?.context || event.page?.context;
                if (context) {
                    this.updateForm('context', context);
                }
            }
        },
    },
    watch: {
        hasFilterOptions: {
            immediate: true,
            handler(hasOptions) {
                if (hasOptions && !this.formBy) {
                    this.updateForm('by', this.filterOptions[0].val);
                }
            },
        },
        formBy: {
            immediate: true,
            handler(newVal, oldVal) {
                if (oldVal) {
                    this.updateForm('fieldId', null);
                }
                if (newVal === 'FIELD') {
                    this.updateForm('fieldId', this.firstField.id);
                } else if (oldVal) {
                    this.updateForm('matchValue', null);
                }
            },
        },
        formField() {
            this.updateForm('matchValue', null);
        },
    },
    created() {
    },
};
</script>

<style scoped>

/*.o-page-subset-filters {

} */

</style>
