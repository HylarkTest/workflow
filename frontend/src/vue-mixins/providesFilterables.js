import { getIcon } from '@/core/display/typenamesList.js';

import MARKER_GROUPS from '@/graphql/markers/queries/MarkerGroups.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

const filterableFieldTypes = ['BOOLEAN', 'SELECT'];

const sortableFieldTypes = [
    'BOOLEAN',
    'CATEGORY',
    'CURRENCY',
    'DATE',
    'DATE_TIME',
    'DURATION',
    'EMAIL',
    'INTEGER',
    'LINE',
    'LOCATION',
    'MONEY',
    'NAME',
    'NUMBER',
    'PARAGRAPH',
    'PHONE',
    'RATING',
    'SALARY',
    'SELECT',
    'TIME',
    'URL',
];

function isFieldSortable(field) {
    if (field.type === 'MONEY' || field.type === 'SALARY') {
        // Cannot sort by fields with variable currency as the numbers would
        // be meaningless. Possibility to sort by converted currency in the
        // future but this would be difficult.
        if (!field.options?.currency) {
            return false;
        }
        // Cannot sort by salary without period as the numbers would be meaningless.
        if (field.type === 'SALARY' && !field.options?.period) {
            return false;
        }
    }
    return sortableFieldTypes.includes(field.type)
        && !field.options?.list
        && !field.options?.multiSelect
        && !field.options?.isRange;
}
export default {
    props: {
        mapping: {
            type: Object,
            required: true,
        },
    },
    apollo: {
        markerGroups: {
            query: MARKER_GROUPS,
            variables() {
                return { usedByMappings: [this.mapping.id] };
            },
            update: (data) => initializeConnections(data).markerGroups,
        },
    },
    computed: {
        filterables() {
            const filterables = [];

            if (this.blueprintMarkerGroups?.length) {
                filterables.push({
                    namePath: 'labels.markers',
                    val: 'MARKERS',
                    options: this.blueprintMarkerGroups.map((group) => ({
                        icon: getIcon(group.type),
                        name: group.name,
                        context: group.context,
                        items: group.items.map((item) => {
                            return {
                                ...item,
                                optionType: group.type,
                            };
                        }),
                    })),
                });
            }

            return [
                ...filterables,
                {
                    namePath: 'labels.fields',
                    val: 'FIELDS',
                    options: this.filterableFieldsOptions,
                },
                {
                    namePath: 'labels.features',
                    val: 'FEATURES',
                    options: this.filterableFeatures,
                },
                // {
                //     namePath: 'labels.createdBy',
                //     val: 'CREATED_BY',
                //     options: [],
                // },
                // {
                //     namePath: 'labels.lastUpdatedBy',
                //     val: 'UPDATED_BY',
                //     options: [],
                // },
            ];
        },
        filterableFeatures() {
            return [
                {
                    name: this.$t('labels.favorites'),
                    val: 'FAVORITES',
                    items: this.favoriteOptions.map((item) => {
                        return {
                            ...item,
                            optionType: 'FAVORITES',
                        };
                    }),
                },
                {
                    name: this.$t('labels.priority'),
                    val: 'PRIORITIES',
                    items: this.priorityOptions.map((item) => {
                        return {
                            ...item,
                            slotName: 'priority',
                            optionType: 'PRIORITIES',
                        };
                    }),
                },
            ].filter(({ val }) => _.find(this.mapping.features, { val }));
        },
        blueprintMarkerGroups() {
            return this.mapping.markerGroups.flatMap((blueprintGroup) => {
                const markerGroup = _.find(this.markerGroups, { id: blueprintGroup.group.id });
                if (!markerGroup) {
                    return [];
                }
                return [{
                    ...markerGroup,
                    name: blueprintGroup.name,
                    context: blueprintGroup.id,
                }];
            });
        },
        favoriteOptions() {
            return [
                { name: this.$t('labels.favorites'), value: true, icon: 'fas fa-heart' },
                { name: this.$t('labels.notFavorites'), value: false, icon: 'far fa-heart' },
            ];
        },
        priorityOptions() {
            return [
                { value: 0, text: this.$t('common.none') },
                { value: 1, text: this.$t('common.priorities.urgent') },
                { value: 3, text: this.$t('common.priorities.high') },
                { value: 5, text: this.$t('common.priorities.normal') },
                { value: 9, text: this.$t('common.priorities.low') },
            ];
        },
        fields() {
            return this.mapping.fields;
        },
        // All fields that can be used for filtering
        filterableFields() {
            return this.fields.filter((field) => filterableFieldTypes.includes(field.type));
        },
        sortableFields() {
            return this.fields.filter((field) => isFieldSortable(field));
        },
        sortableFieldOptions() {
            return this.sortableFields.map((field) => ({
                value: `field:${field.id}`,
                name: field.name,
                direction: 'DESC',
            }));
        },
        // Formatted field options with the possible filterable values
        filterableFieldsOptions() {
            return this.filterableFields.map((field) => {
                let items = field.type === 'BOOLEAN'
                    ? this.buildBooleanFieldOptions(field)
                    : this.buildSelectFieldOptions(field);
                // We add the field to each option, so we know how to construct
                // the query when it is selected.
                items = items.map((item) => ({
                    ...item,
                    field,
                    optionType: field.type,
                }));

                return {
                    name: field.name,
                    fieldId: field.id,
                    items,
                };
            });
        },
        sortables() {
            return [
                {
                    group: this.$t('labels.general'),
                    options: this.getBasicSortables(),
                },
                {
                    group: this.$t('labels.fields'),
                    options: this.sortableFieldOptions,
                },
            ];
        },
    },
    methods: {
        buildSelectFieldOptions(field) {
            return _.map(field.options.valueOptions, (name, value) => ({ name, value }));
        },
        buildBooleanFieldOptions(field) {
            const display = field.meta?.display;
            if (display === 'ICON_TOGGLE') {
                return [
                    { name: this.$t('common.yes'), value: true, icon: `${field.meta.symbol} text-primary-600` },
                    { name: this.$t('common.no'), value: false, icon: `${field.meta.symbol} text-cm-00` },
                ];
            }
            if (display === 'TOGGLE') {
                return [
                    { name: this.$t('labels.on'), value: true, icon: 'fa-toggle-on' },
                    { name: this.$t('labels.off'), value: false, icon: 'fa-toggle-off' },
                ];
            }
            return [
                { name: this.$t('common.yes'), value: true, icon: 'fa-square-check' },
                { name: this.$t('common.no'), value: false, icon: 'fa-square' },
            ];
        },
    },
};
