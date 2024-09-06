<template>
    <div
        class="o-base-uses flex flex-col items-center"
    >
        <div class="centered flex-col bg-cm-100 rounded-xl min-w-lg mb-6 py-4 px-8">
            <h3 class="mb-2 text-smbase text-cm-700 font-medium">
                {{ $t(`registration.uses.${baseTypeFormatted}.useFilters`, { group: baseName }) }}
            </h3>
            <FreeFilter
                v-model="filters.freeText"
                class="w-300p"
                size="lg"
                bgColor="white"
                freePlaceholder="Type your ideas here"
            >
            </FreeFilter>
            <div class="flex flex-wrap gap-2 max-w-xl justify-center mt-4">
                <button
                    v-for="category in allCategories"
                    :key="category"
                    class="button--sm"
                    :class="isSelectedCategoryClass(category)"
                    type="button"
                    @click="toggleCategory(category)"
                >
                    {{ $t(`registration.uses.categories.${category}`) }}
                </button>
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-2">
                <p
                    class="text-sm text-cm-600 font-medium"
                >
                    I'll customize later!
                </p>
                <button
                    class="button--sm button-primary"
                    type="button"
                    @click="registerWithoutUse"
                >
                    {{ withoutUseText }}
                </button>
            </div>
        </div>

        <div class="flex flex-wrap gap-4 justify-center">
            <UseItem
                v-for="use in filteredUses"
                :key="use.val"
                :class="selectedUseClass(use)"
                :use="use"
                :base="base"
                :isSelected="!!selectedUse(use).length"
                @click="toggleUse(use)"
            >
            </UseItem>
        </div>
    </div>
</template>

<script>

import UseItem from '@/components/access/UseItem.vue';
import FreeFilter from '@/components/sorting/FreeFilter.vue';

import filterList from '@/core/filterList.js';
import uses from '@/core/mappings/templates/uses.js';

export default {
    name: 'BaseUses',
    components: {
        UseItem,
        FreeFilter,
    },
    mixins: [
    ],
    props: {
        base: {
            type: Object,
            required: true,
        },
        preSelectedUses: {
            type: Array,
            default: () => ([]),
        },
        useCases: {
            type: Array,
            required: true,
        },
        contextLabel: {
            type: String,
            default: 'REGISTRATION',
            validator: (val) => (['REGISTRATION', 'CREATE'].includes(val)),
        },
    },
    emits: [
        'registerWithoutUse',
        'toggleUse',
    ],
    data() {
        return {
            filters: {
                freeText: '',
                categories: [],
            },
            maxChoices: 3,
        };
    },
    computed: {
        selectedUsesLength() {
            return this.selectedUses.length;
        },
        selectedUses() {
            return _.uniqBy(this.useCases, 'val');
        },
        baseType() {
            return this.base.baseType;
        },
        baseTypeFormatted() {
            return _.camelCase(this.baseType);
        },
        baseName() {
            return this.base.name;
        },
        categories() {
            return _(this.usesArr).flatMap('categories').uniq().value();
        },
        allCategories() {
            return this.categories.concat(['all']);
        },
        usesArr() {
            const validUses = uses;
            if (!this.preSelectedUses.length) {
                return _.map(validUses);
            }
            const preSelected = [];
            const remaining = [];

            _.forEach(validUses, (use) => {
                if (this.preSelectedUses.includes(use.val)) {
                    preSelected.push(use);
                } else {
                    remaining.push(use);
                }
            });

            return preSelected.concat(remaining);
        },
        filteredUses() {
            return filterList(
                this.filteredByCategory,
                this.filters,
                { keys: ['searchTerms'], threshold: 0.4 });
        },
        filtersCategories() {
            return this.filters.categories;
        },
        filteredByCategory() {
            return this.usesArr.filter((use) => {
                if (this.filters.freeText) {
                    return true;
                }
                if (!this.filters.categories.length) {
                    const initialCondition = this.isCollabBase
                        ? use.collab && use.initial
                        : use.initial;

                    return initialCondition
                        || this.preSelectedUses.includes(use.val)
                        || _.find(this.selectedUses, { val: use.val });
                }
                if (this.isSelectedCategory('all')) {
                    return true;
                }
                return _.intersection(use.categories, this.filtersCategories).length;
            });
        },
        isCollabBase() {
            return this.base.baseType === 'COLLABORATIVE';
        },
        maxReached() {
            return this.maxChoices <= this.selectedUsesLength;
        },
        contextLabelFormatted() {
            return _.camelCase(this.contextLabel);
        },
        withoutUseText() {
            return this.$t(`registration.uses.${this.contextLabelFormatted}.withoutUses`);
        },
    },
    methods: {
        selectedUse(use) {
            return _.filter(this.useCases, { val: use.val });
        },
        selectedUseClass(use) {
            return { 'opacity-50 unclickable': this.maxReached && !this.selectedUse(use).length };
        },
        registerWithoutUse() {
            this.$emit('registerWithoutUse');
        },
        toggleUse(use) {
            return this.$emit('toggleUse', use);
        },
        isSelectedCategoryClass(category) {
            return this.isSelectedCategory(category)
                ? 'button-secondary'
                : 'button-secondary--light';
        },
        isSelectedCategory(category) {
            return this.filtersCategories.includes(category);
        },
        toggleCategory(category) {
            if (category === 'all') {
                if (this.isSelectedCategory('all')) {
                    this.filters.categories = [];
                } else {
                    this.filters.categories = ['all'];
                }
            } else {
                // Remove "all" if filtering by something else
                this.filters.categories = _.without(this.filters.categories, 'all');
                if (this.isSelectedCategory(category)) {
                    this.filters.categories = _.without(this.filters.categories, category);
                } else {
                    this.filters.categories.push(category);
                }
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-base-uses {

} */

</style>
