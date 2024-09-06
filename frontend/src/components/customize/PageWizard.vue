<!-- Refactor to put more of the logic in CreationWizard.vue if the wizard system is used again -->
<template>
    <LoaderFetch
        v-if="showLoader"
        :isFull="true"
    >
    </LoaderFetch>

    <CreationWizard
        v-else
        class="o-page-wizard"
        :seeBack="seeBack"
        :seeNext="seeNext"
        :nextTextPath="nextText"
        :processing="processing"
        @goBack="goBack"
        @goNext="goNext"
    >
        <template
            #processingText
        >
            Creating your new page...
        </template>

        <component
            :is="pageComponent"
            v-model:selectedPageData="selectedPageData"
            class="flex flex-col items-center"
            :pageForm="pageForm"
            :pageCounter="pageCounter"
            :listForm="listForm"
            :space="space"
            :blueprintForm="blueprintForm"
            :page="newPage"
            :availablePages="availablePages"
            :pages="pages"
            :presetPageFactors="presetPageFactors"
            :selectedPath="selectedPath"
            :markerGroups="markerGroups"
            :categories="categories"
            :availableLists="availableLists"
            @setSelectedPath="setSelectedPath"
            @update:pageForm="updatePageForm"
            @update:listForm="updateListForm"
            @update:blueprintForm="updateBlueprintForm"
            @closeFullDialog="$emit('closeFullDialog')"
            @customizePage="$emit('customizePage', $event)"
            @addAnother="addAnother"
            @pressedEnter="pressedEnter"
        >
        </component>
    </CreationWizard>
</template>

<script>

import { gql } from '@apollo/client';
import CreationWizard from './CreationWizard.vue';
import PageWizardPath from './PageWizardPath.vue';
import PageWizardPages from './PageWizardPages.vue';
import PageWizardStructure from './PageWizardStructure.vue';
import PageWizardAdditions from './PageWizardAdditions.vue';
import PageWizardType from './PageWizardType.vue';
import PageWizardName from './PageWizardName.vue';
import PageWizardIcon from './PageWizardIcon.vue';
import PageWizardFolder from './PageWizardFolder.vue';
import PageWizardLists from './PageWizardLists.vue';
import PageWizardBlueprint from './PageWizardBlueprint.vue';
import PageWizardSubset from './PageWizardSubset.vue';
import PageWizardDetails from './PageWizardDetails.vue';
import PageWizardData from './PageWizardData.vue';
import PageWizardDone from './PageWizardDone.vue';

import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';
import interactsWithWizardBasic from '@/vue-mixins/interactsWithWizardBasic.js';
import providesPageGeneralForm from '@/vue-mixins/customizations/providesPageGeneralForm.js';
import providesBlueprintGeneralForm from '@/vue-mixins/customizations/providesBlueprintGeneralForm.js';
import { createFullPage, createPageFromWizard } from '@/core/repositories/pageRepository.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import { availablePages, allPages } from '@/core/mappings/templates/pages.js';
import { pagesList } from '@/core/mappings/templates/bundles.js';
import { reverseRelationshipType } from '@/core/utils.js';
import { getList } from '@/core/mappings/templates/lists.js';
import {
    getCategoriesFromPages,
    getCombinedLists,
    getFullPagesFromPagesArr,
    getMarkerGroupsFromPages,
} from '@/core/mappings/templates/helpers.js';

import BASIC_CATEGORIES from '@/graphql/categories/queries/BasicCategories.gql';
import MARKER_GROUPS from '@/graphql/markers/queries/MarkerGroups.gql';
import PAGES from '@/graphql/pages/queries/AllPages.gql';
import MAPPINGS from '@/graphql/mappings/queries/MappingsFull.gql';
import FEATURE_LIST_FRAGMENT from '@/graphql/FeatureListFragment.gql';

export default {
    name: 'PageWizard',
    components: {
        CreationWizard,
        PageWizardPath,
        PageWizardPages,
        PageWizardStructure,
        PageWizardAdditions,
        PageWizardType,
        PageWizardName,
        PageWizardIcon,
        PageWizardFolder,
        PageWizardLists,
        PageWizardBlueprint,
        PageWizardDetails,
        PageWizardSubset,
        PageWizardData,
        PageWizardDone,
    },
    mixins: [
        interactsWithApolloQueries,
        interactsWithWizardBasic,
        providesPageGeneralForm,
        providesBlueprintGeneralForm,
    ],
    props: {
        space: {
            type: Object,
            required: true,
        },
        potentialPage: {
            type: [Object, null],
            default: null,
        },
        initialStep: {
            type: String,
            default: '',
        },
    },
    emits: [
        'closeFullDialog',
        'customizePage',
    ],
    apollo: {
        pages: {
            query: PAGES,
            variables() {
                return {
                    spaceId: this.space.id,
                };
            },
            update: (data) => initializeConnections(data).pages,
        },
        mappings: {
            query: MAPPINGS,
            variables() {
                return {
                    spaceId: this.space.id,
                };
            },
            update: (data) => initializeConnections(data).mappings,
        },
        categories: {
            query: BASIC_CATEGORIES,
            update: (data) => initializeConnections(data).categories,
            fetchPolicy: 'cache-first',
        },
        markerGroups: {
            query: MARKER_GROUPS,
            update: (data) => initializeConnections(data).markerGroups,
        },
        listsByRefs: {
            query() {
                const listField = {
                    LINKS: 'linkLists',
                    CALENDAR: 'calendars',
                    TODOS: 'todoLists',
                    DOCUMENTS: 'drives',
                    PINBOARD: 'pinboards',
                    NOTES: 'notes',
                }[this.pageForm.type];

                return gql`
                query ListsByRefs($refs: [String!]) {
                    listsByRefs: ${listField}(refs: $refs) {
                        edges {
                            node {
                                ...FeatureList
                            }
                        }
                    }
                }
                ${FEATURE_LIST_FRAGMENT}
                `;
            },
            variables() {
                return {
                    refs: this.selectedRefs,
                };
            },
            skip() {
                return this.shouldSkipListsByRefsQuery;
            },
            update: (data) => initializeConnections(data).listsByRefs,
        },
    },
    data() {
        return {
            currentStep: this.getInitialStep(),
            listForm: this.$apolloForm({
                lists: [],
                newLists: [],
                space: this.space.id,
            }),
            pageCounter: 1,
            newPage: null,
            selectedPath: null,
            selectedPageData: {
                page: null,
                reusedBlueprint: null,
                createdRelationships: [],
                relatedAddition: null,
                mode: null,
            },
        };
    },
    computed: {
        pageComponent() {
            return `PageWizard${this.stepPascal}`;
        },
        isLoadingInitialData() {
            return this.$isLoadingQueriesFirstTime(['pages', 'mappings', 'markerGroups']);
        },
        onPageSelectStep() {
            return this.currentStep === 'PAGES';
        },
        onPageStructureStep() {
            return this.currentStep === 'STRUCTURE';
        },
        showLoader() {
            const showLoaderStep = this.onPageSelectStep || this.onPageStructureStep;
            return this.isLoadingInitialData && showLoaderStep;
        },
        isTypeEntities() {
            return this.pageForm.type === 'ENTITIES';
        },
        isNameCompleted() {
            if (this.currentStep === 'DETAILS') {
                return !!(this.blueprintForm.name && this.blueprintForm.singularName);
            }
            return !!this.pageForm.name;
        },
        isEntitiesEntityType() {
            return ['ENTITIES', 'ENTITY'].includes(this.pageForm.type);
        },
        stream() {
            return this.isEntitiesEntityType ? 'BLUEPRINT' : 'FEATURE';
        },
        isBlueprintStream() {
            return this.stream === 'BLUEPRINT';
        },
        isNewBlueprint() {
            return this.pageForm.mapping === 'NEW';
        },
        pageFilter() {
            return this.pageForm.filter;
        },
        finishWithSubset() {
            const bf = this.pageFilter;
            if (!bf) {
                return false;
            }
            if (bf === 'ALL') {
                return true;
            }
            const by = this.pageFilter.by;
            if (by === 'FIELD') {
                return !!(bf.fieldId && !_.isNull(bf.matchValue));
            }
            return !!(by && bf.matchValue);
        },
        hasListSelected() {
            return !!(this.listForm?.lists.length
                || this.listForm?.newLists.length);
        },
        steps() {
            return [
                {
                    step: 'PATH',
                    hideBack: true,
                    seeNext: this.selectedPath,
                    goNext: this.selectedPath,
                },
                {
                    step: 'PAGES',
                    seeNext: this.selectedPageData.page,
                    backFunction: 'backToPath',
                },
                {
                    step: 'STRUCTURE',
                    seeNext: this.canAdvanceStructure,
                    goNext: 'DONE',
                    nextText: 'finish',
                },
                // For page name and folder
                // {
                //     step: 'ADDITIONS',
                //     seeNext: false, // TODO: add logic
                //     goNext: 'DONE',
                //     nextText: 'finish',
                // },
                {
                    step: 'TYPE',
                    seeNext: this.pageForm.type,
                    goBack: 'PATH',
                    backFunction: 'backToPath',
                },
                {
                    step: 'NAME',
                    seeNext: this.isNameCompleted,
                },
                {
                    step: 'ICON',
                    seeNext: this.pageForm.symbol,
                },
                {
                    step: 'FOLDER',
                    seeNext: true,
                    goNext: this.isBlueprintStream ? 'BLUEPRINT' : 'LISTS',
                },
                {
                    step: 'LISTS',
                    seeNext: this.hasListSelected,
                    nextText: 'finish',
                    goNext: 'DONE',
                },
                {
                    step: 'BLUEPRINT',
                    seeNext: this.pageForm.mapping,
                    goNext: this.afterPickingBlueprint,
                    nextText: this.blueprintNext,
                    goBack: 'FOLDER',
                },
                {
                    step: 'SUBSET',
                    seeNext: this.finishWithSubset,
                    nextText: 'finish',
                    goNext: 'DONE',
                    goBack: 'BLUEPRINT',
                },
                {
                    step: 'DETAILS',
                    seeNext: this.isNameCompleted,
                    goBack: 'BLUEPRINT',
                },
                {
                    step: 'DATA',
                    seeNext: !!this.blueprintForm.type,
                    nextText: 'finish',
                    goNext: 'DONE',
                    goBack: 'DETAILS',
                },
                {
                    step: 'DONE',
                    seeNext: true,
                    nextText: 'done',
                    hideBack: true,
                    emitEvent: 'closeFullDialog',
                },
            ];
        },
        afterPickingBlueprint() {
            if (this.isNewBlueprint) {
                return 'DETAILS';
            }
            if (this.isTypeEntities) {
                return 'SUBSET';
            }
            return 'DONE';
        },
        blueprintNext() {
            if (this.isNewBlueprint || this.isTypeEntities) {
                return 'next';
            }
            return 'finish';
        },

        // *Avoiding data repetition and integrating the new page with existing data*

        // We do not want users to end up with a lot of similar pages and data.
        // Also we want to be able to help them fit in their new page with existing data, and
        // make it easier to potentially add related pages (and since we know they are related,
        // we can create relationships or other connections as appropriate).

        // The following computed properties deal with cases where we want to avoid duplication
        // Duplicates we want to avoid:
        // 1. Blueprints
        // 2. Categories
        // 3. Markers
        // 4. Feature lists

        // Also handled:
        // 5. Relationships
        // 6. Make it easier to add related pages

        presetPageFactors() {
            return {
                alreadyUsedBlueprints: this.alreadyUsedBlueprints, // Same template ref (existent)
                similarBlueprints: this.similarBlueprintsToPreset, // Same mergeIds (existent)
                relatedPages: this.relatedFromBundle, // Shares a bundle with
                existentFromBundle: this.existentFromBundle, // Existent pages from the bundle
                possibleRelationships: this.possibleRelationships, // All possible relationships from related pages
                nothingToDo: this.nothingToDo,
                relationshipsLength: this.relationshipsLength,
                relatedPagesLength: this.relatedPagesLength,
                filteredRelatedPages: this.filteredRelatedPages,
                blueprintSuggestions: this.blueprintSuggestions,
                matchingCategories: this.matchingCategories,
                matchingMarkers: this.matchingMarkers,
                matchingLists: this.matchingLists,
            };
        },
        matchingCategories() {
            const categories = this.selectedPage ? getCategoriesFromPages([this.selectedPage]) : [];
            const categoryIds = _.flatMap(categories, 'templateRefs');
            const existingIds = _.flatMap(this.categories, 'templateRefs');
            return this.selectedPage ? _.intersection(existingIds, categoryIds) : [];
        },
        matchingMarkers() {
            const markerGroups = getMarkerGroupsFromPages([this.selectedPage]);
            const markerIds = _.flatMap(markerGroups, 'templateRefs');
            const existingIds = _.flatMap(this.markerGroups, 'templateRefs');
            return this.selectedPage ? _.intersection(existingIds, markerIds) : [];
        },
        matchingLists() {
            if (this.availableLists.length) {
                return _(this.availableLists).flatMap('templateRefs').uniq().value();
            }
            return [];
        },
        shouldSkipListsByRefsQuery() {
            // To clear the Apollo query if the new page selected is not a feature page,
            // and thus does not have lists
            return !this.selectedRefs || !this.pageForm.type || this.isEntitiesEntityType;
        },
        availableLists() {
            if (this.shouldSkipListsByRefsQuery) {
                return [];
            }
            return this.listsByRefs || [];
        },
        existingTemplateRefs() {
            // This gets the template refs of all of the blueprints used

            // This deals with (1)
            return _(this.mappings).flatMap('templateRefs').compact().value();
        },
        existingPagesStructure() {
            // This uses the template refs of existing blueprints to get
            // the create-a-page structure of any page matching
            // that template ref

            // This deals with (1)
            return _(this.existingTemplateRefs).flatMap((ref) => {
                return _(allPages).filter((page) => {
                    return page.templateRefs?.includes(ref);
                }).value();
            }).uniqBy('id').value();
        },
        existingMergeIds() {
            // This gets any merge ids to also get cases where the templateRefs might
            // be different, but the two pages can be merged
            return _(this.existingPagesStructure).flatMap('mergeIds').compact().uniq()
                .value();
        },
        selectedRefs() {
            return this.selectedPage?.templateRefs;
        },
        alreadyUsedBlueprints() {
            if (!this.selectedPage) {
                return [];
            }
            return this.mappings.filter((mapping) => {
                return _.intersection(mapping.templateRefs, this.selectedRefs).length;
            });
        },
        similarBlueprintsToPreset() {
            if (!this.selectedPage) {
                return [];
            }
            return this.mappings.filter((mapping) => {
                const structure = _.find(this.existingPagesStructure, { templateRefs: mapping.templateRefs });
                return structure && _.intersection(structure.mergeIds, this.selectedPage.mergeIds).length;
            });
        },
        bundlesForSelected() {
            if (!this.selectedPage) {
                return [];
            }
            return _(pagesList).map((pages, bundleKey) => {
                const hasMatches = pages?.some((page) => {
                    return this.selectedPage.id === page?.id;
                });
                if (!hasMatches) {
                    return null;
                }
                return bundleKey;
            }).compact().value();
        },
        selectedPage() {
            return this.selectedPageData.page;
        },
        selectedPageId() {
            return this.selectedPage?.id;
        },
        selectedIsFeaturePage() {
            const pageType = this.selectedPage?.pageType;
            if (!pageType) {
                return false;
            }
            return !['ENTITIES', 'ENTITY'].includes(pageType);
        },
        relatedFromBundle() {
            if (!this.selectedPage) {
                return [];
            }
            // For now exclude feature pages from any matching
            if (this.selectedIsFeaturePage) {
                return [];
            }
            return _(this.bundlesForSelected).flatMap((bundle) => {
                return this.getRelatedFromBundle(bundle);
            }).uniqBy('id')
                .filter({ includeInPages: true })
                .value();
        },
        relatedIds() {
            return _(this.relatedFromBundle).map('id').compact().value();
        },
        existentFromBundle() {
            // Which pages from the bundle already exist
            if (!this.selectedPageData.page) {
                return [];
            }
            return this.pages.filter((page) => {
                return _.intersection(this.relatedIds, page.templateRefs)?.length;
            });
        },
        bundleRelationships() {
            return this.relatedFromBundle.flatMap((page) => {
                if (!page?.relationships) {
                    return [];
                }
                // combine the relationship with the page because we might
                // need to build the inverse relationship.
                return page.relationships.map((relationship) => ({
                    page,
                    relationship,
                }));
            });
        },
        possibleRelationships() {
            if (!this.selectedPageData.page) {
                return [];
            }

            // First get the references of the selected page
            const templateRefs = this.selectedPageData.page.templateRefs;

            // Flat map to filter and map at the same time
            return _(this.bundleRelationships).flatMap(({ page, relationship }) => {
                // Loop through the relationships and if none of them is to or
                // from the selected page, then filter it out.
                const relationshipIsToSelectedPage = templateRefs.includes(relationship.to);
                const relationshipIsFromSelectedPage = !!_.intersection(
                    templateRefs,
                    page.templateRefs.concat([relationship.to])
                ).length;

                // Filter out relationships that are not from the perspective of the selected page
                if (!relationshipIsToSelectedPage && !relationshipIsFromSelectedPage) {
                    return [];
                }

                // If the relationship is to the selected page then we want to
                // invert it.
                const normalizedRelationship = relationshipIsToSelectedPage
                    ? {
                        name: relationship.inverseName,
                        inverseName: relationship.name,
                        type: reverseRelationshipType(relationship.type),
                        to: page.id,
                    }
                    : _.cloneDeep(relationship);

                // Finally we want to see if the user currently has the equivalent
                // mapping from the bundle so we can ask if they want to create
                // a relationship to it.

                const toMapping = _.find(this.mappings, (mapping) => {
                    const id = normalizedRelationship.to;
                    return mapping.templateRefs?.includes(id);
                });

                if (!toMapping) {
                    return [];
                }

                normalizedRelationship.to = toMapping;

                return normalizedRelationship;
            }).value();
        },
        existentTemplateRefs() {
            return _.flatMap(this.existentFromBundle, 'templateRefs');
        },
        filteredRelatedPages() {
            // Remove subsets, pages that already exist, and itself
            return this.relatedFromBundle.filter((page) => {
                return !this.existentTemplateRefs.includes(page.id)
                    && !page.subset
                    && page.id !== this.selectedPageData.page.id;
            });
        },
        hasSimilar() {
            return this.blueprintSuggestions?.length;
        },
        blueprintSuggestions() {
            const allBlueprints = this.alreadyUsedBlueprints.concat(this.similarBlueprintsToPreset);
            return _.uniqBy(allBlueprints, 'id');
        },
        nothingToDo() {
            // hasSimilar is used as a proxy for matching markers, and matching categories
            return !this.relatedPagesLength
                && !this.relationshipsLength
                && !this.hasSimilar
                && !this.matchingLists.length;
        },
        relatedPagesLength() {
            return this.filteredRelatedPages?.length;
        },
        relationshipsLength() {
            return this.possibleRelationships?.length;
        },
        availablePages() {
            return availablePages();
        },
        canAdvanceStructure() {
            if (this.nothingToDo || !this.hasSimilar) {
                return true;
            }
            if (this.mode === 'REUSE') {
                return this.reusedBlueprint;
            }
            if (this.mode === 'NEW') {
                return true;
            }
            return false;
        },
        mode() {
            return this.selectedPageData.mode;
        },
        reusedBlueprint() {
            return this.selectedPageData.reusedBlueprint;
        },
        longWayCreation() {
            return this.selectedPageData.page
                && (this.mode === 'NEW'
                    || (!this.hasSimilar && !this.selectedIsFeaturePage));
        },
        getSelectedPageData() {
            if (!this.longWayCreation) {
                return null;
            }
            const selected = _.cloneDeep(this.selectedPage);
            const additional = _.cloneDeep(this.selectedPageData.relatedAddition);

            const pages = [selected];
            if (additional) {
                pages.push(additional);
            }

            const selectedData = this.selectedPageData;
            const reusedMarkerGroups = selectedData.reusedMarkerGroups || {};
            const reusedCategories = _.mapKeys(selectedData.reusedCategories || {}, (__, key) => `${key}_TEMP`);
            const reusedBlueprints = {};
            if (selectedData.createdRelationships?.length) {
                selectedData.createdRelationships.forEach((createdRelation) => {
                    const reusedRelation = selectedData.page.relationships.find((relation) => {
                        return createdRelation.to.templateRefs.includes(relation.to);
                    });
                    if (reusedRelation) {
                        reusedBlueprints[reusedRelation.to] = createdRelation.to.id;
                    }
                });
            }

            // Remove any markers and categories that are being used so we don't create new ones
            const markerGroups = getMarkerGroupsFromPages(pages).filter((group) => {
                return !_.has(reusedMarkerGroups, group.id);
            });
            const categories = getCategoriesFromPages(pages).filter((category) => {
                return !_.has(reusedCategories, category.id);
            });

            return {
                reusedMarkerGroups,
                reusedCategories,
                markerGroups,
                categories,
                space: {
                    id: this.space.id,
                    reusedBlueprints,
                    lists: getCombinedLists(pages),
                    pages: getFullPagesFromPagesArr(pages).map((page) => {
                        return {
                            ...page,
                            // We only want to create relationships that the user
                            // has manually selected in the createdRelationships
                            // field.
                            relationships: page.relationships?.filter((relation) => {
                                return _.has(reusedBlueprints, relation.to);
                            }),
                        };
                    }),
                },
            };
        },
    },
    methods: {
        async goNext(nextStep) {
            // When an automatic step forward, delay a bit so the user has a moment to see their selection
            const time = nextStep ? 400 : 0;
            setTimeout(async () => {
                if (this.nextAction === 'DONE') {
                    this.processing = true;
                    try {
                        if (this.longWayCreation) {
                            const pages = await createPageFromWizard(this.getSelectedPageData);
                            this.newPage = pages[0];
                            this.$apollo.queries.markerGroups.refresh();
                            this.$apollo.queries.categories.refresh();
                        } else {
                            const response = await createFullPage(
                                this.pageForm,
                                this.listForm,
                                this.blueprintForm,
                                this.space
                            );
                            this.newPage = response.page;
                            this.$apollo.queries.listsByRefs.refresh();
                        }
                        this.currentStep = this.nextAction;
                    } finally {
                        this.processing = false;
                    }
                } else {
                    const emit = this.currentStepObj.emitEvent;
                    if (emit) {
                        this.$emit(emit);
                    } else {
                        this.currentStep = this.nextAction;
                    }
                }
            }, time);
        },
        updateForm(formKey, valKey, newVal, nextStep) {
            this[formKey][valKey] = newVal;
            if (nextStep === 'NEXT' && (nextStep !== 'SAME')) {
                this.goNext(nextStep);
            }
        },
        updatePageForm({ valKey, newVal, nextStep }) {
            this.updateForm('pageForm', valKey, newVal, nextStep);
        },
        updateListForm({ valKey, newVal, nextStep }) {
            this.updateForm('listForm', valKey, newVal, nextStep);
        },
        updateBlueprintForm({ valKey, newVal, nextStep }) {
            this.updateForm('blueprintForm', valKey, newVal, nextStep);
        },
        addAnother() {
            this.currentStep = 'PATH';
            this.pageCounter += 1;
            this.clearForms();
            this.clearSelectedData();
            this.selectedPath = null;
        },
        setSelectedPath(path) {
            this.selectedPath = path;
            this.goNext();
        },
        backToPath() {
            this.selectedPath = null;
        },
        clearForms() {
            this.listForm.reset();
            this.blueprintForm.reset();
            this.pageForm.reset();
        },
        clearSelectedData() {
            this.selectedPageData.mode = null;
            this.selectedPageData.reusedBlueprint = null;
            this.selectedPageData.createdRelationships = [];
            this.selectedPageData.relatedAddition = null;
        },
        getRelatedFromBundle(bundle) {
            return pagesList[bundle];
        },
        getInitialStep() {
            return this.initialStep || 'PATH';
        },
        populateListForm(selectedPageData) {
            if (this.selectedIsFeaturePage) {
                const reusedLists = selectedPageData.reusedLists;
                const reusedRefs = reusedLists ? _.keys(reusedLists) : [];
                this.listForm.lists = reusedLists ? _.values(reusedLists).map((id) => ({ id })) : [];

                // Flat map to filter and transform in one function
                this.listForm.newLists = this.selectedPage.lists.flatMap((list) => {
                    const obj = getList(this.selectedPage.pageType, list);
                    if (_.intersection(obj.templateRefs, reusedRefs).length) {
                        return [];
                    }
                    return [{
                        name: obj.name,
                    }];
                });
            }
        },
    },
    watch: {
        selectedPath() {
            this.clearForms();
            this.clearSelectedData();
            this.selectedPageData.page = null;
        },
        selectedPageId(newId) {
            // When the page changes, reset the settings
            this.clearSelectedData();

            if (!newId) {
                this.clearForms();
            } else {
                const newVal = this.selectedPage;

                // Set data on the pageForm
                this.pageForm.name = newVal.pageName || newVal.name;
                this.pageForm.type = newVal.pageType;
                this.pageForm.symbol = newVal.symbol;
                this.pageForm.newFields = newVal.newFields;
                this.pageForm.templateRefs = newVal.templateRefs;
            }
        },
        selectedPageData(newVal) {
            // Deal with lists
            this.populateListForm(newVal);
        },
        reusedBlueprint(newVal) {
            if (this.mode === 'REUSE') {
                this.pageForm.mapping = newVal?.id;
            } else {
                this.pageForm.mapping = null;
            }
        },
    },
    created() {
        if (this.potentialPage) {
            this.selectedPageData.page = this.potentialPage;
            this.populateListForm(this.selectedPageData);
        }
    },
};
</script>

<style scoped>

/*.o-page-wizard {

} */

</style>
