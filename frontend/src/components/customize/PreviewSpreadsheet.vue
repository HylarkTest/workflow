<template>
    <div
        ref="container"
        class="o-preview-spreadsheet relative"
    >
        <!-- Teleport element here -->

        <div class="overflow-x-auto">
            <table class="table-reset">
                <SpreadsheetHeader
                    ref="header"
                    :columns="columns"
                    :gridMap="gridMap"
                >
                </SpreadsheetHeader>

                <tbody
                    v-if="gridMap"
                    class="bg-cm-00"
                >
                    <Spreadsheet1
                        :gridMap="gridMap"
                        :columns="columns"
                        :dataStructure="dataStructure"
                        v-bind="$attrs"
                    >
                    </Spreadsheet1>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>

import SpreadsheetHeader from '@/components/views/SpreadsheetHeader.vue';

export default {
    name: 'PreviewSpreadsheet',
    components: {
        SpreadsheetHeader,
    },
    mixins: [
    ],
    props: {
        columns: {
            type: Array,
            required: true,
        },
        dataStructure: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
        };
    },
    computed: {
        gridMap() {
            return this.columns?.map((column, index) => {
                let width;
                if (index === 0) {
                    width = 200;
                } else if (column.width) {
                    width = column.width;
                } else {
                    width = 180;
                }
                return {
                    id: column.formattedId,
                    width,
                };
            });
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-preview-spreadsheet {

} */

</style>
