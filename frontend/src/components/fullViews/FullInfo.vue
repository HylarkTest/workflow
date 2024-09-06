<template>
    <div class="o-full-info">
        <div
            v-if="!preview"
            class="mb-2 flex justify-end"
        >
            <button
                class="button--sm button-primary--light"
                type="button"
                @click="openModal"
            >
                Edit all fields
            </button>
        </div>

        <div>
            <div
                v-for="(section, index) in infoSource"
                :key="index"
                class="mb-8"
            >
                <h2
                    v-if="section.header"
                    class="header-display-section"
                >
                    {{ section.header }}
                </h2>

                <div>
                    <div
                        v-for="field in section.fields"
                        :key="field.formattedId"
                        class="mb-6 last:mb-0"
                    >
                        <DisplayerContainer
                            :dataInfo="field"
                            :dataValue="getDataValue(field)"
                            :mapping="mapping"
                            :showNecessaryLabels="true"
                            :item="item"
                            :isModifiable="!preview"
                            :showMock="preview"
                            :alwaysShowLabels="true"
                            sizeInstructions="w-1/4 min-w-100p"
                        >
                        </DisplayerContainer>
                    </div>
                </div>
            </div>
        </div>

        <EntityEditModal
            v-if="isModalOpen"
            :item="item"
            :page="page"
            @closeModal="closeModal"
        >
        </EntityEditModal>
    </div>
</template>

<script>

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import { getFullFieldsInfoDefault } from '@/core/display/fullViewFunctions.js';
import {
    itemDisplayFlatAndFormatted,
    getBasicFormattedData,
} from '@/core/display/theStandardizer.js';
import {
    getMockOfFieldsArr,
} from '@/core/display/displayerInstructions.js';

export default {
    name: 'FullInfo',
    components: {

    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        mapping: {
            type: Object,
            required: true,
        },
        item: {
            type: [Object, null],
            default: null,
        },
        page: {
            type: [Object, null],
            default: null,
        },

        preview: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        itemDisplay() {
            const itemDisplay = this.page?.design?.itemDisplay;
            return itemDisplay && itemDisplayFlatAndFormatted(itemDisplay, this.fields, this.itemData);
        },
        itemData() {
            return this.item?.data;
        },
        itemDisplayPopulated() {
            return this.itemDisplay;
        },
        infoSource() {
            return this.itemDisplay || this.defaultInfo;
        },
        defaultInfo() {
            const arg = this.preview ? null : this.itemData;
            return getFullFieldsInfoDefault(this.mapping, arg);
        },
        fields() {
            return this.mapping.fields;
        },
        formattedFields() {
            return getBasicFormattedData(this.fields, 'FIELDS');
        },
        mockData() {
            return getMockOfFieldsArr(this.formattedFields);
        },
        shownData() {
            return this.itemData || this.mockData;
        },
    },
    methods: {
        getDataValue(field) {
            return this.shownData[field.formattedId];
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-full-info {

} */

</style>
