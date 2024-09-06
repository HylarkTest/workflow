<template>
    <div class="o-view-edit-appearance flex flex-col">
        <div class="flex flex-1 min-h-0 ">
            <div class="flex flex-col mr-4 w-40 shrink-0">
                <h3 class="header-uppercase mb-2">
                    Data options
                </h3>

                <div class="overflow-y-auto flex-1 pr-3">
                    <ButtonEl
                        v-for="item in form.visibleData"
                        :key="item.formattedId"
                        class="o-view-edit-appearance__item relative"
                        :class="{ 'o-view-edit-appearance__item--selected': isSelected(item.formattedId) }"
                        @click="selectItem(item)"
                    >
                        <DataInfo
                            class="mr-2 hover:ml-2 hover:mr-0 transition-2eio"
                            :item="item"
                        >
                        </DataInfo>

                        <!-- <div
                            v-show="isSelected(item.formattedId)"
                            class="absolute top-0 left-0 bg-primary-200 w-0.5 h-full"
                        >

                        </div> -->
                    </ButtonEl>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="mb-8 rounded-xl bg-cm-100 p-4 mb-8">
                    <h3 class="header-uppercase mb-2">
                        Preview
                    </h3>
                    <component
                        ref="container"
                        :is="previewComponent"
                        :templateComponent="templateComponent"
                        :dataStructure="form.visibleData"
                        :columns="form.visibleData"
                        :page="page"
                        :previewMode="true"
                        :selectedSlot="selectedSlot"
                        :isSummaryView="true"
                        @selectSlot="selectSlot"
                    >
                    </component>
                </div>
                <div>
                    <h3 class="header-uppercase mb-3">
                        Appearance options
                    </h3>
                    <DesignOptions
                        v-model:data="selectedData"
                    >
                    </DesignOptions>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import DataInfo from './DataInfo.vue';
import PreviewSpreadsheet from './PreviewSpreadsheet.vue';
import PreviewKanban from './PreviewKanban.vue';
import PreviewTile from './PreviewTile.vue';
import PreviewLine from './PreviewLine.vue';
import DesignOptions from './DesignOptions.vue';

import {
    getTemplates,
    getDataForSlots,
} from '@/core/display/cardInstructions.js';

import {
    getUsedSlotsDefault,
    getColumnDefaults,
} from '@/core/display/getAllEntityData.js';

import {
    getExpandedData,
    visibleDataFlatAndFormatted,
} from '@/core/display/theStandardizer.js';

import { updatePageView } from '@/core/repositories/pageRepository.js';

export default {
    name: 'ViewEditAppearance',
    components: {
        DataInfo,
        PreviewSpreadsheet,
        PreviewTile,
        PreviewKanban,
        PreviewLine,
        DesignOptions,
    },
    mixins: [
    ],
    props: {
        view: {
            type: Object,
            required: true,
        },
        page: {
            type: Object,
            required: true,
        },
        viewName: {
            type: String,
            required: true,
        },
        allAvailableData: {
            type: Object,
            required: true,
        },
    },
    data() {
        const allData = getExpandedData(this.allAvailableData);
        const templateOptions = getTemplates(this.view.viewType);
        let visibleData;

        if (this.view.visibleData) {
            const baseData = visibleDataFlatAndFormatted(this.view.visibleData, allData);
            const filteredByUsedSlots = getDataForSlots(baseData, this.view);
            visibleData = filteredByUsedSlots;
        } else {
            visibleData = this.getDefault(allData, this.view.viewType);
        }

        const form = this.$apolloForm({
            ...this.view,
            visibleData,
        }, {
            reportValidation: true,
        });
        return {
            form,
            selectedFormattedId: form.visibleData[0].formattedId,
            templateComponent: this.getTemplateComponent(templateOptions, this.view.viewType),
            allData,
            processing: false,
        };
    },
    computed: {
        selectedData: {
            get() {
                return _.find(this.form.visibleData, ['formattedId', this.selectedFormattedId]);
            },
            set(data) {
                const index = _.findIndex(this.form.visibleData, ['formattedId', this.selectedFormattedId]);
                this.form.visibleData[index] = data;
            },
        },
        viewVal() {
            return this.view.viewType;
        },
        previewComponent() {
            return `Preview${_.pascalCase(this.viewVal)}`;
        },
        hasScrollToRef() {
            return this.isSpreadsheet;
        },
        isSpreadsheet() {
            return this.viewVal === 'SPREADSHEET';
        },
        selectedSlot() {
            return this.isSpreadsheet
                ? this.selectedData.formattedId
                : this.selectedData.slot;
        },
    },
    methods: {
        selectSlot(event) {
            this.selectItem(event.dataInfo);
        },
        isSelected(id) {
            return this.selectedFormattedId === id;
        },
        selectItem(item) {
            this.selectedFormattedId = item.formattedId;
            if (this.hasScrollToRef) {
                this.scrollXToRef(item);
            }
        },
        scrollXToRef(item) {
            const id = item.formattedId;
            const el = this.$refs.container.$refs.header.colRefs[id];
            el.scrollIntoView({ behavior: 'smooth', inline: 'center' });
            // return id;
        },
        getDefault(allData, viewType) {
            const isSpreadsheet = viewType === 'SPREADSHEET';
            if (isSpreadsheet) {
                return getColumnDefaults(allData);
            }
            return getUsedSlotsDefault(allData);
        },
        getTemplateComponent(templateOptions, viewType) {
            const isSpreadsheet = viewType === 'SPREADSHEET';
            if (isSpreadsheet) {
                return '';
            }
            if (this.view?.template) {
                return this.view.template;
            }
            return templateOptions && templateOptions[0];
        },
        async saveView() {
            this.processing = true;
            try {
                await updatePageView(this.form, this.page);
                this.$debouncedSaveFeedback();
            } finally {
                this.processing = false;
            }
        },
    },
    watch: {
        'form.visibleData': {
            deep: true,
            handler() {
                this.saveView();
            },
        },
    },
    created() {

    },
};
</script>

<style>

.o-view-edit-appearance {
    &__item {
        transition: 0.2s ease-in-out;

        @apply
            border-b
            border-cm-300
            border-solid
            px-2
            py-3
            rounded-lg
        ;

        &--selected {
            @apply
                bg-cm-100
            ;
        }

        &:last-child {
            @apply
                border-none
            ;
        }
    }
}

</style>
