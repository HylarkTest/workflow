<template>
    <div class="o-view-edit-data relative">

        <div
            class="sticky top-0.5 z-alert flex justify-end w-full transition-3eio"
            :class="isScrolledToBox ? 'opacity-100' : 'opacity-0'"
        >
            <button
                class="button-rounded--sm mr-0.5"
                :class="stickyToggleClass"
                type="button"
                @click="toggleSticky"
            >
                {{ stickyText }}
            </button>
        </div>

        <!-- Wrapper element below to prevent scroll content poking out the sides
            and around top rounded edges of sticky banner -->
        <div
            ref="topBox"
            class="z-cover mb-8 -mx-3 bg-cm-00 px-3"
            :class="stickyClass"
        >
            <div
                class="rounded-xl bg-cm-100 p-4"
            >
                <h3
                    class="header-3 mb-2"
                >
                    Select a slot from the template to assign your data to that slot
                </h3>

                <component
                    :is="previewComponent"
                    :templateComponent="templateComponent"
                    :selectorMode="true"
                    :dataStructure="dataForm.visibleData"
                    :selectedSlot="selectedSlot"
                    :isSummaryView="true"
                    @selectSlot="selectSlot"
                >
                </component>

            </div>
        </div>
        <div class="pb-4">
            <h3
                class="header-3 mb-5"
            >
                Possible options for the selected slot
            </h3>

            <div
                v-if="validDataLength"
                class="o-view-edit-data__tabs flex flex-wrap gap-2 mb-4"
            >
                <button
                    v-for="tab in dataTabs"
                    :key="tab"
                    class="button-rounded"
                    :class="buttonClass(tab)"
                    type="button"
                    @click="selectDataTab(tab)"
                >
                    {{ $t(dataTabPath(tab)) }}
                </button>
            </div>

            <div>
                <button
                    class="button button-gray mb-4"
                    :class="{ unclickable: !selectedSlotId }"
                    type="button"
                    :title="!selectedSlotId ? 'Select a slot first' : 'Clear this slot'"
                    @click="clearSlot"
                >
                    Clear this slot
                </button>
            </div>

            <div>
                <div
                    v-for="(group, groupKey) in groupedData"
                    :key="groupKey"
                    class="mb-6 last:mb-0"
                >
                    <h4
                        v-if="groupKey !== 'undefined'"
                        class="mb-2 font-semibold text-primary-600"
                    >
                        <i
                            v-if="isList(group)"
                            class="fa-regular fa-bars mr-2 text-primary-600"
                        >
                        </i>

                        {{ getGroupHeader(groupKey, group) }}
                    </h4>

                    <div>
                        <div
                            v-for="(sub, index) in getSubSourceForSelection(group)"
                            :key="index"
                            class="mb-4 last:mb-0"
                        >
                            <DataNameDisplay
                                v-if="sub.info?.options?.hasSubSections"
                                class="mb-1 font-semibold text-smbase"
                                :dataObj="sub"
                            >
                            </DataNameDisplay>

                            <div class="o-view-edit-data__items grid gap-1">

                                <ButtonEl
                                    v-for="item in getSubOptionsForSelection(sub)"
                                    :key="item.formattedId"
                                    class="relative"
                                    :class="buttonClasses(item)"
                                    @click="selectItem(item)"
                                >
                                    <div
                                        v-if="isAlreadySelected(item)"
                                        class="absolute h-full w-full centered z-over"
                                    >
                                        <div
                                            class="absolute h-full w-full bg-cm-00 opacity-40 centered"
                                        >
                                        </div>

                                        <div
                                            class="py-1 px-2 relative z-over bg-primary-100 text-xs
                                                   font-medium rounded-lg"
                                        >
                                            Already selected
                                        </div>
                                    </div>

                                    <DataInfo
                                        class="o-view-edit-data__item h-full hover:shadow-lg"
                                        :class="selectedClasses(item)"
                                        :item="item"
                                    >
                                    </DataInfo>

                                    <i
                                        v-if="isSelected(item.formattedId)"
                                        class="o-view-edit-data__icon fas fa-circle-check"
                                    >
                                    </i>
                                </ButtonEl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <p
                v-if="!validData.length"
                class="text-center mt-6"
            >
                There is no data available for that slot. Add more fields!
            </p>
        </div>
    </div>
</template>

<script>

import DataInfo from './DataInfo.vue';
import PreviewKanban from './PreviewKanban.vue';
import PreviewTile from './PreviewTile.vue';
import PreviewLine from './PreviewLine.vue';
import DataNameDisplay from '@/components/customize/DataNameDisplay.vue';

import scrolling from '@/components/data/scrolling.js';

import {
    getGroupHeader,
    getGroupedDataFromSections,
    visibleDataFlatAndFormatted,
    getPickerOptions,
    getExpandedData,
    getSubSourceForSelection,
    getSubOptionsForSelection,
} from '@/core/display/theStandardizer.js';

import {
    getUsedSlotsDefault,
} from '@/core/display/getAllEntityData.js';

import {
    getTemplates,
    getDataForSlots,
} from '@/core/display/cardInstructions.js';
import { updatePageView } from '@/core/repositories/pageRepository.js';

export default {
    name: 'ViewEditData',
    components: {
        DataNameDisplay,
        DataInfo,
        PreviewTile,
        PreviewKanban,
        PreviewLine,
    },
    mixins: [
        scrolling,
    ],
    props: {
        allAvailableData: {
            type: Object,
            required: true,
        },
        page: {
            type: Object,
            required: true,
        },
        view: {
            type: Object,
            required: true,
        },
    },
    data() {
        const allDataObj = this.getAllData(); // For display of options
        const allDataFlat = getExpandedData(this.allAvailableData); // For processing selection
        const templateOptions = getTemplates(this.view.viewType);

        let visibleData;

        if (this.view.visibleData) {
            const baseData = visibleDataFlatAndFormatted(this.view.visibleData, allDataFlat);
            const filteredByUsedSlots = getDataForSlots(baseData, this.view);
            visibleData = filteredByUsedSlots;
        } else {
            visibleData = getUsedSlotsDefault(allDataFlat);
        }

        return {
            dataForm: this.$apolloForm(() => {
                return {
                    ...this.view,
                    visibleData,
                };
            }, {
                reportValidation: true,
            }),
            allDataObj,
            selectedSlot: 'HEADER1',
            templateComponent: this.view?.template || templateOptions[0],
            processingItem: null,
            viewedDataType: 'FIELDS',
            isScrolledToBox: false,
            isStickied: true,
        };
    },
    computed: {
        typeData() {
            return this.allDataObj[this.viewedDataType];
        },
        validData() {
            return this.typeData.filter((item) => {
                return this.meetsSlotCondition(item);
            });
        },
        groupedData() {
            return getGroupedDataFromSections(this.validData, this.viewedDataType);
        },
        dataTabs() {
            if (this.slotIsHeader) {
                return ['FIELDS'];
            }
            if (this.slotIsImage) {
                return ['FIELDS'];
            }
            return _.keys(this.allDataObj);
        },
        previewComponent() {
            return `Preview${_.pascalCase(this.viewVal)}`;
        },
        slotIsHeader() {
            return this.selectedSlot.includes('HEADER');
        },
        slotIsImage() {
            return this.selectedSlot.includes('IMAGE');
        },
        slotIsRegular() {
            return this.selectedSlot.includes('REG');
        },
        viewVal() {
            return this.view.viewType;
        },
        slotMap() {
            return _(this.dataForm.visibleData).map((item) => {
                return [
                    item.slot,
                    item.formattedId,
                ];
            }).fromPairs().value();
        },
        selectedSlotId() {
            return this.slotMap[this.selectedSlot];
        },
        selectedSlotObject() {
            return _.find(this.allDataObj, { formattedId: this.selectedSlotId });
        },
        slotDataIds() {
            return _.map(this.dataForm.visibleData, 'formattedId');
        },
        populatedSlots() {
            return _.map(this.dataForm.visibleData, 'slot');
        },
        validDataLength() {
            return this.validData.length;
        },
        stickyToggleClass() {
            return this.isStickied ? 'button-secondary--light' : 'button-secondary';
        },
        stickyText() {
            if (this.isScrolledToBox && this.isStickied) {
                return 'Unpin preview from top';
            }
            if (this.isScrolledToBox && !this.isStickied) {
                return 'Pin preview to top';
            }
            return '';
        },
        stickyClass() {
            return this.isStickied ? 'sticky top-0' : '';
        },

    },
    methods: {
        selectedClasses(item) {
            return this.isSelected(item.formattedId)
                ? 'shadow-lg shadow-primary-600/20 border-primary-600'
                : 'border-cm-200';
        },
        buttonClass(tab) {
            return this.isSelectedDataTab(tab)
                ? 'button-primary'
                : 'button-primary--light';
        },
        isSelectedDataTab(tab) {
            return tab === this.viewedDataType;
        },
        buttonClasses(item) {
            const unclickableClass = this.isAlreadySelected(item)
                ? 'pointer-events-none'
                : '';
            return `${this.processingClass(item)} ${unclickableClass}`;
        },
        processingClass(item) {
            if (this.processingItem === item.formattedId) {
                return 'unclickable';
            }
            return '';
        },
        getAllData() {
            return getPickerOptions(this.allAvailableData);
        },
        isSelected(id) {
            return this.selectedSlotId === id;
        },
        selectItem(item) {
            if (!this.isAlreadySelected(item)) {
                const slotIndex = _.findIndex(this.dataForm.visibleData, { slot: this.selectedSlot });
                const isSelected = this.isSelected(item.formattedId);

                if (~slotIndex) {
                    // Removes if slot already selected by the value, or by another value
                    this.dataForm.visibleData.splice(slotIndex, 1);
                }
                if (!isSelected) {
                    const clone = _.clone(item);
                    clone.slot = this.selectedSlot;
                    this.dataForm.visibleData.push(clone);
                }

                this.saveData(item);
            }
        },
        meetsSlotCondition(item) {
            const notImage = item.info?.subType !== 'IMAGE';
            if (this.slotIsHeader) {
                return item.dataType === 'FIELDS' && notImage;
            }
            if (this.slotIsImage) {
                return item.info?.subType === 'IMAGE';
            }
            return notImage;
        },
        selectSlot(event) {
            const slot = event.slot;
            this.selectedSlot = slot;
            this.scrollToTop();
        },
        scrollToTop() {
            this.$el.scrollIntoView();
        },
        async saveData(item) {
            this.processingItem = item.formattedId;
            try {
                await updatePageView(this.dataForm, this.page);
                this.$debouncedSaveFeedback();
            } finally {
                this.processingItem = null;
            }
        },
        selectDataTab(tab) {
            this.viewedDataType = tab;
        },
        dataTabPath(tab) {
            return `labels.${_.camelCase(tab)}`;
        },
        getParent(group) {
            return group[0]?.info?.parent;
        },
        isList(group) {
            const first = group[0];
            return first.info?.options?.list;
        },
        getSubSourceForSelection(group) {
            return getSubSourceForSelection(group);
        },
        getSubOptionsForSelection(group) {
            return getSubOptionsForSelection(group);
        },
        getGroupHeader(groupKey, group) {
            return getGroupHeader(groupKey, group);
        },
        isAlreadySelected(data) {
            const formattedId = data.formattedId;
            return this.slotDataIds.includes(formattedId)
                && !this.isSelected(formattedId);
        },
        onScroll() {
            this.isScrolledToBox = this.getScrollPosition();
        },
        getScrollPosition() {
            const offset = this.scrollTop();

            return offset > 100;
        },
        getScrollEl() {
            return this.$el;
        },
        toggleSticky() {
            this.isStickied = !this.isStickied;
        },
        clearSlot() {
            const slotItem = _.find(this.dataForm.visibleData, { slot: this.selectedSlot });
            if (slotItem) {
                this.selectItem(slotItem);
            }
        },

    },
    watch: {
        selectedSlot: {
            immediate: true,
            handler(newVal) {
                if (!this.dataTabs.includes(newVal)) {
                    const tab = this.selectedSlotObject?.dataType || 'FIELDS';
                    this.viewedDataType = tab;
                }
            },
        },
    },
    created() {
    },
    mounted() {
        this.onScroll();
    },
};
</script>

<style scoped>

.o-view-edit-data {
    &__items {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr))
    }

    &__item {
        transition: 0.2s ease-in-out;

        @apply
            border
            border-solid
            px-2
            py-3
            rounded-lg
        ;
    }

    &__icon {
        @apply
            absolute
            bg-cm-00
            leading-none
            -right-1
            rounded-full
            text-2xl
            text-primary-600
            -top-1
        ;
    }
}

</style>
