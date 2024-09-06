<template>
    <div class="c-side-content relative">
        <div
            v-if="basicFilters?.length"
            class="mt-2 mb-4 text-sm"
        >
            <FilterOption
                v-for="filter in basicFilters"
                :key="filter.id"
                :option="filter"
                :filterCount="getFilterCount(filter)"
                :isActive="filtersObj.filter === filter.id"
                @click="toggleFilter(filter)"
            >
            </FilterOption>

        </div>

        <div
            v-if="!hideFreeFilter"
            class="flex items-center mt-2 mb-8"
        >
            <FreeFilter
                class="flex-1"
                :freePlaceholder="freePlaceholder"
                :modelValue="filtersObj.freeText"
                @update:modelValue="updateSearch"
            >
            </FreeFilter>

            <FilterButton
                v-if="hasFilterables"
                class="ml-2"
                :filterables="filterables"
                :sortables="sortables"
                :modelValue="filtersObj"
                :mapping="null"
                :page="page"
                :featureType="featureType"
                @update:modelValue="emitFilters"
            >

            </FilterButton>
        </div>

        <div>
            <div class="flex justify-between items-end mb-2">
                <h4
                    v-t="listName"
                    class="font-semibold text-primary-950 text-xl"
                >
                </h4>

                <div class="flex items-center">
                    <button
                        v-if="showPageSettings"
                        type="button"
                        class="c-side-content__edit centered transition-2eio"
                        @click="$emit('editPageSettings')"
                    >
                        <i
                            class="fa fa-pencil-alt text-xs"
                        >
                        </i>
                    </button>

                    <div
                        v-if="!hideAddList"
                        ref="blurParent"
                        v-blur="closeNewListDropdown"
                        class="relative"
                    >
                        <slot
                            name="actionButtons"
                        >
                            <AddCircle
                                ref="add"
                                @click="addNew"
                            >
                            </AddCircle>

                            <PopupBasic
                                v-if="newListDropdown"
                                :activator="$refs.add"
                                :blurParent="$refs.blurParent"
                                nudgeDownProp="0.375rem"
                                nudgeRightProp="0.625rem"
                                alignRight
                            >
                                <button
                                    v-for="option in addNewOptions"
                                    :key="option.name"
                                    type="button"
                                    class="c-side-content__option c-side-content__name"
                                    @click="selectNewListSource(option)"
                                >
                                    <i
                                        v-if="option.provider"
                                        class="text-cm-300 mr-1"
                                        :class="integrationIcon(option.provider)"
                                    >
                                    </i>

                                    {{ option.name }}
                                </button>
                            </PopupBasic>
                        </slot>
                    </div>
                </div>
            </div>

            <div>
                <div
                    v-for="base in bases"
                    :key="base.id"
                    class="c-side-content__source"
                >
                    <!-- <h5
                        class="c-side-content__name"
                    >
                        {{ base.name }}
                    </h5> -->

                    <div
                        v-if="hasNoLists"
                        class="text-sm p-2 rounded-lg bg-secondary-100"
                    >
                        <p class="mb-2">
                            There are no lists added on this page.
                        </p>

                        <p>
                            Create a new list or add from existing lists!
                        </p>
                    </div>

                    <div>
                        <div
                            v-for="space in sourceSpaces"
                            :key="space.id"
                            class="text-cm-700 mb-4 last:mb-0"
                        >

                            <h5
                                class="c-side-content__name"
                            >
                                {{ space.name }}
                            </h5>

                            <Draggable
                                data-scrollable-box-observable
                                :modelValue="space.lists"
                                itemKey="id"
                                :group="{ name: space.id, pull: false, put: ['listItems'] }"
                                @change="moveItem(space, $event)"
                                @update="moveList(space.lists, $event)"
                            >
                                <template #item="{ element }">
                                    <ListLine
                                        :list="element"
                                        :source="space"
                                        :highlightedList="highlightedList"
                                        :processing="isPendingDelete(element.id)"
                                        :displayedList="displayedList"
                                        v-bind="$attrs"
                                        @update:highlight="updateHighlightedList(element, $event)"
                                    >
                                    </ListLine>
                                </template>
                            </Draggable>
                        </div>
                    </div>

                    <div
                        v-if="hasIntegrations"
                        class="mt-4"
                    >
                        <h5
                            v-t="'links.integrations'"
                            class="c-side-content__name"
                        >
                        </h5>

                        <div
                            v-for="integration in sources.integrations"
                            :key="integration.id"
                            class="mt-2 mb-6 last:mb-0"
                        >
                            <h6
                                class="text-sm text-cm-500 flex items-baseline u-text"
                            >
                                <i
                                    class="fa-fw text-primary-400 mr-1 shrink-0"
                                    :class="integrationIcon(integration.provider)"
                                >
                                </i>
                                {{ integration.name }}
                            </h6>

                            <div
                                v-if="integration.renewalUrl"
                                class="bg-cm-100 text-center text-sm text-cm-500 py-3 px-4 font-semibold rounded-lg"
                            >
                                <div>
                                    This integration needs to be renewed.
                                </div>
                                <div
                                    class="flex justify-center"
                                >
                                    <a
                                        class="button--sm button-secondary text-center mt-2"
                                        :href="integration.renewalUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        Renew
                                    </a>
                                </div>
                            </div>
                            <Draggable
                                v-else
                                data-scrollable-box-observable
                                :modelValue="collapsedIntegrations[integration.id].initial"
                                itemKey="id"
                                :group="{ name: integration.id, pull: false, put: ['listItems'] }"
                                :disabled="true"
                                @change="moveItem(source, $event)"
                                @update="moveList(integration.lists, $event)"
                            >
                                <template #item="{ element }">
                                    <ListLine
                                        :list="element"
                                        :hideColorSquare="hideColorSquare"
                                        :source="integration"
                                        :highlightedList="highlightedList"
                                        :displayedList="displayedList"
                                        :processing="isPendingDelete(element.id)"
                                        v-bind="$attrs"
                                        @update:highlight="updateHighlightedList(element, $event)"
                                    >
                                    </ListLine>
                                </template>
                            </Draggable>

                            <div
                                v-if="hasCollapsed(integration.id)"
                            >
                                <ButtonEl
                                    class="c-side-content__more text-cm-700 hover:text-primary-600"
                                    @click="toggleMoreOpen(integration.id)"
                                >
                                    <i
                                        class="far fa-angle-down mr-3"
                                        :class="isMoreOpen(integration.id) ? 'fa-angle-up' : 'fa-angle-down'"
                                    >
                                    </i>

                                    <div
                                        v-t="isMoreOpen(integration.id) ? 'common.less' : 'common.more'"
                                        class="font-semibold"
                                    >
                                    </div>
                                </ButtonEl>

                                <div
                                    v-if="isMoreOpen(integration.id)"
                                    data-scrollable-box-observable
                                >
                                    <ListLine
                                        v-for="element in collapsedIntegrations[integration.id].collapsed"
                                        :key="element.id"
                                        :list="element"
                                        :source="integration"
                                        :hideColorSquare="hideColorSquare"
                                        :processing="isPendingDelete(element.id)"
                                        :highlightedList="highlightedList"
                                        :displayedList="displayedList"
                                        v-bind="$attrs"
                                        @update:highlight="updateHighlightedList(element, $event)"
                                    >
                                    </ListLine>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Draggable from 'vuedraggable';

import FreeFilter from '@/components/sorting/FreeFilter.vue';
import FilterButton from '@/components/sorting/FilterButton.vue';
import FilterOption from '@/components/buttons/FilterOption.vue';
import ListLine from '@/components/display/ListLine.vue';
import AddCircle from '@/components/buttons/AddCircle.vue';

import { getIntegrationIcon } from '@/core/display/integrationIcons.js';
import { arrRemove, randomNumber } from '@/core/utils.js';

import {
    featureFiltersObj,
    getFeatureFilters,
} from '@/core/display/featureFilters.js';

import MARKER_GROUPS from '@/graphql/markers/queries/MarkerGroups.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import { getIcon } from '@/core/display/typenamesList.js';

const newListObj = {
    id: '',
    name: 'New list',
    count: 0,
    new: true,
    order: null,
};

const newExternalList = {
    id: '',
    name: 'New list',
    new: true,
};

const featureBehaviors = {
    TODOS: {
        hideColorSquare: true,
    },
    EVENTS: {
        hideFreeFilter() {
            return this.viewType === 'CALENDAR';
        },
    },
    EMAILS: {
        hideAddList: true,
        hideColorSquare: true,
        filterables: [],
    },
};

export default {
    name: 'FeatureSide',
    components: {
        FreeFilter,
        FilterButton,
        FilterOption,
        AddCircle,
        ListLine,
        Draggable,
    },
    mixins: [
    ],
    props: {
        filtersObj: {
            type: Object,
            required: true,
        },
        getFilterCount: {
            type: Function,
            required: true,
        },
        sources: {
            type: Object,
            required: true,
        },
        displayedList: {
            type: Object,
            default: null,
        },
        showPageSettings: Boolean,
        stopReordering: Boolean,
        canMoveItemToList: Boolean,
        pendingDelete: {
            type: [Object, null],
            default: null,
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
        currentView: {
            type: [Object, null],
            default: null,
        },
        availableSources: {
            type: Object,
            required: true,
        },
        sortables: {
            type: [Array, null],
            default: null,
        },
        page: {
            type: [Object, null],
            default: null,
        },
        freePlaceholder: {
            type: [String, null],
            default: 'Search by name',
        },
        contextSideFilters: {
            type: [Array, null],
            default: null,
        },
    },
    emits: [
        'update:filtersObj',
        'moveList',
        'moveItem',
        'addNewList',
        'editPageSettings',
    ],
    apollo: {
        markerGroups: {
            query: MARKER_GROUPS,
            variables() {
                return this.featureType ? { usedByFeatures: [this.featureType] } : {};
            },
            update: (data) => initializeConnections(data).markerGroups,
        },
    },
    data() {
        return {
            highlightedList: null,
            openViewMore: [],
            newListDropdown: false,
            bases: [{}],
        };
    },
    computed: {
        filterables() {
            if (this.featureFilterables) {
                return this.featureFilterables;
            }
            if (this.markerGroups?.length) {
                return [
                    {
                        namePath: 'labels.markers',
                        val: 'MARKERS',
                        options: this.markerGroups?.map((group) => ({
                            icon: getIcon(group.type),
                            name: group.name,
                            items: group.items,
                        })),
                    },
                ];
            }
            return null;
        },
        sourceSpaces() {
            return this.sources.spaces;
        },
        sourceSpacesLength() {
            return this.sourceSpaces?.length || 0;
        },
        hasNoLists() {
            return !this.sourceSpacesLength && !this.hasIntegrations;
        },
        integrations() {
            return this.sources.integrations;
        },
        hasIntegrations() {
            return this.integrations && this.integrations.length;
        },
        collapsedIntegrations() {
            return _(this.integrations).map((integration) => {
                const collapsed = integration.lists.filter((list) => {
                    return list.isCollapsed;
                });
                const initial = integration.lists.filter((list) => {
                    return !list.isCollapsed;
                });
                return [
                    integration.id,
                    {
                        initial,
                        collapsed,
                    },
                ];
            }).fromPairs().value();
        },
        hasFilterables() {
            return this.filterables?.length;
        },
        viewType() {
            return this.currentView?.viewType;
        },
        basicFilters() {
            if (this.contextSideFilters) {
                return getFeatureFilters(this.contextSideFilters);
            }
            const featureVal = featureFiltersObj[this.featureType];
            if (_.isArray(featureVal)) {
                return featureVal || [];
            }
            if (_.isObject(featureVal) && this.currentView) {
                return featureVal[this.viewType] || [];
            }
            return [];
        },
        listName() {
            return `features.${this.featureTypeFormatted}.listName`;
        },
        featureTypeFormatted() {
            return _.camelCase(this.featureType);
        },
        combinedSources() {
            // Generated based on the lists, with the subset page
            // spaces or integrations available
            const sourcesArr = this.sourceSpacesLength
                ? this.sources.spaces
                : this.availableSources.spaces;

            const integrationsArr = this.hasIntegrations
                ? this.sources.integrations
                : this.availableSources.integrations;

            return [
                ...sourcesArr || [],
                ...integrationsArr || [],
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
        featureBehaviorObj() {
            return featureBehaviors[this.featureType];
        },
        hideFreeFilter() {
            return this.featureBehaviorCheck('hideFreeFilter');
        },
        hideColorSquare() {
            return this.featureBehaviorCheck('hideColorSquare');
        },
        hideAddList() {
            return this.featureBehaviorCheck('hideAddList');
        },
        featureFilterables() {
            return this.featureBehaviorCheck('filterables');
        },
    },
    methods: {
        // General
        integrationIcon(val) {
            return getIntegrationIcon(val);
        },
        featureBehaviorCheck(keyToCheck) {
            if (!this.featureBehaviorObj) {
                return false;
            }
            const val = this.featureBehaviorObj[keyToCheck];
            if (_.isFunction(val)) {
                return val.call(this);
            }
            return val;
        },

        // Search and filtering
        updateSearch(val) {
            this.$proxyEvent(val, this.filtersObj, 'freeText', 'update:filtersObj');
        },
        toggleFilter(filter) {
            const val = filter.id === this.filtersObj.filter ? null : filter.id;
            this.$proxyEvent(val, this.filtersObj, 'filter', 'update:filtersObj');
        },
        emitFilters(filter) {
            this.$emit('update:filtersObj', filter);
        },

        // Update and reorder functions
        moveList(lists, event) {
            this.$emit('moveList', { list: lists[event.oldIndex], from: event.oldIndex, to: event.newIndex });
        },
        // If the changed item is a todo item then we emit up so the parent can
        // add it to TodoMain.
        // Ideally I would like to create a sortable plugin that handles this
        // and allows sortable items to "swallow" other items, but for now this
        // will do.
        moveItem(source, event) {
            if (this.canMoveItemToList && this.highlightedList) {
                const list = this.highlightedList;
                this.$emit('moveItem', { source, list, item: event.added.element });
                this.highlightedList = null;
            }
        },
        // This component is in charge of which list is highlighted (which
        // occurs when a todo item is moved onto the list).
        // Only one list can be highlighted at once but the lists can emit when
        // they are highlighted as the list element is what the sortable
        // plugin has access to.
        //
        updateHighlightedList(list, isHighlighted) {
            this.highlightedList = isHighlighted ? list : null;
        },
        addNew() {
            if (this.addNewOptions?.length === 1) {
                this.selectNewListSource(this.addNewOptions[0]);
            } else {
                this.newListDropdown = !this.newListDropdown;
            }

            // this.$emit('addNew', this.$refs.add.$el);
        },
        selectNewListSource(source) {
            this.closeNewListDropdown();
            const clone = this.getNewClone(source, newListObj, newExternalList);

            this.$emit('addNewList', { newList: clone, source });
        },

        closeNewListDropdown() {
            this.newListDropdown = false;
        },
        getNextOrder(source) {
            const highest = _.maxBy(source.list, 'order');
            return highest + 1;
        },
        getNewClone(source, newList, newExternalListObj) {
            const clone = _.clone(source.provider ? newExternalListObj : newList);

            clone.id = randomNumber();

            if (_.has(clone, 'order')) {
                clone.order = this.getNextOrder(source);
            }
            return clone;
        },

        hasCollapsed(id) {
            return this.collapsedIntegrations[id].collapsed?.length;
        },
        isMoreOpen(id) {
            return this.openViewMore.includes(id);
        },
        toggleMoreOpen(id) {
            if (this.isMoreOpen(id)) {
                this.openViewMore = arrRemove(this.openViewMore, id);
            } else {
                this.openViewMore.push(id);
            }
        },
        isPendingDelete(id) {
            return !!(this.pendingDelete?.id === id);
        },
    },
    created() {
    },
};
</script>

<style>
.c-side-content .item-sortable-chosen {
    @apply
        hidden
    ;
}
</style>
<style scoped>

.c-side-content {
    &__edit {
        @apply
            bg-cm-100
            h-6
            mr-2
            rounded-full
            text-cm-500
            w-6
        ;

        &:hover {
            @apply
                bg-cm-200
            ;
        }
    }

    &__source:not(:last-child) {
        @apply
            mb-10
        ;
    }

    &__name {
        @apply
            font-semibold
            mb-1
            text-cm-400
            text-xs
            uppercase
        ;
    }

    &__more {
        transition: 0.2s ease-in-out;

        @apply
            flex
            items-center
            ml-3
            mt-1
            text-xs
        ;
    }

    &__option {
        @apply
            flex
            items-center
            px-4
            py-1
            text-xs
            uppercase
            w-full
        ;

        &:hover {
            @apply
                bg-cm-100
            ;
        }
    }
}

</style>
