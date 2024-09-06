import DraggableList from '@/components/views/DraggableList.vue';

import { getTemplates } from '@/core/display/cardInstructions.js';
import { allData, getColumnDefaults } from '@/core/display/getAllEntityData.js';

import {
    getAllAvailableDataFormatted,
    visibleDataFlatAndFormatted,
} from '@/core/display/theStandardizer.js';

export default {
    components: {
        DraggableList,
    },
    props: {
        page: {
            type: [Object, null],
            default: null,
        },
        items: {
            type: Array,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
        currentView: {
            type: [Object, null],
            default: null,
        },
        isReadOnly: Boolean,
    },
    data() {
        return {
            viewType: '', // Defined in component. KANBAN, TILE, LINE
        };
    },
    computed: {
        view() {
            const allViews = this.page?.design?.views || [];
            return allViews.find((view) => view.id === this.currentView?.id);
        },
        visibleData() {
            return this.view?.visibleData;
        },

        isSpreadsheet() {
            return this.viewType === 'SPREADSHEET';
        },
        defaultTemplate() {
            if (!this.isSpreadsheet) {
                return getTemplates(this.viewType)[0];
            }
            return null;
        },
        savedComponent() {
            return this.view?.template;
        },
        templateComponent() {
            // Prop or default
            return this.savedComponent || this.defaultTemplate;
        },
        formattedData() {
            return getAllAvailableDataFormatted(this.allAvailableData);
        },
        allAvailableData() {
            return allData(this.mapping);
        },
        visibleUpdated() {
            return visibleDataFlatAndFormatted(this.visibleData, this.formattedData);
        },
        validData() {
            if (this.visibleData?.length) {
                return this.visibleUpdated;
            }
            if (this.isSpreadsheet) {
                return getColumnDefaults(this.formattedData);
            }
            return this.filteredForDefault;
        },
        filteredForDefault() {
            return [this.systemNameWithSlot];
        },
        systemName() {
            return this.formattedData.find((item) => {
                return item.info?.subType === 'SYSTEM_NAME' || item.info?.subType === 'NAME';
            });
        },
        systemNameWithSlot() {
            const name = _.clone(this.systemName);
            name.slot = 'HEADER1';
            return name;
        },
    },
};
