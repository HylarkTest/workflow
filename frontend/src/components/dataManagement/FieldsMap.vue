<template>
    <div class="o-fields-map">

        <FieldsMapDisplay
            v-if="showTopHeaders"
        >
            <template
                #one
            >
                <label
                    class="o-fields-map__label"
                >
                    Column name
                </label>
            </template>

            <template
                #two
            >
                <label
                    class="o-fields-map__label"
                >
                    Extracted data examples
                </label>
            </template>

            <template
                #three
            >
                <label
                    class="o-fields-map__label"
                >
                    Hylark field
                </label>
            </template>
        </FieldsMapDisplay>

        <div
            ref="columns"
        >
            <FieldMap
                v-for="(column, index) in columns"
                :key="index"
                :modelValue="modelValue"
                class="odd:bg-cm-00 even:bg-primary-100 p-3"
                :mapping="mapping"
                :pickerBgColor="getPickerBgColor(index)"
                :column="column"
                :showHeaders="!showTopHeaders"
                :importableFieldTypes="importableFieldTypes"
                @update:modelValue="$emit('update:modelValue', $event)"
            >

            </FieldMap>
        </div>
    </div>
</template>

<script>

import FieldsMapDisplay from '@/components/dataManagement/FieldsMapDisplay.vue';
import FieldMap from '@/components/dataManagement/FieldMap.vue';

import listensToScrollandResizeEvents from '@/vue-mixins/listensToScrollAndResizeEvents.js';

import { isEvenNumber } from '@/core/utils.js';

const importableFieldTypes = [
    'BOOLEAN',
    'CURRENCY',
    'DATE',
    'DATE_TIME',
    'TIME',
    'EMAIL',
    'NUMBER',
    'LINE',
    'NAME',
    'PARAGRAPH',
    'PHONE',
    'RATING',
    'SYSTEM_NAME',
    'URL',
];

export default {
    name: 'FieldsMap',
    components: {
        FieldsMapDisplay,
        FieldMap,
    },
    mixins: [
        listensToScrollandResizeEvents,
    ],
    props: {
        mapping: {
            type: Object,
            required: true,
        },
        columns: {
            type: Array,
            required: true,
        },
        modelValue: {
            type: Array,
            required: true,
        },
    },
    emits: [
        'update:modelValue',
    ],
    data() {
        return {
            columnsWidth: 0,
        };
    },
    computed: {
        showTopHeaders() {
            return this.columnsWidth > 695;
        },
        mappingId() {
            return this.mapping.id;
        },
    },
    methods: {
        getPickerBgColor(index) {
            return isEvenNumber(index) ? 'gray' : 'white';
        },
        onResize() {
            this.columnsWidth = this.$refs.columns.offsetWidth;
        },
    },
    created() {
        this.importableFieldTypes = importableFieldTypes;
    },
    mounted() {
        this.onResize();
    },
};
</script>

<style scoped>

.o-fields-map {
    &__label {
        @apply
            font-bold
            mb-1
            text-cm-400
            text-sm
        ;
    }
}

</style>
