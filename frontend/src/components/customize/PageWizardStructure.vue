<template>
    <div class="o-page-wizard-structure flex justify-center">
        <div class="text-center mb-12">
            <p
                v-t="'customizations.pageWizard.review.selection'"
                class="mb-4 font-bold text-xl text-primary-700"
            >
            </p>
            <div class="max-w-300p">
                <PresetPage
                    :page="selectedPageData.page"
                    outerEl="div"
                >
                </PresetPage>
            </div>
        </div>
        <div class="flex flex-col items-center">
            <div class="o-page-wizard-structure__container">
                <h2
                    v-if="nothingToDo"
                    v-t="'customizations.pageWizard.review.allSet'"
                    class="o-creation-wizard__prompt"
                >
                </h2>
                <template
                    v-if="hasSimilar"
                >
                    <h2
                        v-t="'customizations.pageWizard.review.dataReuse.header'"
                        class="o-creation-wizard__prompt"
                    >
                    </h2>

                    <div class="bg-cm-100 rounded-xl p-4 mt-10">
                        <p
                            v-t="'customizations.pageWizard.review.dataReuse.prompt'"
                            class="text-center font-semibold mb-4 text-lg"
                        >
                        </p>

                        <div class="flex justify-center gap-2">
                            <button
                                v-t="'customizations.pageWizard.review.dataReuse.optionNew'"
                                class="button"
                                :class="mode === 'NEW' ? 'button-secondary' : 'button-secondary--light'"
                                type="button"
                                @click="createNewBlueprint"
                            >
                            </button>

                            <button
                                v-t="'customizations.pageWizard.review.dataReuse.optionReuse'"
                                class="button"
                                :class="mode === 'REUSE' ? 'button-secondary' : 'button-secondary--light'"
                                type="button"
                                @click="reuseBlueprint"
                            >
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="showArrow"
                        class="centered my-4"
                    >
                        <i
                            class="fas fa-arrow-down text-2xl text-secondary-600"
                        >
                        </i>
                    </div>

                    <div
                        v-if="mode === 'REUSE'"
                        class="bg-cm-100 rounded-xl p-4"
                    >
                        <p
                            v-t="'customizations.pageWizard.review.dataReuse.dataSelection'"
                            class="text-center font-semibold mb-4 text-lg"
                        >
                        </p>

                        <div>
                            <ButtonEl
                                v-for="blueprint in blueprintSuggestions"
                                :key="blueprint.id"
                                class="o-page-wizard-structure__option"
                                :class="selectedBlueprintClass(blueprint)"
                                @click="selectBlueprint(blueprint)"
                            >
                                <p class="text-center font-bold text-secondary-600 text-lg">
                                    {{ blueprint.name }}
                                </p>

                                <div class="flex flex-wrap items-baseline">
                                    <p
                                        class="mr-2 font-semibold text-cm-700"
                                    >
                                        {{ $t('customizations.pageWizard.review.usedInPages') }}:
                                    </p>
                                    <div class="flex flex-wrap gap-4">
                                        <div
                                            v-for="page in blueprint.pages"
                                            :key="page.id"
                                            class="text-cm-500"
                                        >
                                            <i
                                                class="far mr-1"
                                                :class="page.symbol"
                                            >
                                            </i>
                                            {{ page.name }}
                                        </div>
                                    </div>
                                </div>
                            </ButtonEl>
                        </div>
                    </div>
                </template>

                <template
                    v-if="showNewCustomizations"
                >
                    <h2
                        v-if="!hasSimilar && relatedPagesLength"
                        v-t="'customizations.pageWizard.review.addMorePrompt'"
                        class="o-creation-wizard__prompt"
                    >
                    </h2>

                    <div
                        v-if="relatedPagesLength"
                        class="bg-cm-100 rounded-xl p-4"
                    >
                        <p
                            class="text-center font-semibold mb-4 text-lg"
                        >
                            {{ $t('customizations.pageWizard.review.createRelatedPages', { selectedName }) }}
                        </p>

                        <div class="flex flex-col gap-4">
                            <CheckHolder
                                v-for="page in filteredRelatedPages"
                                :key="page.id"
                                class="text-cm-600"
                                :val="page"
                                :modelValue="selectedPageData.relatedAddition"
                                type="radio"
                                :canRadioClear="true"
                                predicate="id"
                                @update:modelValue="addAdditional"
                            >
                                <i
                                    class="far mr-1"
                                    :class="page.symbol"
                                >
                                </i>
                                {{ getSelectedPageName(page) }}
                            </CheckHolder>
                        </div>
                    </div>

                    <div
                        v-if="listsLength"
                        class="bg-cm-100 rounded-xl p-4 mt-4"
                    >
                        <p
                            v-t="'customizations.pageWizard.review.dataReuse.reuseList'"
                            class="text-center font-semibold mb-4 text-lg"
                        >
                        </p>

                        <div>
                            <div
                                v-for="list in matchingListOptions"
                                :key="list.ref"
                                class="mb-8 last:mb-0"
                            >
                                <p
                                    class="mb-3 font-semibold text-secondary-700"
                                >
                                    {{ list.name }}
                                </p>

                                <ButtonEl
                                    class="bg-cm-00 rounded-md w-full py-1.5 px-4 mb-2 hover:bg-cm-200 transition-2eio"
                                    :class="{ 'button-secondary': !isReusingList(list.ref) }"
                                    @click="createNewList(list.ref)"
                                >
                                    {{ $t('customizations.filters.labels.createNew') }}
                                </ButtonEl>

                                <ButtonEl
                                    v-for="option in list.listOptions"
                                    :key="option.id"
                                    class="bg-cm-00 rounded-md w-full py-1.5 px-4 hover:bg-cm-200 transition-2eio"
                                    :class="{ 'button-secondary': isReusingList(list.ref, option) }"
                                    @click="reuseList(list.ref, option)"
                                >
                                    {{ getOptionName(option.name) }}
                                </ButtonEl>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="markerGroupsLength"
                        class="bg-cm-100 rounded-xl p-4 mt-4"
                    >
                        <p
                            v-t="'customizations.pageWizard.review.dataReuse.reuseMarkerGroup'"
                            class="text-center font-semibold mb-4 text-lg"
                        >
                        </p>

                        <div>
                            <div
                                v-for="marker in matchingMarkerOptions"
                                :key="marker.ref"
                                class="mb-8 last:mb-0"
                            >
                                <p
                                    class="mb-3 font-semibold text-secondary-700"
                                >
                                    {{ marker.label }}
                                </p>

                                <ButtonEl
                                    class="bg-cm-00 rounded-md w-full py-1.5 px-4 mb-2 hover:bg-cm-200 transition-2eio"
                                    :class="{ 'button-secondary': !isReusingMarkerGroup(marker.ref) }"
                                    @click="createNewMarkerGroup(marker.ref)"
                                >
                                    {{ $t('customizations.filters.labels.createNew') }}
                                </ButtonEl>

                                <ButtonEl
                                    v-for="option in marker.markerOptions"
                                    :key="option.id"
                                    class="bg-cm-00 rounded-md w-full py-1.5 px-4 hover:bg-cm-200 transition-2eio"
                                    :class="{ 'button-secondary': isReusingMarkerGroup(marker.ref, option) }"
                                    @click="reuseMarkerGroup(marker.ref, option)"
                                >
                                    {{ getOptionName(option.name) }}
                                </ButtonEl>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="categoriesLength"
                        class="bg-cm-100 rounded-xl p-4 mt-4"
                    >
                        <p
                            v-t="'customizations.pageWizard.review.dataReuse.reuseCategories'"
                            class="text-center font-semibold mb-4 text-lg"
                        >
                        </p>

                        <div>
                            <div
                                v-for="category in matchingCategoryOptions"
                                :key="category.ref"
                                class="mb-8 last:mb-0"
                            >
                                <p
                                    class="mb-3 font-semibold text-secondary-700"
                                >
                                    {{ category.label }}
                                </p>

                                <ButtonEl
                                    class="bg-cm-00 rounded-md w-full py-1.5 px-4 mb-2 hover:bg-cm-200 transition-2eio"
                                    :class="{ 'button-secondary': !isReusingCategory(category.ref) }"
                                    @click="createNewCategory(category.ref)"
                                >
                                    {{ $t('customizations.filters.labels.createNew') }}
                                </ButtonEl>

                                <ButtonEl
                                    v-for="option in category.categoryOptions"
                                    :key="option.id"
                                    class="bg-cm-00 rounded-md w-full py-1.5 px-4 hover:bg-cm-200 transition-2eio"
                                    :class="{ 'button-secondary': isReusingCategory(category.ref, option) }"
                                    @click="reuseCategory(category.ref, option)"
                                >
                                    {{ getOptionName(option.name) }}
                                </ButtonEl>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="relationshipsLength"
                        class="bg-cm-100 rounded-xl p-4 mt-4"
                    >
                        <p
                            class="text-center font-semibold mb-4 text-lg"
                        >
                            {{ $t('customizations.pageWizard.review.linkData', { selectedName }) }}
                        </p>

                        <div>
                            <ButtonEl
                                v-for="relationship in possibleRelationships"
                                :key="relationship.id"
                                class="o-page-wizard-structure__option"
                                :class="selectedRelationshipClass(relationship)"
                                @click="selectRelationship(relationship)"
                            >
                                <p class="text-center font-bold text-secondary-600 mb-4 text-lg">
                                    {{ relationship.to.name }}
                                </p>

                                <div class="flex flex-wrap items-baseline">
                                    <p
                                        class="mr-2 font-semibold text-cm-700"
                                    >
                                        {{ $t('customizations.pageWizard.review.usedInPages') }}:
                                    </p>
                                    <div class="flex flex-wrap gap-4">
                                        <div
                                            v-for="page in relationship.to.pages"
                                            :key="page.id"
                                            class="text-cm-500"
                                        >
                                            <i
                                                class="far mr-1"
                                                :class="page.symbol"
                                            >
                                            </i>
                                            {{ getExistingPageName(page) }}
                                        </div>
                                    </div>
                                </div>
                            </ButtonEl>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>

import PresetPage from '@/components/customize/PresetPage.vue';
import { arrRemoveId } from '@/core/utils.js';

export default {
    name: 'PageWizardStructure',
    components: {
        PresetPage,
    },
    mixins: [
    ],
    props: {
        selectedPageData: {
            type: [null, Object],
            required: true,
        },
        presetPageFactors: {
            type: Object,
            required: true,
        },
        pages: {
            type: Array,
            required: true,
        },
        markerGroups: {
            type: Array,
            required: true,
        },
        categories: {
            type: Array,
            required: true,
        },
        availableLists: {
            type: Array,
            required: true,
        },
    },
    emits: [
        'update:selectedPageData',
    ],
    data() {
        return {
            showReuse: false,
        };
    },
    computed: {
        // General
        nothingToDo() {
            return this.presetPageFactors.nothingToDo;
        },
        showNewCustomizations() {
            return !this.nothingToDo && (!this.hasSimilar || this.mode === 'NEW');
        },
        showArrow() {
            if (!this.mode) {
                return false;
            }
            if (this.mode === 'NEW') {
                return this.relatedPagesLength || this.relationshipsLength;
            }
            return true;
        },
        mode() {
            return this.selectedPageData.mode;
        },
        clearAllArr() {
            return [
                {
                    data: null,
                    dataKey: 'reusedBlueprint',
                },
                {
                    data: null,
                    dataKey: 'createdRelationship',
                },
                {
                    data: null,
                    dataKey: 'relatedAddition',
                },
            ];
        },

        // Pages
        relatedPages() {
            return this.presetPageFactors.relatedPages;
        },
        relatedPagesLength() {
            return this.presetPageFactors.relatedPagesLength;
        },
        filteredRelatedPages() {
            return this.presetPageFactors.filteredRelatedPages;
        },
        existentFromBundle() {
            // Existing related pages
            return this.presetPageFactors.existentFromBundle;
        },
        selectedName() {
            return this.getSelectedPageName(this.selectedPageData.page);
        },

        // Blueprints
        blueprintSuggestions() {
            return this.presetPageFactors.blueprintSuggestions;
        },
        hasSimilar() {
            return this.blueprintSuggestions?.length;
        },
        similarBlueprints() {
            // Same mergeIds
            return this.presetPageFactors.similarBlueprints;
        },
        alreadyUsed() {
            // Same templateRef
            return this.presetPageFactors.alreadyUsedBlueprints;
        },

        // Lists
        matchingLists() {
            return this.presetPageFactors.matchingLists;
        },
        listsLength() {
            return this.matchingLists.length;
        },
        matchingListOptions() {
            return this.matchingLists.map((ref) => {
                return {
                    ref,
                    listOptions: this.getReusableLists(ref),
                };
            });
        },

        // Markers
        matchingMarkers() {
            return this.presetPageFactors.matchingMarkers;
        },
        matchingMarkerOptions() {
            return this.matchingMarkers.map((ref) => {
                return {
                    ref,
                    label: this.$t(this.getLabel(ref)),
                    markerOptions: this.getReusableMarkers(ref),
                };
            });
        },
        markerGroupsLength() {
            return this.matchingMarkers.length;
        },

        // Categories
        matchingCategories() {
            return this.presetPageFactors.matchingCategories;
        },
        categoriesLength() {
            return this.matchingCategories.length;
        },
        matchingCategoryOptions() {
            return this.matchingCategories.map((ref) => {
                return {
                    ref,
                    label: this.$t(this.getLabel(ref)),
                    categoryOptions: this.getReusableCategories(ref),
                };
            });
        },

        // Relationships
        relationshipsLength() {
            return this.presetPageFactors.relationshipsLength;
        },
        possibleRelationships() {
            return this.presetPageFactors.possibleRelationships;
        },
    },
    methods: {
        // General
        getLabel(val) {
            return `labels.${_.camelCase(val)}`;
        },
        clearAll() {
            this.emitSelected(this.clearAllArr);
        },
        emitSelected(newData) {
            // newData is arr of objects with data and dataKey
            const newObj = _.cloneDeep(this.selectedPageData);
            newData.forEach((data) => {
                _.set(newObj, data.dataKey, data.data);
            });
            this.$emit('update:selectedPageData', newObj);
        },

        // Pages
        getExistingPageName(page) {
            const pageObj = _.find(this.pages, { id: page.id });
            return pageObj.name;
        },
        getSelectedPageName(page) {
            return page.pageName || page.name;
        },
        addAdditional(page) {
            if (page.id === this.selectedPageData.relatedAddition?.id) {
                this.emitSelected([
                    {
                        data: null,
                        dataKey: 'relatedAddition',
                    },
                ]);
            } else {
                this.emitSelected([
                    {
                        data: page,
                        dataKey: 'relatedAddition',
                    },
                ]);
            }
        },
        getOptionName(optionName) {
            return this.$t('customizations.pageWizard.review.dataReuse.reuseItem', { itemName: optionName });
        },

        // Blueprints
        createNewBlueprint() {
            if (this.mode === 'NEW') {
                this.emitSelected([
                    ...this.clearAllArr,
                    {
                        data: null,
                        dataKey: 'mode',
                    },
                ]);
            } else {
                this.emitSelected([
                    ...this.clearAllArr,
                    {
                        data: 'NEW',
                        dataKey: 'mode',
                    },
                ]);
            }
        },
        reuseBlueprint() {
            if (this.mode === 'REUSE') {
                this.emitSelected([
                    ...this.clearAllArr,
                    {
                        data: null,
                        dataKey: 'mode',
                    },
                ]);
            } else {
                this.emitSelected([
                    ...this.clearAllArr,
                    {
                        data: 'REUSE',
                        dataKey: 'mode',
                    },
                ]);
            }
            this.showReuse = !this.showReuse;
        },
        selectBlueprint(blueprint) {
            if (this.isSelectedBlueprint(blueprint)) {
                this.emitSelected([
                    {
                        data: null,
                        dataKey: 'reusedBlueprint',
                    },
                ]);
            } else {
                this.emitSelected([
                    {
                        data: blueprint,
                        dataKey: 'reusedBlueprint',
                    },
                ]);
            }
        },
        isSelectedBlueprint(blueprint) {
            return this.selectedPageData.reusedBlueprint?.id === blueprint.id;
        },
        selectedBlueprintClass(blueprint) {
            return this.isSelectedBlueprint(blueprint) ? 'border-secondary-600' : 'border-transparent';
        },

        // Lists
        isReusingList(listRef, existingList = null) {
            if (this.selectedPageData) {
                const pathToRef = this.selectedPageData.reusedLists?.[listRef];
                if (existingList) {
                    return pathToRef === existingList.id;
                }
                return pathToRef;
            }
            return false;
        },
        reuseList(listRef, existingList) {
            const reusedLists = this.selectedPageData.reusedLists || {};
            this.emitSelected([{
                data: {
                    ...reusedLists,
                    [listRef]: existingList.id,
                },
                dataKey: 'reusedLists',
            }]);
        },
        getReusableLists(listRef) {
            return this.availableLists.filter((list) => {
                return list.templateRefs?.includes(listRef);
            });
        },
        createNewList(listRef) {
            const reusedLists = this.selectedPageData.reusedLists || {};
            this.emitSelected([{
                data: _.omit(reusedLists, listRef),
                dataKey: 'reusedLists',
            }]);
        },

        // Markers
        isReusingMarkerGroup(markerGroupRef, existingGroup = null) {
            if (this.selectedPageData) {
                const pathToRef = this.selectedPageData.reusedMarkerGroups?.[markerGroupRef];
                if (existingGroup) {
                    return pathToRef === existingGroup.id;
                }
                return pathToRef;
            }
            return false;
        },
        reuseMarkerGroup(markerGroupRef, existingGroup) {
            const reusedGroups = this.selectedPageData.reusedMarkerGroups || {};
            this.emitSelected([{
                data: {
                    ...reusedGroups,
                    [markerGroupRef]: existingGroup.id,
                },
                dataKey: 'reusedMarkerGroups',
            }]);
        },
        createNewMarkerGroup(markerGroup) {
            const reusedGroups = this.selectedPageData.reusedMarkerGroups || {};
            this.emitSelected([{
                data: _.omit(reusedGroups, markerGroup),
                dataKey: 'reusedMarkerGroups',
            }]);
        },
        getReusableMarkers(markerRef) {
            return this.markerGroups.filter((group) => {
                return group.templateRefs?.includes(markerRef);
            });
        },

        // Categories
        isReusingCategory(category, existingCategory = null) {
            if (this.selectedPageData) {
                const pathToCat = this.selectedPageData.reusedCategories?.[category];
                if (existingCategory) {
                    return pathToCat === existingCategory.id;
                }
                return pathToCat;
            }
            return false;
        },
        reuseCategory(category, existingCategory) {
            const reusedCategories = this.selectedPageData.reusedCategories || {};
            this.emitSelected([{
                data: {
                    ...reusedCategories,
                    [category]: existingCategory.id,
                },
                dataKey: 'reusedCategories',
            }]);
        },
        createNewCategory(category) {
            const reusedCategories = this.selectedPageData.reusedCategories || {};
            this.emitSelected([{
                data: _.omit(reusedCategories, category),
                dataKey: 'reusedCategories',
            }]);
        },
        getReusableCategories(categoryRef) {
            return this.categories.filter((cat) => {
                return cat.templateRefs?.includes(categoryRef);
            });
        },

        // Relationships
        selectedRelationshipClass(relationship) {
            return this.isSelectedRelationship(relationship) ? 'border-secondary-600' : 'border-transparent';
        },
        isSelectedRelationship(relationship) {
            return _.some(this.selectedPageData.createdRelationships, ((rel) => {
                return rel.to.id === relationship.to.id;
            }));
        },
        selectRelationship(relationship) {
            if (this.isSelectedRelationship(relationship)) {
                this.emitSelected([
                    {
                        data: arrRemoveId(
                            this.selectedPageData.createdRelationships || [],
                            relationship.to.id,
                            'to.id'
                        ),
                        dataKey: 'createdRelationships',
                    },
                ]);
            } else {
                this.emitSelected([
                    {
                        data: [
                            ...(this.selectedPageData.createdRelationships || []),
                            relationship,
                        ],
                        dataKey: 'createdRelationships',
                    },
                ]);
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-page-wizard-structure {
    &__container {
        max-width: 600px;
    }

    &__option {
        transition: 0.2s ease-in-out;
        @apply
            bg-cm-00
            border
            border-solid
            mb-2
            px-4
            py-2
            rounded-lg
        ;

        &:hover {
            @apply
                shadow-md
            ;
        }
    }
}

</style>
