<template>
    <Modal
        class="o-filter-save-modal"
        containerClass="p-4 w-600p"
        :header="true"
        v-bind="$attrs"
        @closeModal="closeModal"
    >
        <template
            #header
        >
            {{ headerText }}
        </template>

        <FormWrapper
            :form="form"
        >
            <div
                class="mb-8"
            >
                <label class="o-filter-save-modal__label">
                    {{ $t(namePathLabel) }}
                </label>

                <InputBox
                    ref="input"
                    formField="name"
                    bgColor="gray"
                    :placeholder="$t('customizations.filters.filterName')"
                >
                </InputBox>
            </div>

            <div
                class="mb-8"
            >
                <label class="o-filter-save-modal__label mb-1 block">
                    {{ $t(defaultPathLabel) }}
                </label>

                <div
                    v-if="showSingleOption"
                >
                    <ToggleButton
                        :modelValue="singleOptionValue"
                        @update:modelValue="setSingleOptionValue"
                    >
                    </ToggleButton>
                </div>

                <div
                    v-else
                >
                    <div
                        class="mb-1"
                    >
                        <CheckHolder
                            v-model="personalDefault"
                            type="radio"
                            :val="true"
                        >
                            For me only
                        </CheckHolder>
                    </div>

                    <CheckHolder
                        v-model="generalDefault"
                        type="radio"
                        :val="true"
                    >
                        For anyone viewing this page
                    </CheckHolder>
                </div>
            </div>

            <div>
                <label class="o-filter-save-modal__label">
                    {{ $t(configurationPathLabel) }}
                </label>

                <div
                    class="bg-cm-100 rounded-xl max-h-[360px] relative overflow-y-auto p-4"
                >
                    <FilterMain
                        v-model:sortOrder="form.sortOrder"
                        v-model:group="form.currentGroup"
                        v-model:filters="form.discreteFilters"
                        :sortables="sortables"
                        :filterables="filterables"
                        bgColor="white"
                        :mapping="mapping"
                    >
                    </FilterMain>
                </div>
            </div>

            <SaveButtonSticky
                :disabled="processing"
                :textPath="savePath"
                @click.stop="saveFilter(false)"
            >
                <SaveButton
                    v-if="showApplyButton"
                    class="mr-2"
                    colorClass="button-primary--border"
                    :textPath="secondSavePath"
                    @click.stop="saveAndApply"
                >
                </SaveButton>
            </SaveButtonSticky>
        </FormWrapper>
    </Modal>
</template>

<script>

import FilterMain from '@/components/sorting/FilterMain.vue';

import interactsWithSortables from '@/vue-mixins/interactsWithSortables.js';

import { isActiveBaseCollaborative } from '@/core/repositories/baseRepository.js';
import { saveFilter } from '@/core/repositories/savedFiltersRepository.js';

export default {
    name: 'FilterSaveModal',
    components: {
        FilterMain,
    },
    mixins: [
        interactsWithSortables,
    ],
    props: {
        filtersObj: {
            type: [Object, null],
            default: null,
        },
        editableFilter: {
            type: [Object, null],
            default: null,
        },
        filterDomain: {
            type: String,
            required: true,
            validator(val) {
                return ['PERSONAL', 'PUBLIC'].includes(val);
            },
        },
        mapping: {
            type: [null, Object],
            default: null,
        },
        filterables: {
            type: Array,
            required: true,
        },
        sortables: {
            type: Array,
            required: true,
        },
        page: {
            type: Object,
            required: true,
        },
        fromBlank: Boolean,
        hasPersonalDefaultInitially: Boolean,
        hasGeneralDefaultInitially: Boolean,
        showApplyButton: Boolean,
    },
    emits: [
        'closeModal',
        'applyFilter',
    ],
    data() {
        let sourceData;
        if (this.editableFilter) {
            sourceData = {
                ...this.editableFilter.toLocalFilters(this.filterables),
                name: this.editableFilter.name,
            };
        } else {
            sourceData = this.filtersObj && !this.fromBlank ? this.filtersObj : null;
        }
        const page = this.page;

        return {
            form: this.$apolloForm(() => {
                const data = {
                    nodeId: page.id,
                    name: sourceData?.name || this.getFilterName(),
                    sortOrder: sourceData?.sortOrder || this.getDefaultSort(),
                    currentGroup: sourceData?.currentGroup || null,
                    discreteFilters: sourceData?.discreteFilters || {},
                    private: this.filterDomain === 'PERSONAL',
                    personalDefault: this.hasPersonalDefaultInitially || false,
                    generalDefault: this.hasGeneralDefaultInitially || false,
                };
                if (sourceData?.id) {
                    data.id = sourceData.id;
                    data.personalDefault = sourceData.id === page.personalDefaultFilter?.id;
                    data.generalDefault = sourceData.id === page.defaultFilter?.id;
                }
                return data;
            }),
            processing: false,
        };
    },
    computed: {
        generalDefault: {
            get() {
                return this.form.generalDefault;
            },
            set(val) {
                this.form.generalDefault = val;
                if (val) {
                    this.form.personalDefault = false;
                }
            },
        },
        personalDefault: {
            get() {
                return this.form.personalDefault;
            },
            set(val) {
                this.form.personalDefault = val;
                if (val) {
                    this.form.generalDefault = false;
                }
            },
        },
        isPersonalDomain() {
            return this.filterDomain === 'PERSONAL';
        },
        isCollab() {
            return isActiveBaseCollaborative();
        },
        headerText() {
            let pathKey;
            if (this.editableFilter) {
                pathKey = 'editFilter';
            } else {
                pathKey = this.filterDomain === 'PERSONAL'
                    ? 'createNewPersonal'
                    : 'createNewPublic';
            }
            return this.$t(`customizations.filters.headers.${pathKey}`);
        },
        showSingleOption() {
            return this.isPersonalDomain || !this.isCollab;
        },
        defaultPathLabel() {
            return this.isPersonalDomain
                ? 'customizations.filters.setDefaultPersonal'
                : 'customizations.filters.setDefault';
        },
        namePathLabel() {
            return this.editableFilter
                ? 'labels.name'
                : 'customizations.filters.giveName';
        },
        configurationPathLabel() {
            return this.editableFilter
                ? 'customizations.filters.filterConfiguration'
                : 'customizations.filters.defineFilters';
        },
        savePath() {
            return this.editableFilter
                ? 'common.update'
                : 'common.save';
        },
        secondSavePath() {
            return this.editableFilter
                ? 'common.updateAndApply'
                : 'common.saveAndApply';
        },
        singleOptionValue() {
            const formKey = this.isPersonalDomain ? 'personalDefault' : 'generalDefault';
            return this.form[formKey];
        },
    },
    methods: {
        closeModal() {
            this.$emit('closeModal');
        },
        async saveFilter(hasApply) {
            this.processing = true;
            try {
                const newFilter = await saveFilter(this.form, this.page);
                this.$saveFeedback();
                if (hasApply) {
                    this.$emit('applyFilter', newFilter);
                }
                this.closeModal();
            } finally {
                this.processing = false;
            }
        },
        getDefaultSort() {
            return this.startingSortOrder('NAME');
        },
        getFilterName() {
            let base = 'Saved filter';
            let group = this.filtersObj?.currentGroup ? ' Grouped' : '';
            let filters = '';

            if (this.filtersObj?.discreteFilters) {
                const filtersMap = _(this.filtersObj.discreteFilters).map((filter, filterKey) => {
                    const length = filter.length;

                    if (length) {
                        const keyString = this.$t(`labels.${_.camelCase(filterKey)}`);
                        return `${keyString} (${length})`;
                    }
                    return null;
                }).compact().value();
                const joined = filtersMap.join(', ');
                filters = ` Criteria - ${joined}`;
            }

            if (group || filters) {
                base = `${base}:`;
            }

            if (group && filters) {
                group = `${group} |`;
            }

            return `${base}${group}${filters}`;
        },
        saveAndApply() {
            this.saveFilter(true);
        },
        setSingleOptionValue(value) {
            const formKey = this.isPersonalDomain ? 'personalDefault' : 'generalDefault';
            this.form[formKey] = value;
        },

    },
    created() {

    },
    mounted() {
        if (!this.editableFilter) {
            this.$refs.input.focus();
            this.$refs.input.select();
        }
    },
};
</script>

<style scoped>

.o-filter-save-modal {
    &__label {
        @apply
            font-semibold
            text-cm-600
            text-lg
        ;
    }
}

</style>
