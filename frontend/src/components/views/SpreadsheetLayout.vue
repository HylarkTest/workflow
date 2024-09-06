<template>
    <div
        ref="container"
        class="c-spreadsheet-layout relative w-fit max-w-full"
    >
        <!-- Teleporting element in here -->

        <div
            ref="spreadsheet"
            class="overflow-x-auto relative"
        >

            <!--  Teleporting element in here -->

            <table class="table-reset">
                <SpreadsheetHeader
                    v-model:columns="columns"
                    :isDraggable="isDraggable"
                    :gridMap="gridMap"
                >
                </SpreadsheetHeader>

                <tbody v-if="gridMap">
                    <Spreadsheet1
                        v-for="item in items"
                        :key="item.id"
                        :item="item"
                        :gridMap="gridMap"
                        :columns="columns"
                        :page="page"
                        :dataStructure="validData"
                        :dataValueObject="item"
                        :mapping="mapping"
                        :isSummaryView="true"
                        :isReadOnly="isReadOnly"
                    >
                        <template
                            #cell="scope"
                        >
                            <slot
                                name="cell"
                                v-bind="scope"
                            >
                            </slot>
                        </template>
                    </Spreadsheet1>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>

import SpreadsheetHeader from '@/components/views/SpreadsheetHeader.vue';

import interactsWithViewsLayouts from '@/vue-mixins/views/interactsWithViewsLayouts.js';

import { updatePageView } from '@/core/repositories/pageRepository.js';

const defaultWidth = 200;

export default {
    name: 'SpreadsheetLayout',
    components: {
        SpreadsheetHeader,
    },
    mixins: [
        interactsWithViewsLayouts,
    ],
    props: {
        isDraggable: Boolean,
        hasWidthToFit: Boolean,
    },
    data() {
        return {
            gridMap: null,
            viewType: 'SPREADSHEET',
        };
    },
    computed: {
        columns: {
            get() {
                return this.validData || [];
            },
            set(columns) {
                const viewData = this.view || this.currentView;
                updatePageView(this.$apolloForm({
                    ...viewData,
                    visibleData: columns,
                }), this.page);
            },
        },
    },
    methods: {
        getDragColumnMap() {
            return _(this.columns).map((column, index) => {
                let width;
                if (column.width) {
                    width = column.width;
                } else if (index === 0) {
                    width = 300;
                } else if (this.hasWidthToFit) {
                    width = {
                        minWidth: 200,
                        maxWidth: 350,
                    };
                } else {
                    width = defaultWidth;
                }
                if (!column?.formattedId) {
                    return null;
                }
                return {
                    id: column.formattedId,
                    width,
                };
            }).compact().value();
        },
    },
    watch: {
        columns: {
            immediate: true,
            handler() {
                this.gridMap = this.getDragColumnMap();
            },
        },
    },
    created() {

    },
};
</script>

<style>

.c-spreadsheet-layout {
    @apply
        bg-cm-00
        rounded-xl
    ;

    &__row {
        &:nth-child(odd) {
            @apply
                bg-cm-00
            ;

            .c-spreadsheet-layout__cell {
                @apply
                    bg-cm-00
                ;
            }
        }

        &:nth-child(even) {
            @apply
                bg-primary-50
            ;

            .c-spreadsheet-layout__cell {
                @apply
                    bg-primary-50
                ;
            }
        }
    }
}

</style>
