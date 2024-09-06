<template>
    <div class="o-import-check bg-cm-00 p-4 rounded-lg">
        <div
            v-md-text="$t('imports.headers.importingBlueprint', {
                blueprintName: mapping.name,
            })"
            class="text-xl mb-6"
        >
        </div>

        <div
            v-if="unmappedColumnsLength"
            class="bg-secondary-100 rounded-xl p-4"
        >
            <h4 class="font-semibold text-secondary-800 text-lg">
                {{ $t('imports.headers.unmappedColumns') }}
            </h4>
            <p class="mb-2">
                {{ $t('imports.headers.notIncluded') }}
            </p>

            <ul class="ml-5">
                <li
                    v-for="column in unmappedColumns"
                    :key="column.column"
                >
                    <i
                        class="fa-regular fa-circle-exclamation mr-1 text-gold-600"
                    >
                    </i>

                    {{ column.data[0] }}
                </li>
            </ul>
        </div>

        <div
            class="h-divider my-8"
        >
        </div>

        <div class="text-lg mb-2 font-semibold">
            {{ $t('imports.headers.dataPreview') }}
        </div>

        <div
            class="min-w-0 max-w-full z-0 relative"
        >
            <SpreadsheetLayout
                :items="validPreview"
                :mapping="mapping"
                :currentView="pseudoCurrentView"
                :page="pseudoPage"
                :isReadOnly="true"
                :hasWidthToFit="true"
            >
                <template
                    #cell="scope"
                >
                    <!-- dataInfo and item added separately
                        due to their importance and for prop validation -->
                    <ImportCheckCell
                        :dataInfo="scope.dataInfo"
                        :item="scope.item"
                        :scope="scope"
                    >
                    </ImportCheckCell>
                </template>
            </SpreadsheetLayout>
        </div>
    </div>
</template>

<script setup>

import {
    computed,
} from 'vue';

import SpreadsheetLayout from '@/components/views/SpreadsheetLayout.vue';
import ImportCheckCell from '@/components/dataManagement/ImportCheckCell.vue';
import { getFormattedId } from '@/core/display/theStandardizer.js';

const props = defineProps({
    mapping: {
        type: Object,
        required: true,
    },
    form: {
        type: Object,
        required: true,
    },
    fileData: {
        type: Array,
        required: true,
    },
    preview: {
        type: Array,
        required: true,
    },
    previewErrors: {
        type: Array,
        default: null,
    },
});

const validPreview = computed(() => {
    return props.preview.map((item, index) => {
        if (item) {
            return item;
        }
        const getError = props.previewErrors.find((error) => {
            return parseInt(error.index, 10) === index;
        });
        const error = getError?.error || 'Row could not be imported';
        // Since row is null
        const id = _.random(0, 10000, 10000);
        return {
            id: _.toString(id),
            rowError: error,
        };
    });
});

const unmappedColumns = computed(() => {
    return props.fileData.filter((info) => {
        return !_.find(props.form.columnMap, { column: info.column });
    }) || [];
});

const unmappedColumnsLength = computed(() => unmappedColumns.value.length);

const dataColumns = computed(() => {
    return _(props.form.columnMap).map((column) => {
        let dataType;
        if (column.fieldId) {
            dataType = 'FIELDS';
        }
        const option = {
            id: column.fieldId,
        };
        return {
            dataType,
            formattedId: getFormattedId(option, null, dataType, null),
        };
    }).uniqBy('formattedId').value();
});
const visibleData = computed(() => {
    return _.orderBy(dataColumns.value, (item) => {
        return item.formattedId === 'SYSTEM_NAME';
    }, 'desc');
});

const pseudoPage = computed(() => ({
    design: {
        views: [{
            id: 'IMPORT',
            visibleData: visibleData.value,
        }],
    },
}));

const pseudoCurrentView = computed(() => ({
    id: 'IMPORT',
    visibleData: visibleData.value,
}));

</script>

<style scoped>

/*.o-import-check {

} */

</style>
