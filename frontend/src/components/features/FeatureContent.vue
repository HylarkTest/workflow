<template>
    <div
        class="o-feature-content relative"
        @dragenter="onDragEnter"
        @dragover="onDragEnter"
        @dragleave="onDragLeave"
        @drop="onDrop"
    >
        <div
            class="sticky z-cover mb-1 bg-cm-00"
            :class="headerClasses"
        >
            <FeatureHeader
                ref="header"
                :hideGrouping="!hasGrouping"
                :list="displayedList"
                :isLoading="shouldShowLoader"
                :sortables="sortables"
                :featureType="featureType"
                :filtersObj="filtersObj"
                :isSideMinimized="isSideMinimized"
                :hasReducedPadding="hasReducedPadding"
                :spaceId="spaceId"
                @update:filtersObj="emitFiltersObj"
                @minimizeSide="$emit('minimizeSide', $event)"
                @saveList="$emit('saveList', $event)"
            >
                <template
                    v-if="!isDisplayedListExternalReadOnly"
                    #newButton
                >
                    <slot
                        name="newButton"
                    >
                        <button
                            class="button button-rounded button-secondary"
                            type="button"
                            @click="addItem"
                        >
                            <i class="fa-solid fa-plus mr-1">
                            </i>

                            {{ $t(itemNamePath) }}

                        </button>
                    </slot>
                </template>
            </FeatureHeader>
        </div>

        <AttachmentsOverlay
            v-if="enableFileDrop && !isModalOpen"
            :hovering="hovering"
            :isImageType="isImageFeature"
        >
        </AttachmentsOverlay>

        <div
            class="pb-6"
            :class="hasReducedPadding ? 'pr-2' : 'px-6'"
        >
            <LoaderFetch
                v-if="shouldShowLoader"
                class="py-10"
                :isFull="true"
                :sphereSize="40"
                bgColorClass="bg-secondary-200"
            >
            </LoaderFetch>

            <div
                v-else-if="activeQueryResults"
            >
                <component
                    :is="itemsComponent"
                    v-model:showCompleted="customFilters.showCompleted"
                    v-model:dateRange="customFilters.dateRange"
                    :featureType="featureType"
                    :displayedList="displayedList"
                    :filtersObj="filtersObj"
                    :page="page"
                    :isLoading="isLoading"
                    :selectedItem="modalItem"
                    :defaultAssociations="defaultAssociations"
                    :currentView="currentView"
                    :teleportRef="headerTeleportRef"
                    :forceNoDrag="forceNoDrag"
                    v-bind="itemProps"
                    @showMore="showMore"
                    @openItem="openItemModal"
                >
                    <template
                        #noContentSlot
                    >
                        <NoContentText
                            class="mt-8"
                            :customHeaderPath="customHeaderPath"
                            :customMessagePath="customMessagePath"
                            :customIcon="featureIcon"
                        >
                            <template
                                v-if="hasRequestFilter"
                                #graphic
                            >
                                <BirdImage
                                    class="h-20"
                                    :whichBird="whichBird"
                                >
                                </BirdImage>
                            </template>
                        </NoContentText>
                    </template>
                </component>
            </div>
        </div>
    </div>
</template>

<script>
import LinkItems from '@/components/links/LinkItems.vue';
import PinItems from '@/components/pinboard/PinItems.vue';
import NoteItems from '@/components/notes/NoteItems.vue';
import DocumentItems from '@/components/documents/DocumentItems.vue';
import TodoItems from '@/components/todos/TodoItems.vue';
import EventItems from '@/components/events/EventItems.vue';

import AttachmentsOverlay from '@/components/documents/AttachmentsOverlay.vue';

import FeatureHeader from '@/components/features/FeatureHeader.vue';

import providesFilterProperties from '@/vue-mixins/providesFilterProperties.js';
import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import interactsWithFileDrop from '@/vue-mixins/interactsWithFileDrop.js';

import { featureIcons } from '@/core/display/featureIcons.js';

import LINKS from '@/graphql/links/queries/Links.gql';
import LINK_CREATED from '@/graphql/links/subscriptions/LinkCreated.gql';
import LINK_UPDATED from '@/graphql/links/subscriptions/LinkUpdated.gql';
import LINK_DELETED from '@/graphql/links/subscriptions/LinkDeleted.gql';
import GROUPED_LINKS from '@/graphql/links/queries/GroupedLinks.gql';
import LINK_STATS from '@/graphql/links/queries/LinkStats.gql';
import { initializeLinks } from '@/core/repositories/linkRepository.js';

import PINS from '@/graphql/pinboard/queries/Pins.gql';
import PIN_CREATED from '@/graphql/pinboard/subscriptions/PinCreated.gql';
import PIN_UPDATED from '@/graphql/pinboard/subscriptions/PinUpdated.gql';
import PIN_DELETED from '@/graphql/pinboard/subscriptions/PinDeleted.gql';
import GROUPED_PINS from '@/graphql/pinboard/queries/GroupedPins.gql';
import PINBOARD_STATS from '@/graphql/pinboard/queries/PinStats.gql';
import { initializePins } from '@/core/repositories/pinRepository.js';

import NOTES from '@/graphql/notes/queries/Notes.gql';
import NOTE_CREATED from '@/graphql/notes/subscriptions/NoteCreated.gql';
import NOTE_UPDATED from '@/graphql/notes/subscriptions/NoteUpdated.gql';
import NOTE_DELETED from '@/graphql/notes/subscriptions/NoteDeleted.gql';
import GROUPED_NOTES from '@/graphql/notes/queries/GroupedNotes.gql';
import NOTE_STATS from '@/graphql/notes/queries/NoteStats.gql';
import { initializeNotes } from '@/core/repositories/noteRepository.js';

import DOCUMENTS from '@/graphql/documents/queries/Documents.gql';
import DOCUMENT_CREATED from '@/graphql/documents/subscriptions/DocumentCreated.gql';
import DOCUMENT_UPDATED from '@/graphql/documents/subscriptions/DocumentUpdated.gql';
import DOCUMENT_DELETED from '@/graphql/documents/subscriptions/DocumentDeleted.gql';
import GROUPED_DOCUMENTS from '@/graphql/documents/queries/GroupedDocuments.gql';
import DOCUMENT_STATS from '@/graphql/documents/queries/DocumentStats.gql';
import { initializeDocuments } from '@/core/repositories/documentRepository.js';

import TODOS from '@/graphql/todos/queries/Todos.gql';
import TODO_CREATED from '@/graphql/todos/subscriptions/TodoCreated.gql';
import TODO_UPDATED from '@/graphql/todos/subscriptions/TodoUpdated.gql';
import TODO_DELETED from '@/graphql/todos/subscriptions/TodoDeleted.gql';
import EXTERNAL_TODOS from '@/graphql/todos/queries/ExternalTodos.gql';
import EXTERNAL_TODO_LISTS from '@/graphql/todos/queries/ExternalTodoLists.gql';
import GROUPED_TODOS from '@/graphql/todos/queries/GroupedTodos.gql';
import TODO_STATS from '@/graphql/todos/queries/TodoStats.gql';
import { initializeTodos } from '@/core/repositories/todoRepository.js';

import EVENTS from '@/graphql/calendar/queries/Events.gql';
import EVENT_CREATED from '@/graphql/calendar/subscriptions/EventCreated.gql';
import EVENT_UPDATED from '@/graphql/calendar/subscriptions/EventUpdated.gql';
import EVENT_DELETED from '@/graphql/calendar/subscriptions/EventDeleted.gql';
// import GROUPED_EVENTS from '@/graphql/calendar/queries/GroupedEvents.gql';
import EXTERNAL_EVENTS from '@/graphql/calendar/queries/ExternalEvents.gql';
import EXTERNAL_CALENDARS from '@/graphql/calendar/queries/ExternalCalendars.gql';
import { checkAndHandleMissingError } from '@/http/exceptionHandler.js';
import { initializeEvents } from '@/core/repositories/eventRepository.js';
import { getFirstKey } from '@/core/utils.js';
import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';

import { getCachedOperationNames } from '@/core/helpers/apolloHelpers.js';

import useFeatureItemModal from '@/composables/useFeatureItemModal.js';

const featureMap = {
    LINKS: {
        query: LINKS,
        listIdKey: 'linkListId',
        component: 'LinkItems',
        initializePointer: initializeLinks,
        connectionKey: '__LinkConnection',
        itemsName: 'links',
        groupedQuery: GROUPED_LINKS,
        groupedName: 'groupedLinks',
        subscriptions: [
            LINK_CREATED,
            LINK_UPDATED,
            LINK_DELETED,
        ],
        statsQuery: LINK_STATS,
    },
    PINBOARD: {
        query: PINS,
        listIdKey: 'pinboardId',
        component: 'PinItems',
        initializePointer: initializePins,
        connectionKey: '__PinConnection',
        itemsName: 'pins',
        groupedQuery: GROUPED_PINS,
        groupedName: 'groupedPins',
        subscriptions: [
            PIN_CREATED,
            PIN_UPDATED,
            PIN_DELETED,
        ],
        statsQuery: PINBOARD_STATS,
    },
    NOTES: {
        query: NOTES,
        listIdKey: 'notebookId',
        component: 'NoteItems',
        initializePointer: initializeNotes,
        connectionKey: '__NoteConnection',
        itemsName: 'notes',
        groupedQuery: GROUPED_NOTES,
        groupedName: 'groupedNotes',
        subscriptions: [
            NOTE_CREATED,
            NOTE_UPDATED,
            NOTE_DELETED,
        ],
        statsQuery: NOTE_STATS,
    },
    DOCUMENTS: {
        query: DOCUMENTS,
        listIdKey: 'driveId',
        component: 'DocumentItems',
        initializePointer: initializeDocuments,
        connectionKey: '__DocumentConnection',
        itemsName: 'documents',
        groupedQuery: GROUPED_DOCUMENTS,
        groupedName: 'groupedDocuments',
        subscriptions: [
            DOCUMENT_CREATED,
            DOCUMENT_UPDATED,
            DOCUMENT_DELETED,
        ],
        statsQuery: DOCUMENT_STATS,
    },
    EVENTS: {
        query: EVENTS,
        externalQuery: EXTERNAL_EVENTS,
        externalRefetchQuery: EXTERNAL_CALENDARS,
        // groupedQuery: GROUPED_EVENTS,
        // groupedName: 'groupedEvents',
        subscriptions: [
            EVENT_CREATED,
            EVENT_UPDATED,
            EVENT_DELETED,
        ],
        dateRangeKeys: ['endsAfter', 'startsBefore'],
        listIdKey: 'calendarId',
        disableLoadHandling: true,
        component: 'EventItems',
        initializePointer: initializeEvents,
        connectionKey: '__EventConnection',
        itemsName: 'events',
        externalItemsName: 'externalEvents',
        customVariablesProperty: 'dateRangeVariables',
        customFilters: ['DATE_RANGE'],
        extraProps: {
            onNewEvent(day) {
                this.addItem({ time: day });
            },
        },
    },
    TODOS: {
        query: TODOS,
        groupedQuery: GROUPED_TODOS,
        externalQuery: EXTERNAL_TODOS,
        externalRefetchQuery: EXTERNAL_TODO_LISTS,
        subscriptions: [
            TODO_CREATED,
            TODO_UPDATED,
            TODO_DELETED,
        ],
        dateRangeKeys: ['dueAfter', 'dueBefore'],
        listIdKey: 'todoListId',
        component: 'TodoItems',
        initializePointer: initializeTodos,
        connectionKey: '__TodoConnection',
        itemsName: 'todos',
        externalItemsName: 'externalTodos',
        groupedName: 'groupedTodos',
        customVariablesProperty: 'completedVariables',
        customFilters: ['COMPLETED'],
        customNoContent: {
            bird: 'UsingHylarkBird_72dpi.png',
        },
        statsQuery: TODO_STATS,
    },
};

export default {
    name: 'FeatureContent',
    components: {
        LinkItems,
        PinItems,
        NoteItems,
        DocumentItems,
        FeatureHeader,
        AttachmentsOverlay,
        TodoItems,
        EventItems,
    },
    mixins: [
        providesFilterProperties,
        interactsWithModal,
        interactsWithFileDrop,
        interactsWithApolloQueries,
    ],
    props: {
        defaultAssociations: {
            type: [Array, null],
            default: null,
        },
        displayedList: {
            type: [Object, null],
            default: null,
        },
        filtersObj: {
            type: Object,
            required: true,
        },
        page: {
            type: [Object, null],
            default: null,
        },
        node: {
            type: [Object, null],
            default: null,
        },
        forceNoDrag: Boolean,
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
        topHeaderClass: {
            type: String,
            default: 'stickies--body',
        },
        spaceId: {
            type: [String, null],
            default: null,
        },
        isSideMinimized: Boolean,
        hasReducedPadding: Boolean,
        enableFileDrop: Boolean,
        currentView: {
            type: [Object, null],
            default: null,
        },
        sortables: {
            type: [Array, null],
            required: true,
        },
    },
    emits: [
        'saveList',
        'minimizeSide',
        'addFile',
        'update:filtersObj',
    ],
    setup(props) {
        const {
            featureItemFormKey,
            featureListFormKey,
            createFeatureFormModal,
        } = useFeatureItemModal(props);

        return {
            featureItemFormKey,
            featureListFormKey,
            createFeatureFormModal,
        };
    },
    apollo: {
        items: {
            query() {
                return this.featureInfoObj.query;
            },
            skip() {
                // This cannot use the computed property because it screws with
                // reactivity and ends up fetching the query when it should be
                // skipped.
                return !!this.filtersObj.currentGroup
                    || (this.displayedList?.isExternalList()
                        && (!this.hasActiveFilters));
            },
            variables() {
                return this.queryVariables;
            },
            update(data) {
                return this.featureInfoObj.initializePointer(data);
            },
        },
        externalItems: {
            query() {
                return this.featureInfoObj.externalQuery;
            },
            skip() {
                // This cannot use the computed property because it screws with
                // reactivity and ends up fetching the query when it should be
                // skipped.
                return !this.displayedList?.isExternalList()
                    || !!this.hasActiveFilters;
            },
            update(data) {
                return this.featureInfoObj.initializePointer(data);
            },
            variables() {
                return this.externalQueryVariables;
            },
            subscription() {
                return this.querySubscription;
            },
        },
        groupedItems: {
            query() {
                // return buildPriorityGroupQuery();
                return this.featureInfoObj.groupedQuery;
            },
            skip() {
                return !this.featureInfoObj.groupedQuery || !this.filtersObj.currentGroup;
            },
            update(data) {
                return this.featureInfoObj.initializePointer(data);
            },
            variables() {
                return this.groupedQueryVariables;
            },
            subscription() {
                return this.querySubscription;
            },
        },
    },
    data() {
        return {
            editNameMode: false,
            form: this.$apolloForm(() => ({
                id: this.displayedList?.id,
                name: this.displayedList?.name,
                color: this.displayedList?.color,
            })),
            modalItem: null,
            // Specifics
            customFilters: {
                showCompleted: false,
                dateRange: null,
            },
            headerTeleportRef: null,
            formModalProps: null,
        };
    },
    computed: {
        // Headers
        headerClasses() {
            return [
                this.topHeaderClass,
                { 'rounded-t-2xl': !this.hasReducedPadding },
            ];
        },

        // Items
        hasFilteredItems() {
            return !!this.activeQueryResults?.length;
        },

        // Loading
        shouldShowLoader() {
            return (!this.featureInfoObj.disableLoadHandling
                && this.isLoading)
                || !this.headerTeleportRef;
        },
        isLoading() {
            return this.$isLoadingQueriesFirstTimeOrFromChange([
                'externalItems',
                'items',
                'groupedItems',
            ]);
        },
        // Feature info properties
        featureInfoObj() {
            return featureMap[this.featureType];
        },
        featureIcon() {
            return featureIcons[this.featureType].icon;
        },
        hasGrouping() {
            if (this.featureInfoObj.hasGrouping) {
                return this.featureInfoObj.hasGrouping.call(this);
            }
            if (this.hasExternalFunctionality) {
                return false;
            }
            return !!this.featureInfoObj.groupedQuery;
        },
        connectionKey() {
            return this.featureInfoObj.connectionKey;
        },
        requestInfo() {
            return this.items?.[this.connectionKey].pageInfo;
        },
        hasRequestFilter() {
            return !this.hasExternalFunctionality && this.requestInfo?.hasFilterApplied;
        },
        itemProps() {
            const obj = {
                [this.featureInfoObj.itemsName]: this.items,
            };
            const external = this.featureInfoObj.externalItemsName;
            if (external) {
                obj[external] = this.externalItems;
            }
            const grouped = this.featureInfoObj.groupedName;
            if (grouped) {
                obj[grouped] = this.groupedItems;
            }
            const extras = this.featureInfoObj.extraProps;
            if (extras) {
                _.forEach(extras, (prop, key) => {
                    obj[key] = _.isFunction(prop) ? prop.bind(this) : prop;
                });
                // extras.forEach((prop) => {
                //     obj[prop.propKey] = _.get(this, prop.propPointer);
                // });
            }
            return obj;
        },
        itemsComponent() {
            return this.featureInfoObj.component;
        },
        customHeaderPath() {
            const baseLangKey = `features.${this.featureTypeFormatted}.noContent.`;
            if (this.hasContentFilters) {
                return 'common.noFilterResults';
            } if (this.mainFilter && this.mainFilter !== 'all') {
                return baseLangKey + this.mainFilter;
            }
            return `${baseLangKey}header`;
        },
        customMessagePath() {
            return this.hasContentFilters
                ? ''
                : `features.${this.featureTypeFormatted}.noContent.description`;
        },
        featureCustomNoContent() {
            return this.featureInfoObj.customNoContent;
        },
        whichBird() {
            if (this.featureCustomNoContent?.bird) {
                return this.featureCustomNoContent.bird;
            }
            return 'MagnifyingGlassBird_72dpi.png';
        },
        itemNamePath() {
            return `features.${this.featureTypeFormatted}.itemName`;
        },
        featureTypeFormatted() {
            return _.camelCase(this.featureType);
        },
        isImageFeature() {
            return this.featureType === 'PINBOARD';
        },

        // External
        hasExternalFunctionality() {
            return this.displayedList?.isExternalList() && !this.hasActiveFilters;
        },
        isDisplayedListExternalReadOnly() {
            return this.hasExternalFunctionality && this.displayedList?.isReadOnly;
        },

        // Query things
        groupedQueryVariables() {
            return this.generateVariables(true);
        },
        queryVariables() {
            return this.generateVariables();
        },
        externalQueryVariables() {
            const customVariables = this.generateCustomVariables(this.featureInfoObj, true);
            const obj = {
                sourceId: this.displayedList?.account?.id,
                [this.featureInfoObj.listIdKey]: this.displayedList?.id,
                ...(customVariables || {}),
            };
            if (this.node) {
                obj.forNode = this.node.id;
            }
            return obj;
        },
        activeQueryResults() {
            if (this.filtersObj.currentGroup) {
                return this.groupedItems?.groups;
            }
            if (this.hasExternalFunctionality) {
                return this.externalItems?.data;
            }
            return this.items;
        },
    },
    methods: {
        async openItemModal(node) {
            const openable = !!node || !this.isDisplayedListExternalReadOnly;
            if (openable) {
                let canOpenModal = true;
                if (this.hasExternalFunctionality && typeof node === 'undefined') {
                    await this.$apollo.queries.externalItems.refetch().catch((error) => {
                        if (!checkAndHandleMissingError(error, false)) {
                            throw error;
                        }
                        this.$apollo.getClient().refetchQueries({
                            include: [this.featureInfoObj.externalRefetchQuery],
                        });
                        canOpenModal = false;
                    });
                }
                if (canOpenModal) {
                    this.createFeatureFormModal({
                        [this.featureItemFormKey]: node || null,
                        [this.featureListFormKey]: this.displayedList,
                        ...this.formModalProps,
                    });
                }
            }
        },
        closeItemModal() {
            this.closeModal();
            this.modalItem = null;
            this.formModalProps = null;
        },
        async showMore(grouping) {
            if (this.hasExternalFunctionality) {
                await this.$apollo.queries.externalItems.fetchMore({
                    page: this.externalItems.paginatorInfo.page + 1,
                });
            } else {
                const query = this.filtersObj.currentGroup
                    ? this.$apollo.queries.groupedItems
                    : this.$apollo.queries.items;
                const variables = {
                    after: this.items[this.connectionKey].pageInfo.endCursor,
                };
                if (this.filtersObj.currentGroup) {
                    variables.includeGroups = [grouping.header.val];
                }
                await query.fetchMore({ variables });
            }
        },
        addItem(extraProps) {
            this.formModalProps = extraProps;
            this.openItemModal();
        },
        addFile(file) {
            if (this.enableFileDrop) {
                this.addItem({ file });
            }
        },
        emitFiltersObj(event) {
            this.$emit('update:filtersObj', event);
        },

        // Variables
        generateVariables(isGrouped = false) {
            const featureInfoObj = featureMap[this.featureType];

            const orderByArr = this.generateOrderByVariables(featureInfoObj);

            const customVariables = this.generateCustomVariables(featureInfoObj);

            const filter = {};

            const search = this.filtersObj.freeText;

            const variables = {
                filters: [filter],
                search: search ? [search] : null,
                orderBy: orderByArr,
                ...(customVariables || {}),
            };

            if (this.page?.mapping) {
                variables.forMapping = this.page.mapping.id;
            }
            if (this.node) {
                variables.forNode = this.node.id;
            }
            if (isGrouped) {
                variables.group = this.filtersObj.currentGroup;
            }

            if (this.filtersObj.discreteFilters?.MARKERS) {
                filter.markers = _.map(this.filtersObj.discreteFilters.MARKERS, (marker) => ({
                    markerId: marker.filter.id,
                }));
            }

            if (this.page?.space) {
                variables.spaceId = this.page.space.id;
            }

            if (this.filtersObj.filter) {
                if (this.filtersObj.filter === 'today') {
                    const [afterKey, beforeKey] = featureInfoObj.dateRangeKeys;
                    variables[afterKey] = this.$dayjs().startOf('day').utc().toISOString();
                    variables[beforeKey] = this.$dayjs().startOf('day').utc().add(1, 'day')
                        .toISOString();
                } else if (this.filtersObj.filter === 'scheduled') {
                    variables.isScheduled = true;
                } else if (this.filtersObj.filter === 'highPriority') {
                    variables.maxPriority = 1;
                } else if (this.filtersObj.filter === 'overdue') {
                    variables.dueBefore = this.$dayjs().utc().toISOString();
                } else if (this.filtersObj.filter === 'favorites') {
                    variables.isFavorited = true;
                }
            } else {
                variables[this.featureInfoObj.listIdKey] = this.displayedList?.id;
            }
            return variables;
        },
        generateOrderByVariables(featureInfoObj) {
            // Sorting variables
            const generalSorting = {
                direction: this.filtersObj.sortOrder.direction || 'DESC',
                field: this.filtersObj.sortOrder.value,
            };

            const orderByArr = [generalSorting];

            const hasManualSorting = this.sortables?.includes('MANUAL');
            const hasCompletedCustomFilter = featureInfoObj.customFilters?.includes('COMPLETED');

            if (hasManualSorting && hasCompletedCustomFilter) {
                const manualSorting = this.filtersObj.sortOrder?.value === 'MANUAL'
                    ? { direction: 'DESC', field: 'COMPLETED_AT' }
                    : { direction: 'ASC', field: 'IS_COMPLETED' };

                orderByArr.unshift(manualSorting);
            }

            return orderByArr;
        },
        generateCustomVariables(featureInfoObj, forExternal = false) {
            const customVariablesProperty = featureInfoObj.customVariablesProperty;
            if (customVariablesProperty && this[customVariablesProperty]) {
                return this[customVariablesProperty](forExternal);
            }
            return null;
        },
        dateRangeVariables() {
            const dateRange = this.customFilters.dateRange;
            if (dateRange) {
                return {
                    includeRecurringInstances: true,
                    endsAfter: dateRange[0],
                    startsBefore: dateRange[1],
                };
            }
            return null;
        },
        completedVariables(forExternal) {
            const showCompleted = this.customFilters.showCompleted;
            if (forExternal) {
                return {
                    filter: showCompleted
                        ? 'ALL'
                        : 'ONLY_INCOMPLETE',
                };
            }
            if (this.customFilters.showCompleted) {
                return null;
            }
            return { isCompleted: false };
        },
    },
    watch: {
        hasActiveFilters(val, oldVal) {
            if (val && !oldVal && this.featureInfoObj.groupedQuery) {
                this.$emit('update:filtersObj', {
                    ...this.filtersObj,
                    currentGroup: 'LIST',
                });
            } else if (!val && oldVal && this.filtersObj.currentGroup === 'LIST') {
                this.$emit('update:filtersObj', {
                    ...this.filtersObj,
                    currentGroup: null,
                });
            }
        },
    },
    created() {
        // This needs to be added here otherwise the subscriptions are added
        // every time the query changes which is not necessary.
        const subscriptions = this.featureInfoObj.subscriptions;
        const client = this.$apollo.provider.defaultClient;
        if (subscriptions) {
            subscriptions.forEach((query, index) => {
                this.$apollo.addSmartSubscription(`itemsChanged${index}`, {
                    query,
                    variables: () => {
                        return {
                            forMapping: this.page?.mapping?.id,
                            forNode: this.node?.id,
                        };
                    },
                    result: async ({ data }) => {
                        if (!_.isEmpty(getFirstKey(data))) {
                            await Promise.all([
                                this.$apollo.queries.items.refetch(),
                                this.$apollo.queries.groupedItems.refetch(),
                                ...(this.featureInfoObj.statsQuery ? [
                                    client.refetchQueries({
                                        include: getCachedOperationNames([this.featureInfoObj.statsQuery], client),
                                    }),
                                ] : []),
                            ]);
                        }
                    },
                });
            });
        }
    },
    mounted() {
        this.headerTeleportRef = this.$refs.header.$refs.headerTeleport;
    },
};
</script>

<style scoped>

/*.o-feature-content {

} */

</style>
