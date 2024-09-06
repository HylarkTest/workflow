<template>
    <component
        :is="dropdownComponent"
        v-model:searchQuery="searchQuery"
        class="c-dropdown-paged"
        :options="currentPageOptions"
        :displayRule="currentDisplayRule"
        :property="currentProperty"
        :comparator="currentComparator"
        :groups="currentGroups"
        borderWidthClass="border-b"
        v-bind="$attrs"
        :blockClose="!onLastPageOrCollapsed || blockClose"
        useFormValueForDisplay
        :modelValue="modelValueForPage"
        @select="onSelect"
        @update:modelValue="updateValue"
        @close="onClose"
    >
        <template
            v-for="(_, slot) of proxySlots()"
            #[slot]="scope"
        >
            <slot
                :name="slot"
                :page="page"
                :currentPage="currentPage"
                :isOnLastPage="onLastPageOrCollapsed"
                :search="searchQuery"
                v-bind="{
                    ...scope,
                    ...customSlotScope(slot, scope),
                }"
            />
        </template>

        <template
            v-if="headerCondition(page, searchQuery)"
            #popupStart
        >
            <div
                class="px-2 mt-3 mb-2 uppercase text-cm-400 font-semibold text-xs text-center"
            >
                <slot
                    name="popupStart"
                    :page="page"
                    :search="searchQuery"
                >
                </slot>
            </div>
        </template>

        <template #noOptions>
            <p
                v-if="searchQuery"
                v-t="'common.noFilterMatches'"
                class="text-xs px-2 py-3 text-center text-cm-600"
            >
            </p>

            <p
                v-else
                class="text-xs px-2 py-3 text-center text-cm-600"
            >
                {{ searchQuery }}
                There are no options here
            </p>
        </template>

        <template
            #popupEnd="{ selectedEvents }"
        >
            <slot
                name="popupEnd"
                :selectedEvents="selectedEvents"
            >
                <BackMini
                    v-if="!onFirstPage && pageKeys.length"
                    class="text-center"
                    @click="goBack"
                >
                </BackMini>
            </slot>
        </template>

    </component>
</template>

<script>

export default {
    name: 'DropdownPaged',
    components: {

    },
    mixins: [
    ],
    props: {
        dropdownComponent: {
            type: String,
            default: 'DropdownBox',
        },
        options: {
            type: [Array, null],
            default: null,
        },
        groups: {
            type: [Array, null],
            default: null,
        },
        // An array of keys that indicate the next page of options.
        // For example, if the first page is an array of objects with a key 'items',
        // and each of the items has a sub array under the key 'subItems',
        // the pageKeys would be ['items', 'subItems'].
        pageKeys: {
            type: Array,
            required: true,
        },
        // This can be an array to define a different display rule for each page.
        displayRules: {
            type: [Array, Function, String],
            default: null,
        },
        // This can be an array to define a different property for each page.
        properties: {
            type: [Array, Function, String],
            default: null,
        },
        // This can be an array to define a different comparator for each page.
        comparators: {
            type: [Array, String, Function],
            default: () => _.isEqual,
        },
        // If true, the pages all collapse to a single page with the options
        // that match the search query.
        // At present, this does not work with the displayRules, properties,
        // or comparators as arrays.
        collapseOnSearch: Boolean,
        // A function that decides if the search bar should be displayed.
        headerCondition: {
            type: Function,
            default: () => true,
        },
        modelValue: {
            type: [Array, Object, String],
            default: null,
        },
        // If true, the dropdown will skip to the next page if there is only one
        // option.
        skipPageIfSingle: Boolean,
        blockClose: Boolean,
        searchQueryProp: {
            type: String,
            default: '',
        },
    },
    emits: [
        'update:modelValue',
        'select',
    ],
    data() {
        return {
            // An array that indicates which page the user is on.
            // For example, if the user has selected the second page on the first page,
            // the selectedOptionIndexes would be [0, 1].
            // This holds a breadcrumb trail of which pages the user took, so
            // navigating back can be achieved by removing the last index.
            selectedOptionIndexes: [],
            searchQuery: '',
        };
    },
    computed: {
        currentPageHasOneOption() {
            return this.currentPageOptions?.length === 1;
        },
        shouldCollapseOptions() {
            if (this.skipPageIfSingle && this.currentPageHasOneOption) {
                return true;
            }
            if (this.collapseOnSearch) {
                return this.searchQuery && this.onFirstPage;
            }
            return false;
        },
        allFirstPageOptions() {
            return this.hasGroups ? _.flatMap(this.groups, 'options') : this.options;
        },
        keyForCurrentPageOptions() {
            return this.pageKeys[this.indexesLength - 1];
        },
        currentPageOptions() {
            if (this.currentPage) {
                return this.currentPage[this.keyForCurrentPageOptions];
            }
            return this.allFirstPageOptions;
        },
        currentPage() {
            let options = this.allFirstPageOptions;
            let page;
            this.selectedOptionIndexes.forEach((optionIndex, index) => {
                page = options[optionIndex];
                options = page[this.pageKeys[index]];
            });
            return page;
        },
        hasGroups() {
            return !!this.groups;
        },
        currentGroups() {
            // If the user is searching and that collapses the options, then we
            // group those options with the group being the page.
            // Collapsing only works when there are max 2 pages.
            if (this.shouldCollapseOptions) {
                return this.allFirstPageOptions.map((group) => {
                    return {
                        group,
                        options: group[this.pageKeys[0]],
                    };
                });
            }
            // Grouping is only possible on the first page.
            // Subsequent pages cannot have groups.
            if (this.hasGroups && this.onFirstPage) {
                return this.groups;
            }
            return null;
        },
        indexesLength() {
            return this.selectedOptionIndexes.length;
        },
        pageKeysLength() {
            return this.pageKeys.length;
        },
        onLastPage() {
            return this.indexesLength === this.pageKeysLength;
        },
        onLastPageOrCollapsed() {
            return this.onLastPage || this.shouldCollapseOptions;
        },
        page() {
            return this.indexesLength + 1;
        },
        onFirstPage() {
            return this.page === 1;
        },
        currentDisplayRule() {
            return this.getArrayItemForPage(this.displayRules);
        },
        currentProperty() {
            return this.getArrayItemForPage(this.properties);
        },
        currentComparator() {
            return this.getArrayItemForPage(this.comparators);
        },
        modelValueForPage() {
            if (!_.isArray(this.modelValue)) {
                return this.modelValue;
            }
            return this.onLastPageOrCollapsed ? this.modelValue : null;
        },
    },
    methods: {
        // proxySlots needs to be a method because $slots is not reactive (See DropdownBox for more info).
        proxySlots() {
            return _.omit(this.$slots, ['popupEnd', 'popupStart', 'noOptions']);
        },
        getArrayItemForPage(arr) {
            if (_.isArray(arr)) {
                return arr[this.indexesLength];
            }
            return arr;
        },
        onSelect({ option, group }) {
            // Some paged items might not have sub options, in which case don't
            // handle it and emit up as it is an actual option rather than a page.
            if (this.onLastPageOrCollapsed || !this.hasSubOptions(option)) {
                const page = this.findOptionPage(option);
                const payload = {
                    page,
                    group: group || this.findGroup(page || option),
                    value: option,
                };
                this.$emit('select', payload);
                this.selectedOptionIndexes = [];
            } else {
                this.selectedOptionIndexes.push(this.currentPageOptions.indexOf(option));
            }
        },
        findOptionPage(option) {
            let page;
            let pages = this.allFirstPageOptions;
            if (pages.includes(option)) {
                return page;
            }
            for (const key of this.pageKeys) {
                page = pages.find((_page) => _page[key].includes(option));
                if (page) {
                    return page;
                }
                pages = pages.flatMap((_option) => _option[key]);
            }
            return null;
        },
        hasSubOptions(option) {
            const key = this.pageKeys[this.indexesLength];
            return !_.isUndefined(option[key]);
        },
        updateValue(event) {
            // If the event is null then DropdownBasic wants to clear modelValue,
            // so we emit.
            // If we or not on the last page then this component handles the
            // update, so we don't want to emit.
            if (!event || this.onLastPageOrCollapsed) {
                this.$emit('update:modelValue', event);
            }
        },
        customSlotScope(slot, scope) {
            if (slot === 'option') {
                return {
                    isLastSeries: !this.hasSubOptions(scope.original),
                };
            }
            return {};
        },
        goBack() {
            this.selectedOptionIndexes.pop();
            this.searchQuery = '';
        },
        findGroup(option) {
            if (this.hasGroups) {
                return this.groups.find((group) => {
                    return group.options.includes(option);
                });
            }
            return null;
        },
        onClose() {
            this.selectedOptionIndexes = [];
            this.searchQuery = '';
        },
        updateSearchQuery(searchQuery) {
            this.searchQuery = searchQuery;
        },

    },
    watch: {
        searchQueryProp(propSearchQuery) {
            this.updateSearchQuery(propSearchQuery);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-dropdown-paged {

}*/

</style>
