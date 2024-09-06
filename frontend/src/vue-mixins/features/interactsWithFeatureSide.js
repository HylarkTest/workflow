// For when folders/groups are a thing
import { newListShortcut } from '@/core/accessibility/keyboardShortcuts.js';

import { randomNumber } from '@/core/utils.js';
import { getIntegrationIcon } from '@/core/display/integrationIcons.js';

import AddCircle from '@/components/buttons/AddCircle.vue';
import FeatureSide from '@/components/features/FeatureSide.vue';
import initializeConnections from '@/http/apollo/initializeConnections.js';

import MARKER_GROUPS from '@/graphql/markers/queries/MarkerGroups.gql';

// For when folders/groups are a thing
const basicFilters = [
    {
        id: 'all',
        icon: 'fa-box-check',
        color: 'turquoise',
    },
];

const newActions = [
    {
        icon: 'fa-plus-square',
        id: 'list',
        shortcut: newListShortcut,
    },
    // {
    //     icon: 'fa-layer-plus',
    //     id: 'group',
    //     shortcut: newGroupShortcut,
    // },
];

export default {
    components: {
        AddCircle,
        FeatureSide,
    },
    props: {
        // bases: {
        //     type: Array,
        //     required: true,
        // },
        sources: {
            type: Object,
            required: true,
        },
        activeFilters: {
            type: Object,
            required: true,
        },
    },
    apollo: {
        markerGroups: {
            query: MARKER_GROUPS,
            variables() {
                return this.feature ? { usedByFeatures: [this.feature] } : {};
            },
            update: (data) => initializeConnections(data).markerGroups,
        },
    },
    data() {
        return {
            newListDropdown: false,
        };
    },
    computed: {
        filterables() {
            return [
                {
                    namePath: 'labels.tags',
                    val: 'TAGS',
                    options: _.flatMap(this.markerGroups, 'items'),
                },
            ];
        },
        randomFunction() {
            return () => 0;
        },
        combinedSources() {
            return [
                ...(this.sources.spaces || []),
            ];
        },
        addNewOptions() {
            return this.combinedSources.map((source) => {
                return {
                    name: source.name,
                    id: source.id,
                    provider: source.provider,
                };
            });
        },
    },
    methods: {
        integrationIcon(val) {
            return getIntegrationIcon(val);
        },

        // In place for when groups are done
        addNew(action) {
            this[`addNew${_.capitalize(action)}`]();
        },

        addNewList() {
            if (this.addNewOptions?.length === 1) {
                this.selectNewListSource(this.addNewOptions[0]);
            } else {
                this.newListDropdown = !this.newListDropdown;
            }
        },
        closeNewListDropdown() {
            this.newListDropdown = false;
        },
        getNextOrder(source) {
            const highest = _.maxBy(source.list, 'order');
            return highest + 1;
        },
        getNewClone(source, newList, newExternalList) {
            const clone = _.clone(source.provider ? newExternalList : newList);

            clone.id = randomNumber();

            if (_.has(clone, 'order')) {
                clone.order = this.getNextOrder(source);
            }
            return clone;
        },
    },
    created() {
        this.basicFilters = basicFilters;

        // For when folders/groups are a thing
        this.newActions = newActions;
    },
};
