<template>
    <div class="o-filter-panel">
        <SideDialog
            :sideOpen="isOpen"
            innerPadding="py-6"
            @closeSide="closeFilters"
        >
            <div
                v-if="hasSavedFilters"
                class="p-2 flex justify-end mt-2"
            >
                <FiltersPicker
                    :modelValue="modelValue.id"
                    property="id"
                    :filtersObj="filtersObj"
                    :sortables="sortables"
                    :filterables="filterables"
                    :mapping="mapping"
                    :page="page"
                    :hasColorOnSelection="true"
                    :showApplyButton="true"
                    boxStyle="border"
                    @select="updateFilters"
                    @applyFilter="applyFilter"
                    @clear="clearSelectedFilter"
                >
                </FiltersPicker>
            </div>

            <FilterMain
                :group="modelValue.currentGroup"
                :sortOrder="modelValue.sortOrder"
                :filters="modelValue.discreteFilters"
                :sortables="sortables"
                :mapping="mapping"
                :filterables="filterables"
                :featureType="featureType"
                @update:filters="updateValue($event, 'discreteFilters')"
                @update:sortOrder="updateValue($event, 'sortOrder')"
                @update:group="updateValue($event, 'currentGroup')"
            >
            </FilterMain>
        </SideDialog>
    </div>
</template>

<script>

import SideDialog from '@/components/dialogs/SideDialog.vue';
import FilterMain from '@/components/sorting/FilterMain.vue';
import FiltersPicker from '@/components/pickers/FiltersPicker.vue';
import {
    convertApiFiltersToLocal, convertLocalFiltersToApiFilters,
} from '@/core/helpers/filterConverter.js';
import { removeTypename } from '@/core/helpers/apolloHelpers.js';
import SAVED_FILTER from '@/graphql/savedFilters/queries/SavedFilter.gql';

export default {
    name: 'FilterPanel',
    components: {
        SideDialog,
        FilterMain,
        FiltersPicker,
    },
    mixins: [
    ],
    props: {
        isOpen: Boolean,
        sortables: {
            type: Array,
            required: true,
        },
        modelValue: {
            type: Object,
            default: () => ({}),
        },
        mapping: {
            type: [null, Object],
            default: null,
        },
        filterables: {
            type: [Array, null],
            required: true,
        },
        page: {
            type: Object,
            required: true,
        },
        featureType: {
            type: String,
            default: '',
        },
        hasSavedFilters: Boolean,
    },
    emits: [
        'closeFilters',
        'update:modelValue',
        'clearFilters',
    ],
    apollo: {
        appliedSavedFilter: {
            query: SAVED_FILTER,
            variables() {
                return {
                    id: this.modelValue.id,
                };
            },
            skip() {
                return !this.modelValue.id;
            },
            fetchPolicy: 'cache-only',
            update: ({ savedFilter }) => savedFilter,
        },
    },
    data() {
        return {
        };
    },
    computed: {
        filtersObj() {
            return {
                sortOrder: this.modelValue.sortOrder,
                discreteFilters: this.modelValue.discreteFilters,
                currentGroup: this.modelValue.currentGroup,
            };
        },
        doFiltersMatchAppliedFilter() {
            const savedFilter = this.appliedSavedFilter;
            if (savedFilter) {
                const newValFilters = convertLocalFiltersToApiFilters(this.modelValue);
                return _.isEqual(newValFilters, removeTypename(_.pick(savedFilter, ['orderBy', 'group', 'filters'])));
            }
            return true;
        },
    },
    methods: {
        closeFilters() {
            this.$emit('closeFilters');
        },
        updateValue(value, key) {
            this.$emit('update:modelValue', {
                ...this.modelValue,
                [key]: value,
                id: null,
            });
        },
        updateFilters({ option }) {
            if (!option) {
                this.$emit('update:modelValue', null);
            } else {
                const localFilters = convertApiFiltersToLocal(option, this.filterables);
                this.$emit('update:modelValue', {
                    ...this.modelValue,
                    ..._.pick(localFilters, ['sortOrder', 'discreteFilters', 'currentGroup']),
                    id: option.id || null,
                });
            }
        },
        applyFilter(filter) {
            this.updateFilters({ option: filter });
        },
        clearSelectedFilter() {
            this.$emit('clearFilters');
        },
    },
    watch: {
        doFiltersMatchAppliedFilter(newVal) {
            if (!newVal) {
                this.$emit('update:modelValue', {
                    ...this.modelValue,
                    id: null,
                });
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-filter-panel {

}*/

</style>
