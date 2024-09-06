<template>
    <DropdownBox
        class="c-grouping-selection"
        :modelValue="currentGroup"
        :groups="options"
        :inlineLabel="label"
        displayRule="display"
        :hideToggleButton="hideToggleButton"
        :hideValue="hideValue"
        :showClear="true"
        property="value"
        :popupProps="{ widthProp: '11.25rem', alignCenter: true }"
        :hasColorOnSelection="true"
        placeholder="Group by..."
        @update:modelValue="updateValue"
    >
        <template
            v-if="hasNewFilterButton"
            #popupEnd
        >
            <div class="flex justify-center py-1 px-2">
                <button
                    class="button--sm button-primary--light"
                    type="button"
                    @click="openModal"
                >
                    Save grouping
                </button>
            </div>

            <FilterSaveModal
                v-if="isModalOpen"
                :filtersObj="filtersObj"
                :mapping="mapping"
                :page="page"
                filterDomain="PUBLIC"
                :filterables="filterables"
                :sortables="sortables"
                @closeModal="closeModal"
                @applyFilter="applyFilter"
            >
            </FilterSaveModal>
        </template>
    </DropdownBox>
</template>

<script>

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import MARKER_GROUPS from '@/graphql/markers/queries/MarkerGroups.gql';
import groupables from '@/core/instructions/groupables.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import { getIcon } from '@/core/display/typenamesList.js';

// Type key in the object
const groupableFieldTypes = [
    'SELECT',
    'BOOLEAN',
    'CURRENCY',
    'RATING',
    'CATEGORY',
];

// Val key in the object
const groupableFieldVals = [
    'DATE',
];

export default {
    name: 'GroupingSelection',
    components: {
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        currentGroup: {
            type: [null, String],
            default: null,
        },
        featureType: {
            type: String,
            default: '',
        },
        mapping: {
            type: [null, Object],
            default: null,
        },
        showListOption: Boolean,
        hideValue: Boolean,
        hideToggleButton: Boolean,
        hideLabel: Boolean,
        spaceIds: {
            type: [Array, null],
            default: null,
        },
        hasNewFilterButton: Boolean,
        page: {
            type: [Object, null],
            default: null,
        },
        filterables: {
            type: [Array, null],
            default: null,
        },
        sortables: {
            type: [Array, null],
            default: null,
        },
        filtersObj: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'update:currentGroup',
        'applyFilter',
    ],
    apollo: {
        markerGroups: {
            query: MARKER_GROUPS,
            skip() {
                return !this.canBeGroupedByMarkers;
            },
            variables() {
                let varObj = {};
                if (this.mapping) {
                    varObj = {
                        usedByMappings: [this.mapping.id],
                    };
                }
                if (this.featureType) {
                    varObj = {
                        usedByFeatures: [this.featureType],
                    };
                }
                if (this.spaceIds) {
                    varObj.spaceIds = this.spaceIds;
                }
                return varObj;
            },
            update: (data) => initializeConnections(data).markerGroups,
        },
    },
    data() {
        return {
        };
    },
    computed: {
        label() {
            return !this.hideLabel ? this.inlineLabel : null;
        },
        inlineLabel() {
            return {
                icon: 'fal fa-object-group',
                text: this.$t('common.group'),
                position: 'inside',
                hideColon: true,
                useActiveColor: !!this.currentGroup,
            };
        },
        basicOptions() {
            // Flat map is good for filtering and mapping at the same time when
            // the logic is tied together.
            return this.groupables.flatMap((group) => {
                if (group === '{MARKERS}') {
                    return [];
                }
                if (group === '{FIELDS}') {
                    return [];
                }
                if (group === 'LIST') {
                    if (!this.showListOption) {
                        return [];
                    }
                    return [{
                        value: 'LIST',
                        display: this.$t(this.listName),
                    }];
                }
                return [{
                    value: group,
                    display: this.$t(`labels.${_.camelCase(group)}`),
                }];
            });
        },
        markerOptions() {
            if (this.canBeGroupedByMarkers) {
                if (this.mapping) {
                    return this.mapping.markerGroups?.map((markerGroup) => {
                        return {
                            type: markerGroup.type,
                            value: `marker:${markerGroup.id}`,
                            display: markerGroup.name,
                        };
                    }) || [];
                }
                return this.markerGroups?.map((markerGroup) => {
                    return {
                        type: markerGroup.type,
                        value: `marker:${markerGroup.id}`,
                        display: markerGroup.name,
                    };
                }) || [];
            }
            return [];
        },
        groupableFields() {
            return this.mapping.fields.filter((field) => {
                return groupableFieldTypes.includes(field.type)
                    || groupableFieldVals.includes(field.val);
            });
        },
        fieldOptions() {
            if (!this.mapping) {
                return [];
            }
            return this.groupableFields.map((field) => {
                return {
                    value: `field:${field.id}`,
                    display: field.name,
                };
            });
        },
        options() {
            const options = [
                {
                    group: this.$t('labels.general'),
                    options: this.basicOptions,
                },
            ];
            if (this.markerOptions.length) {
                options.push({
                    group: this.$t('labels.markers'),
                    options: this.markerOptions,
                });
            }
            if (this.fieldOptions.length) {
                options.push({
                    group: this.$t('labels.fields'),
                    options: this.fieldOptions,
                });
            }
            return options;
        },
        canBeGroupedByMarkers() {
            if (this.mapping) {
                return true;
            }
            // Cannot use property as created hook isn't run yet.
            return groupables[this.featureType].includes('{MARKERS}');
        },
        listName() {
            return `features.${this.featureTypeFormatted}.listName`;
        },
        featureTypeFormatted() {
            return _.camelCase(this.featureType);
        },
        mappingGroups() {
            const groups = [];
            if (_.find(this.mapping.features, ['val', 'FAVORITES'])) {
                groups.push('FAVORITES');
            }
            if (_.find(this.mapping.features, ['val', 'PRIORITIES'])) {
                groups.push('PRIORITY');
            }
            groups.push('{MARKERS}');
            groups.push('{FIELDS}');
            return groups;
        },
        groupables() {
            if (this.mapping) {
                return this.mappingGroups;
            }
            return this.allGroupables[this.featureType];
        },
    },
    methods: {
        updateValue(val) {
            if (val === this.currentGroup) {
                this.emitGroup(null);
            } else {
                this.emitGroup(val);
            }
        },
        emitGroup(group) {
            this.$emit('update:currentGroup', group);
        },
        getIcon(val) {
            return getIcon(val);
        },
        applyFilter(filter) {
            this.$emit('applyFilter', filter);
        },
        setToFirstOption() {
            const firstGroupWithOptions = _.find(this.options, (group) => group.options.length);
            const firstOption = firstGroupWithOptions?.options[0].value;
            if (firstOption) {
                this.updateValue(firstOption);
            }
        },
    },
    watch: {
        spaceIds(newVal, oldVal) {
            if (typeof oldVal !== 'undefined') {
                this.emitGroup(null);
            }
        },
    },
    created() {
        this.allGroupables = groupables;
    },
};
</script>

<style scoped>

/*
.c-grouping-selection {

}
 */

</style>
