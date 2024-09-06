<template>
    <div
        class="o-full-view flex h-full relative"
        :class="{ unclickable: processingDelete }"
    >
        <LoaderFetch
            v-if="isLoading || !fullItem"
            class="w-full justify-center items-center my-12"
        >
        </LoaderFetch>

        <CollapsableMenu
            v-else
            :isSideVisible="isSideVisible"
            :forceResponsiveDisplay="forceResponsiveDisplay"
            @showSide="showSide"
        >
            <template #menu>
                <div class="flex flex-col items-center relative">
                    <div
                        :class="!primaryImage ? 'h-28 w-full' : 'pt-0'"
                    >
                        <ImageOrFallback
                            class="font-bold text-secondary-600 text-2xl"
                            :class="!primaryImage ? 'bg-secondary-200' : (forceResponsiveDisplay ? 'h-32' : 'h-full')"
                            :imageClass="imageClass"
                            :contain="true"
                            :name="systemName"
                            :image="primaryImage?.url"
                        >
                        </ImageOrFallback>
                    </div>

                    <section
                        v-if="hasPrioritiesOrFavorites"
                        class="absolute top-1 right-2 flex"
                    >
                        <div
                            v-if="hasPriorities"
                            class="mx-0.5"
                        >
                            <DisplayerPriority
                                :dataInfo="formattedPriorities"
                                :dataValue="fullItem.priority"
                                :mapping="mapping"
                                :item="fullItem"
                                :isModifiable="true"
                            >
                            </DisplayerPriority>
                        </div>

                        <div
                            v-if="hasFavorites"
                            class="mx-0.5"
                        >
                            <DisplayerFavorite
                                :dataInfo="formattedFavorites"
                                :dataValue="fullItem.isFavorite"
                                :mapping="mapping"
                                :item="fullItem"
                                :isModifiable="true"
                            >
                            </DisplayerFavorite>
                        </div>
                    </section>

                    <section
                        class="px-4 -mt-4 font-medium max-w-full"
                    >
                        <h3
                            v-if="systemName"
                            class="o-full-view__name u-text"
                        >
                            {{ systemName }}
                        </h3>
                    </section>
                </div>

                <div
                    class="py-6"
                >
                    <Tab1
                        :tabs="tabs"
                        :router="useRouter"
                        :selectedTab="selectedTab"
                        :paramKey="paramKey"
                        :forceResponsiveDisplay="forceResponsiveDisplay"
                        @selectTab="selectContentTab($event, useRouter)"
                    >
                    </Tab1>
                </div>
            </template>

            <template #content>
                <div class="px-6 pb-8 flex-1">
                    <div
                        class="o-full-view__header"
                    >
                        <div
                            v-if="showAssignees || showOptions"
                            class="flex justify-end"
                        >
                            <AssigneesPicker
                                v-if="showAssignees"
                                v-model:assigneeGroups="assigneeGroups"
                            >
                            </AssigneesPicker>

                            <ExtrasButton
                                v-if="showOptions"
                                class="ml-1"
                                buttonStyleName="FULL"
                                :showConfirmDelete="true"
                                :options="recordOptions"
                                contextItemType="RECORD"
                                :propPageName="propPageName"
                                :mapping="mapping"
                                :item="fullItem"
                                :behaviors="pageCreateBehaviors"
                                :pageCreatePathsObj="pageCreatePathsObj"
                                :duplicateItemMethod="duplicateRecord"
                                @selectOption="selectOption"
                            >
                            </ExtrasButton>
                        </div>

                        <h3
                            class="text-4xl font-semibold text-cm-600"
                        >
                            {{ selectedName }}
                        </h3>
                    </div>

                    <component
                        :key="fullTab.value"
                        :is="selectedComponent"
                        :currentTab="fullTab"
                        :item="fullItem"
                        :mapping="mapping"
                        :page="page"
                        :topHeaderClass="topHeaderClass"
                    >
                    </component>
                </div>

                <div class="o-full-view__footer gap-x-6 gap-y-2">
                    <DateLabel
                        :date="updatedAt"
                        :includeLabel="true"
                        :fullTime="true"
                        mode="UPDATED_AT"
                        :performer="updatePerformer"
                    >
                    </DateLabel>
                    <DateLabel
                        :date="createdAt"
                        :includeLabel="true"
                        :fullTime="true"
                        :performer="createPerformer"
                    >
                    </DateLabel>

                    <DeleteButton
                        @click="openDeleteConfirmation"
                    >
                    </DeleteButton>
                </div>
            </template>
        </CollapsableMenu>

        <EntityConfirmDelete
            v-if="showDeleteConfirmation"
            :itemName="systemName"
            @cancelDelete="closeDeleteConfirmation"
            @deleteItem="deleteRecord"
        >
        </EntityConfirmDelete>
    </div>
</template>

<script>

import { gql } from '@apollo/client';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import EntityConfirmDelete from '@/components/assets/DeleteConfirmationModal.vue';
import ExtrasButton from '@/components/buttons/ExtrasButton.vue';
import IconButton from '@/components/buttons/IconButton.vue';

import setsTabSelection from '@/vue-mixins/setsTabSelection.js';
import interactsWithEventBus from '@/vue-mixins/interactsWithEventBus.js';
import interactsWithAssigneesPicker from '@/vue-mixins/features/interactsWithAssigneesPicker.js';
import providesApolloFullItem from '@/vue-mixins/providesApolloFullItem.js';
import providesEntityConnectionsInfo from '@/vue-mixins/providesEntityConnectionsInfo.js';
// import interactsWithSupportWidget from '@/vue-mixins/support/interactsWithSupportWidget.js';
import interactsWithCollapsableMenu from '@/vue-mixins/interactsWithCollapsableMenu.js';

import MAPPING from '@/graphql/mappings/queries/Mapping.gql';

import {
    buildItemFragment,
    simpleMappingRequestFeatures,
} from '@/http/apollo/buildMappingRequests.js';

import { updateMappingPage } from '@/core/repositories/pageRepository.js';

import {
    deleteItem,
    duplicateItem,
    ITEM_ASSOCIATED,
    ITEM_DISASSOCIATED,
} from '@/core/repositories/itemRepository.js';
import { allData } from '@/core/display/getAllEntityData.js';
import {
    convertToFunctionalFormat,
} from '@/core/display/theStandardizer.js';
import { TODO_CREATED, TODO_DELETED, TODO_UPDATED } from '@/core/repositories/todoRepository.js';
import { PIN_CREATED, PIN_DELETED } from '@/core/repositories/pinRepository.js';
import { NOTE_CREATED, NOTE_DELETED } from '@/core/repositories/noteRepository.js';
import { LINK_CREATED, LINK_DELETED } from '@/core/repositories/linkRepository.js';
import { EVENT_CREATED, EVENT_DELETED } from '@/core/repositories/eventRepository.js';
import { DOCUMENT_CREATED, DOCUMENT_DELETED } from '@/core/repositories/documentRepository.js';

import {
    fullRecordOptions,
    pageCreateBehaviors,
    getModalHeaders,
    getDefaultPageName,
} from '@/composables/getsItemOptionsHelpers.js';

import usePerformers from '@/composables/usePerformers.js';

const featureEvents = [
    TODO_CREATED,
    TODO_DELETED,
    TODO_UPDATED,
    EVENT_CREATED,
    EVENT_DELETED,
    PIN_CREATED,
    PIN_DELETED,
    NOTE_CREATED,
    NOTE_DELETED,
    LINK_CREATED,
    LINK_DELETED,
    DOCUMENT_CREATED,
    DOCUMENT_DELETED,
    ITEM_ASSOCIATED,
    ITEM_DISASSOCIATED,
];

export default {
    name: 'FullView',
    components: {
        DeleteButton,
        EntityConfirmDelete,
        ExtrasButton,
        IconButton,
    },
    mixins: [
        setsTabSelection,
        interactsWithEventBus,
        providesApolloFullItem,
        providesEntityConnectionsInfo,
        // interactsWithSupportWidget,
        interactsWithAssigneesPicker,
        interactsWithCollapsableMenu,
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        useRouter: Boolean,
        defaultTab: {
            type: String,
            default: '',
        },
        page: {
            type: [Object, null],
            required: true,
        },
        allowRouterTitle: Boolean,
        context: {
            type: String,
            default: 'ENTITIES',
        },
    },
    emits: [
        'entityDeleted',
    ],
    setup() {
        const {
            getPerformerObj,
        } = usePerformers();

        return {
            fullRecordOptions,
            pageCreateBehaviors,
            getPerformerObj,
        };
    },
    apollo: {
        mapping: {
            query: MAPPING,
            variables() {
                if (this.item?.mapping?.id) {
                    return {
                        id: this.item.mapping.id,
                    };
                }
                return {
                    itemId: this.item.id,
                };
            },
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
            selectedTab: this.defaultTab || 'INFO',
            componentKey: 'Entity',
            paramKey: 'tab',
            isReloading: false,
            listeners: {
                refetchOnFeatureChange: featureEvents,
            },
            processingDelete: false,
            showDeleteConfirmation: false,
            // supportPropsObj: {
            //     sectionName: 'Record',
            //     sectionTitle: 'A record',
            //     val: 'RECORD',
            // },
        };
    },
    computed: {
        itemId() {
            return this.fullItem?.id || this.item.id;
        },
        routerTitle() {
            return this.systemName;
        },
        primaryImage() {
            return this.fullItem?.image;
        },
        systemName() {
            return this.fullItem?.name;
        },
        routeTab() {
            return this.$route.params.tab;
        },
        isLoading() {
            return !this.isReloading
                && (this.$apollo.queries.mapping.loading
                || this.$apollo.queries.fullItem?.loading);
        },
        fullTab() {
            const routeTab = this.routeTab;
            return this.allTabs.find((tab) => {
                if (this.useRouter) {
                    return tab.value === routeTab
                        || tab.value === _.upperSnake(this.routeTab);
                }
                return tab.value === this.selectedTab;
            });
        },
        allTabs() {
            return _(this.tabs).flatMap((tab) => {
                if (tab.sub) {
                    return _.map(tab.sub);
                }
                return tab;
            }).value();
        },
        selectedName() {
            return this.fullTab?.longName;
        },
        mappingType() {
            return this.mapping?.type;
        },
        isPerson() {
            return this.mappingType === 'PERSON';
        },
        infoTabName() {
            const textKey = this.isPerson ? 'profile' : 'info';
            return this.$t(`labels.${textKey}`);
        },
        infoTabIcon() {
            return this.isPerson ? 'fa-square-user' : 'fa-memo-circle-info';
        },
        tabOptions() {
            const link = this.page ? 'entityPage' : 'recordPage';
            const tabs = [
                {
                    value: 'INFO',
                    name: this.infoTabName,
                    icon: this.infoTabIcon,
                    paramName: 'info',
                    link,
                },
            ];

            if (this.featureTabs?.length) {
                tabs.push({
                    label: true,
                    name: 'Features',
                    sub: this.featureTabs,
                });
            }

            if (this.relationshipsTabs?.length) {
                tabs.push({
                    label: true,
                    name: 'Relationships',
                    sub: this.relationshipsTabs,
                });
            }

            tabs.push({
                name: 'History',
                value: 'HISTORY',
                icon: 'fa-history',
                link,
                paramName: 'history',
            });

            return tabs;
        },
        tabs() {
            return this.tabOptions;
        },
        allAvailableData() {
            return this.mapping ? allData(this.mapping) : {};
        },
        features() {
            return this.allAvailableData.FEATURES;
        },
        filteredFeatures() {
            return this.features?.filter((feature) => {
                return !['PRIORITIES', 'FAVORITES'].includes(feature.val);
            });
        },
        featureTabs() {
            return this.getSectionInfo(this.filteredFeatures, 'FEATURES');
        },
        hasPrioritiesOrFavorites() {
            return this.hasPriorities || this.hasFavorites;
        },
        hasPriorities() {
            return _.find(this.features, { val: 'PRIORITIES' });
        },
        formattedPriorities() {
            if (!this.hasPriorities) {
                return null;
            }
            return convertToFunctionalFormat('FEATURES', this.hasPriorities, this.hasPriorities.displayOptions[0]);
        },
        formattedFavorites() {
            if (!this.hasFavorites) {
                return null;
            }
            return convertToFunctionalFormat('FEATURES', this.hasFavorites, this.hasFavorites.displayOptions[0]);
        },
        hasFavorites() {
            return _.find(this.features, { val: 'FAVORITES' });
        },
        relationships() {
            return this.allAvailableData.RELATIONSHIPS;
        },
        relationshipsTabs() {
            return this.getSectionInfo(this.relationships, 'RELATIONSHIPS');
        },
        createdAt() {
            return this.fullItem?.createdAt;
        },
        updatedAt() {
            return this.fullItem?.updatedAt;
        },
        selectedComponent() {
            if (this.fullTab?.section === 'RELATIONSHIPS') {
                return 'EntityRelationships';
            }
            // EntityTodos
            // EntityEvents
            // EntityDocuments
            // EntityPinboard
            // EntityLinks
            // EntityTimekeeper
            return _.pascalCase(this.componentKey) + _.pascalCase(this.selectedPointer);
        },
        topHeaderClass() {
            return this.showAssignees ? 'top-30' : 'top-22';
        },
        imageClass() {
            return {
                'rounded-xl': true,
                'h-full': !this.primaryImage,
                'rounded-tr-none': !this.forceResponsiveDisplay,
                'rounded-b-none': !this.primaryImage || !this.forceResponsiveDisplay,
            };
        },

        // For interactsWithAssigneesPicker mixin
        assigneeGroupsObject() {
            return this.fullItem;
        },

        showOptions() {
            return this.recordOptions?.length;
        },
        pageCreatePathsObj() {
            return getModalHeaders(this.systemName);
        },
        propPageName() {
            return getDefaultPageName(this.systemName);
        },
        recordOptions() {
            if (this.context === 'ENTITY') {
                return _.concat(['DISSOCIATE_RECORD'], this.fullRecordOptions);
            }
            return this.fullRecordOptions;
        },
        createPerformer() {
            const performer = this.fullItem.createAction?.performer;
            if (!performer) {
                return null;
            }
            return this.getPerformerObj(performer);
        },
        updatePerformer() {
            const performer = this.fullItem.latestAction?.performer;
            if (!performer) {
                return null;
            }
            return this.getPerformerObj(performer);
        },
    },
    methods: {
        selectOption(option) {
            if (option === 'DELETE') {
                this.deleteRecord();
            }
            if (option === 'DISSOCIATE_RECORD') {
                this.dissociateRecord();
            }
        },
        openDeleteConfirmation() {
            this.showDeleteConfirmation = true;
        },
        closeDeleteConfirmation() {
            this.showDeleteConfirmation = false;
        },
        async deleteRecord() {
            this.processingDelete = true;
            this.closeDeleteConfirmation();
            try {
                await deleteItem(this.fullItem, this.mapping);
            } finally {
                this.processingDelete = false;
            }
        },
        async dissociateRecord() {
            this.processingDelete = true;
            try {
                await updateMappingPage(this.$apolloForm({
                    id: this.page.id,
                    entityId: null,
                }), this.page);

                this.$saveFeedback({
                    customHeaderPath: 'feedback.records.dissociation.header',
                    customMessagePath: {
                        path: 'feedback.records.dissociation.message',
                        args: {
                            recordName: this.systemName,
                            pageName: this.page.name,
                        },
                    },
                }, 5000);
            } finally {
                this.processingDelete = false;
            }
        },
        async refetchOnFeatureChange(node) {
            if (
                (_.endsWith(node.__typename, 'Item') && node.id === this.fullItem.id)
                || (_.some(node.associations || [], ['id', this.fullItem.id]))
            ) {
                this.isReloading = true;
                await this.$apollo.queries.fullItem.refetch();
                this.isReloading = false;
            }
        },
        selectContentTab(event, useRouter) {
            this.selectTab(event, useRouter);
            this.hideSide();
        },
        duplicateRecord(records) {
            return duplicateItem(this.item, records, this.mapping);
        },
    },
    watch: {
        mapping: {
            handler(val) {
                if (!val) {
                    return;
                }
                this.$apollo.addSmartSubscription('itemUpdated', {
                    query() {
                        const featureIds = simpleMappingRequestFeatures(this.mapping);
                        const fragment = buildItemFragment(this.mapping, null, null, null, featureIds);
                        return gql`subscription { items {
                        ${this.mapping.apiName} {
                            ${this.mapping.apiSingularName}Updated {
                                ${this.mapping.apiSingularName} {
                                    ${fragment}
                                }
                            }
                        }
                    }}`;
                    },
                });

                this.$apollo.addSmartSubscription('itemDeleted', {
                    query() {
                        return gql`subscription { items {
                        ${this.mapping.apiName} {
                            ${this.mapping.apiSingularName}Deleted {
                                ${this.mapping.apiSingularName} {
                                    id
                                }
                            }
                        }
                    }}`;
                    },
                    result(data) {
                        const singularName = this.mapping.apiSingularName;
                        const subscriptionName = `${singularName}Deleted`;
                        const items = data.data.items;
                        const path = `${this.mapping.apiName}.${subscriptionName}.${singularName}.id`;
                        if (_.get(items, path) === this.itemId) {
                            this.$emit('entityDeleted');
                        }
                    },
                });
            },
            immediate: true,
        },
    },
};
</script>

<style scoped>

.o-full-view {
    &__name {
        @apply
            bg-secondary-100
            px-4
            py-2
            rounded-xl
            shadow-md
            text-center
        ;
    }

    &__header {
        @apply
            bg-cm-00
            -mx-2
            px-2
            py-6
            sticky
            top-0
            z-cover
        ;
    }

    &__footer {
        @apply
            bg-cm-00
            bottom-0
            flex
            flex-wrap
            justify-end
            px-4
            py-3
            rounded-b-xl
            sticky
            z-over
        ;
    }
}

</style>
