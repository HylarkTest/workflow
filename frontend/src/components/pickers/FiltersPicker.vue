<template>
    <div class="c-filters-picker">
        <DropdownBox
            class="w-[200px]"
            placeholder="Saved filters"
            :boxStyle="boxStyle"
            :groups="groups"
            groupDisplayRule="label"
            showEmptyGroups
            displayRule="name"
            :showClear="true"
            :optionsPopupProps="{ containerClass: 'py-2', maxHeightProp: '12rem' }"
            v-bind="$attrs"
        >
            <template
                #group="{ group }"
            >
                <p class="px-2 c-filters-picker__category">
                    {{ group.label }}
                </p>
            </template>
            <template
                #emptyGroup="{ group }"
            >
                <template
                    v-if="!loadingInitialSavedFilters(group.val)"
                >
                    <p
                        class="px-2 c-filters-picker__none"
                    >
                        {{ noFiltersMessage(group.val) }}
                    </p>
                </template>
            </template>

            <template
                #option="{ original, selectedEvents, group }"
            >
                <div
                    class="flex justify-between w-full"
                >
                    <span>
                        {{ original.name }}
                    </span>

                    <ActionButtons
                        size="sm"
                        @edit="editFilter(group, original, selectedEvents)"
                        @delete="deleteFilter(original, selectedEvents)"
                        @click.stop
                    >
                    </ActionButtons>
                </div>
            </template>

            <template
                #groupEnd="{
                    group, closePopup,
                }"
            >
                <div
                    class="c-filters-picker__end"
                >
                    <button
                        v-if="hasMoreSavedFilters(group.val)"
                        v-t="'common.loadMore'"
                        :disabled="loadingMoreSavedFilters(group.val)"
                        class="mb-2 text-xs italic hover:underline hover:text-primary-600"
                        type="button"
                        @click="loadMoreSavedFilters(group.val)"
                    >
                    </button>
                    <button
                        v-t="buttonPath(false)"
                        class="button--sm button-primary--light"
                        type="button"
                        @click="openFilterModal(group.val, closePopup, true)"
                    >
                    </button>

                    <button
                        v-if="filtersObj"
                        v-t="buttonPath(true)"
                        class="button--sm button-primary--light mt-1"
                        type="button"
                        @click="openFilterModal(group.val, closePopup, false)"
                    >
                    </button>
                </div>
            </template>
        </DropdownBox>

        <FilterSaveModal
            v-if="isModalOpen"
            :filtersObj="filtersObj"
            :editableFilter="editableFilter"
            :filterDomain="filterDomain"
            :mapping="mapping"
            :page="page"
            :filterables="filterables"
            :sortables="sortables"
            :fromBlank="setNewFilter"
            :hasPersonalDefaultInitially="hasPersonalDefaultInitially"
            :hasGeneralDefaultInitially="hasGeneralDefaultInitially"
            :showApplyButton="showApplyButton"
            @closeModal="closeFiltersModal"
            @applyFilter="applyFilter"
        >
        </FilterSaveModal>
    </div>
</template>

<script>

import SAVED_FILTERS from '@/graphql/savedFilters/queries/SavedFilters.gql';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import { deleteSavedFilter } from '@/core/repositories/savedFiltersRepository.js';

import { isActiveBaseCollaborative } from '@/core/repositories/baseRepository.js';

export default {
    name: 'FiltersPicker',
    components: {
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        // Active filter configuration if available
        filtersObj: {
            type: [Object, null],
            default: null,
        },
        domain: {
            type: [String, null],
            default: null,
            validation(value) {
                return ['PERSONAL', 'PUBLIC'].includes(value);
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
        boxStyle: {
            type: String,
            default: 'plain',
        },
        hasPersonalDefaultInitially: Boolean,
        hasGeneralDefaultInitially: Boolean,
        showApplyButton: Boolean,
    },
    emits: [
        'applyFilter',
    ],
    apollo: {
        savedPersonalFilters: {
            query: SAVED_FILTERS,
            variables() {
                const variables = { privacy: 'ONLY_PRIVATE' };
                if (this.page) {
                    variables.nodeId = this.page.id;
                }
                return variables;
            },
            skip() {
                return this.domain === 'PUBLIC';
            },
            update(data) {
                return initializeConnections(data).savedFilters;
            },
        },
        savedPublicFilters: {
            query: SAVED_FILTERS,
            variables() {
                const variables = { privacy: 'ONLY_PUBLIC' };
                if (this.page) {
                    variables.nodeId = this.page.id;
                }
                return variables;
            },
            skip() {
                return this.domain === 'PERSONAL';
            },
            update(data) {
                return initializeConnections(data).savedFilters;
            },
        },
    },
    data() {
        return {
            filterDomain: null,
            setNewFilter: true,
            editableFilter: null,
        };
    },
    computed: {
        includesPersonalFilters() {
            // Personal filters are only needed on collab bases
            return this.isCollab && (!this.domain || this.domain === 'PERSONAL');
        },
        includesPublicFilters() {
            return !this.domain || this.domain === 'PUBLIC';
        },
        groups() {
            const groups = [];
            if (this.includesPersonalFilters) {
                groups.push({
                    group: {
                        val: 'PERSONAL',
                        label: this.$t('customizations.filters.headers.myPersonal'),
                    },
                    options: this.savedPersonalFilters,
                });
            }
            if (this.includesPublicFilters) {
                groups.push({
                    group: {
                        val: 'PUBLIC',
                        label: this.publicLabel,
                    },
                    options: this.savedPublicFilters,
                });
            }
            return groups;
        },
        isCollab() {
            return isActiveBaseCollaborative();
        },
        publicLabel() {
            const pathKey = this.isCollab ? 'collabPublic' : 'personalPublic';
            return this.$t(`customizations.filters.headers.${pathKey}`);
        },
    },
    methods: {
        buttonClass(condition) {
            return condition ? 'justify-end' : 'justify-center';
        },
        noFiltersMessage(domain) {
            return domain === 'PERSONAL'
                ? 'You do not have any saved personal filters'
                : 'There are no saved filters for this page';
        },
        openFilterModal(filterDomain, closePopupFn, filterFromBlank) {
            this.filterDomain = filterDomain;
            this.setNewFilter = filterFromBlank;
            closePopupFn();
            this.openModal();
        },
        closeFiltersModal() {
            this.filterDomain = null;
            this.editableFilter = null;
            this.setNewFilter = true;
            this.closeModal();
        },
        queryKey(domain) {
            return domain === 'PERSONAL'
                ? 'savedPersonalFilters'
                : 'savedPublicFilters';
        },
        loadingInitialSavedFilters(domain) {
            const queryKey = this.queryKey(domain);
            return !this[queryKey] && this.$apollo.queries[queryKey]?.loading;
        },
        loadingMoreSavedFilters(domain) {
            const queryKey = this.queryKey(domain);
            return this[queryKey] && this.$apollo.queries[queryKey]?.loading;
        },
        hasMoreSavedFilters(domain) {
            const queryKey = this.queryKey(domain);
            return this[queryKey]?.__SavedFilterConnection.pageInfo.hasNextPage;
        },
        async loadMoreSavedFilters(domain) {
            const queryKey = this.queryKey(domain);
            const query = this.$apollo.queries[queryKey];
            const variables = {
                after: this[queryKey].__SavedFilterConnection.pageInfo.endCursor,
            };
            await query.fetchMore({ variables });
        },
        editFilter(group, filter, selectedEvents) {
            this.editableFilter = filter;
            this.openFilterModal(group.val, selectedEvents.click, false);
        },
        async deleteFilter(filter, selectedEvents) {
            selectedEvents.click();
            await deleteSavedFilter(filter);
            this.$successFeedback();
        },
        buttonPath(useObj) {
            const pathKey = useObj ? 'saveFilter' : 'createNew';
            return `customizations.filters.labels.${pathKey}`;
        },
        applyFilter(filter) {
            this.$emit('applyFilter', filter);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-filters-picker {

    &__end {
        @apply
            flex
            flex-col
            items-center
            last:mb-0
            mb-8
            mt-2
            pt-2
        ;
    }

    &__category {
        @apply
            font-semibold
            text-cm-400
            text-xs
            uppercase
        ;
    }

    &__none {
        @apply
            leading-tight
            my-1
            text-center
            text-cm-600
            text-xs
        ;
    }
}

</style>
