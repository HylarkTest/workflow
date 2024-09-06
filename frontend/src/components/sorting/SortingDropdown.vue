<template>
    <DropdownBox
        class="c-sorting-dropdown"
        :modelValue="sortOrder"
        :options="sortableOptions"
        :groups="sortableGroups"
        :inlineLabel="label"
        :displayRule="sortableDisplay"
        :hideToggleButton="hideToggleButton"
        :hideValue="hideValue"
        comparator="value"
        :bgColor="bgColor"
        :popupProps="{ widthProp: '11.25rem', alignCenter: true }"
        @update:modelValue="emitSort"
    >
        <template
            #option="{ display, closePopup, original }"
        >
            <div class="flex justify-between w-full">
                {{ display }}

                <div
                    v-if="original.direction"
                    class="flex w-[40px] ml-2 gap-0.5"
                >
                    <IconActionButton
                        v-if="showDirectionButton(original, 'ASC')"
                        class="flex-1"
                        size="sm"
                        :textPath="ascSortPath"
                        :buttonSpecs="directionButtonOptionAsc(original)"
                        @click.stop="setDirection(original, 'ASC', closePopup)"
                    >
                    </IconActionButton>
                    <IconActionButton
                        v-if="showDirectionButton(original, 'DESC')"
                        class="flex-1"
                        size="sm"
                        :textPath="descSortPath"
                        :buttonSpecs="directionButtonOptionDesc(original)"
                        @click.stop="setDirection(original, 'DESC', closePopup)"
                    >
                    </IconActionButton>
                </div>
            </div>
        </template>

        <template
            v-if="hasDirection"
            #inlineDisplayAfter="{ closePopup }"
        >
            <div
                class="justify-end flex-1 flex z-over relative ml-2"
            >
                <IconActionButton
                    size="sm"
                    :textPath="remainingDirectionTextPath"
                    :buttonSpecs="directionButton"
                    @click.stop="toggleDirection(closePopup)"
                >
                </IconActionButton>
            </div>
        </template>

        <template
            v-if="hasNewFilterButton"
            #popupEnd
        >
            <div class="flex justify-center py-1 px-2">
                <button
                    class="button--sm button-primary--light"
                    type="button"
                    @click="openModal"
                >
                    Save sort order
                </button>
            </div>

            <FilterSaveModal
                v-if="isModalOpen"
                :filtersObj="filtersObj"
                :mapping="mapping"
                :page="page"
                filterDomain="PUBLIC"
                :filterables="filterables"
                :sortables="sortables"
                @closeModal="closeModal"
                @applyFilter="applyFilter"
            >
            </FilterSaveModal>

        </template>
    </DropdownBox>
</template>

<script>

import IconActionButton from '@/components/buttons/IconActionButton.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

const iconBgColor = {
    white: 'text-cm-600 bg-cm-200 hover:bg-cm-300',
    gray: 'text-cm-600 bg-cm-00 hover:bg-primary-200 hover:text-primary-600',
    primary: 'text-cm-600 bg-cm-00 hover:bg-primary-200 hover:text-primary-600',
};

const ascSortPath = 'common.sortByAscending';
const descSortPath = 'common.sortByDescending';

export default {
    name: 'SortingDropdown',
    components: {
        IconActionButton,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        sortables: {
            type: Array,
            required: true,
        },
        sortOrder: {
            type: Object,
            required: true,
        },
        bgColor: {
            type: String,
            default: 'white',
            validator(value) {
                return ['white', 'gray'].includes(value);
            },
        },
        hideValue: Boolean,
        hideToggleButton: Boolean,
        hideLabel: Boolean,
        hasNewFilterButton: Boolean,
        page: {
            type: [Object, null],
            default: null,
        },
        filterables: {
            type: [Array, null],
            default: null,
        },
        filtersObj: {
            type: [Object, null],
            default: null,
        },
        mapping: {
            type: [null, Object],
            default: null,
        },
    },
    emits: [
        'update:sortOrder',
        'applyFilter',
    ],
    data() {
        return {
            inlineLabel: {
                icon: 'fal fa-sort',
                text: this.$t('common.sort'),
                position: 'inside',
                hideColon: true,
            },
        };
    },
    computed: {
        label() {
            return !this.hideLabel ? this.inlineLabel : null;
        },
        hasDirection() {
            return !!this.sortOrder.direction;
        },
        isAscending() {
            return this.hasDirection && this.sortOrder.direction === 'ASC';
        },
        sortIcon() {
            if (this.hasDirection) {
                return this.sortOrder.direction === 'ASC' ? 'arrow-up' : 'arrow-down';
            }
            return null;
        },
        directionButtonOption() {
            if (this.hasDirection) {
                return {
                    colorClasses: iconBgColor.primary,
                };
            }
            return null;
        },
        iconBgClass() {
            return iconBgColor[this.bgColor];
        },
        usesGroups() {
            return this.sortables.some((sortable) => !!sortable.group);
        },
        sortableOptions() {
            return !this.usesGroups ? this.sortables : null;
        },
        sortableGroups() {
            return this.usesGroups ? this.sortables : null;
        },
        remainingDirectionTextPath() {
            return this.isAscending ? descSortPath : ascSortPath;
        },
        directionButton() {
            if (this.hasDirection) {
                return {
                    colorClasses: this.iconBgClass,
                    icon: `far fa-${this.sortIcon}`,
                };
            }
            return null;
        },

    },
    methods: {
        emitSort(sort, direction = null, closePopupFn = null) {
            let emitValue;
            if (sort) {
                emitValue = {
                    namePath: sort.namePath,
                    value: sort.value,
                };

                const directionVal = direction || sort.direction;

                if (directionVal) {
                    emitValue.direction = directionVal;
                }
            } else {
                emitValue = sort;
            }
            if (closePopupFn) {
                closePopupFn();
            }
            this.$emit('update:sortOrder', emitValue);
        },
        toggleDirection(closePopupFn) {
            const newDirection = this.isAscending ? 'DESC' : 'ASC';
            this.emitSort(this.sortOrder, newDirection, closePopupFn);
        },
        setDirection(newSort, newDirection, closePopupFn) {
            this.emitSort(newSort, newDirection, closePopupFn);
        },
        isCurrentlySelected(sortable) {
            return sortable.value === this.sortOrder.value;
        },
        applyFilter(filter) {
            this.$emit('applyFilter', filter);
        },
        directionButtonOptionAsc(original) {
            const color = this.isCurrentlySelected(original)
                ? iconBgColor.primary
                : iconBgColor.white;
            return {
                colorClasses: color,
                icon: 'far fa-arrow-up',
            };
        },
        directionButtonOptionDesc(original) {
            const color = this.isCurrentlySelected(original)
                ? iconBgColor.primary
                : iconBgColor.white;
            return {
                colorClasses: color,
                icon: 'far fa-arrow-down',
            };
        },
        isDirectionSelected(direction) {
            return this.sortOrder.direction === direction;
        },
        showDirectionButton(original, direction) {
            if (!this.isCurrentlySelected(original)) {
                return true;
            }
            return !this.isDirectionSelected(direction);
        },
    },
    created() {
        this.ascSortPath = ascSortPath;
        this.descSortPath = descSortPath;

        this.sortableDisplay = (option) => option.name || this.$t(option.namePath);
    },
};
</script>

<style scoped>
.o-sorting-dropdown {
    @apply relative;

    &__box {
        @apply items-baseline;
    }

    &__header {
        @apply
            pb-1
            pt-2
            px-3
        ;
    }

    &__label {
        @apply
            font-semibold
            text-cm-500
            text-xs
            uppercase
        ;
    }
}
</style>
