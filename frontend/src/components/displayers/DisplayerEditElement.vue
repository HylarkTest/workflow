<template>
    <div
        class="o-displayer-edit-element flex items-baseline"
        :class="listClasses"
    >
        <div
            v-if="isNumberedList"
            class="mr-2 font-bold bg-primary-100 text-primary-600 rounded-md py-0.5 px-1.5 text-xs"
        >
            {{ index + 1 }}
        </div>
        <div class="flex-1">
            <button
                v-if="showClearButton"
                v-t="'common.clear'"
                class="button--xs button-gray mb-2"
                type="button"
                @click="clearField"
            >
            </button>

            <DisplayerEditLabel
                v-if="isLabeled"
                :labelInfo="isLabeled"
                :labelKey="element && element.labelKey"
                :labelVal="element && element.label"
                class="mb-1 flex items-center"
                @update:labelVal="updateLabel($event, 'label')"
                @update:labelKey="updateLabel($event, 'labelKey')"
            >
            </DisplayerEditLabel>

            <component
                ref="element"
                :is="editComponent"
                :dataValue="getDataValue(element)"
                :dataInfo="dataInfo"
                :isModifiable="true"
                bgColor="gray"
                boxStyle="plain"
                v-bind="$attrs"
                :placeholder="placeholder"
                @update:dataValue="updateElementValue"
            >
            </component>
        </div>

        <div
            v-if="isList"
            class="ml-2 flex flex-col items-end"
        >
            <div class="mb-2">
                <CheckHolder
                    :modelValue="mainValue"
                    size="sm"
                    @update:modelValue="setMainValue"
                >
                    <span class="font-semibold">Main</span>
                </CheckHolder>
            </div>

            <div
                class="flex items-center"
            >
                <IconActionButton
                    v-if="showMoveButton"
                    class="drag-this cursor-move mr-2"
                    :buttonSpecs="buttonSpecs"
                    size="sm"
                >
                </IconActionButton>

                <DeleteButton
                    size="sm"
                    :buttonSpecsProp="{ bgColor: 'gray' }"
                    @click="removeFromList"
                >
                </DeleteButton>
            </div>
        </div>
    </div>
</template>

<script>

import DeleteButton from '@/components/buttons/DeleteButton.vue';
import IconActionButton from '@/components/buttons/IconActionButton.vue';

import interactsWithDisplayerContainers from '@/vue-mixins/displayers/interactsWithDisplayerContainers.js';

import { arrRemoveIndex } from '@/core/utils.js';
import { getEditComponent } from '@/core/display/displayerInstructions.js';

const buttonSpecs = {
    icon: 'fa-regular fa-arrows-up-down-left-right',
    bgColor: 'secondary',
};

export default {
    name: 'DisplayerEditElement',
    components: {
        DeleteButton,
        IconActionButton,
    },
    mixins: [
        interactsWithDisplayerContainers,
    ],
    props: {
        fullDataObj: {
            type: [String, Number, Array, Object, Boolean, null],
            required: true,
        },
        dataInfo: {
            type: [Object, null],
            default: null,
        },
        dataValues: {
            type: Array,
            required: true,
        },
        element: {
            type: [Object, null],
            default: null,
        },
        index: {
            type: Number,
            required: true,
        },
        isDraggable: Boolean,
    },
    emits: [
        'setRefs',
        'update:dataValue',
        'updateElementValue',
    ],
    data() {
        return {
            fieldRefs: {},
        };
    },
    computed: {
        editComponent() {
            return getEditComponent(this.dataInfo);
        },
        moreThanOneDataValues() {
            return this.dataValues.length > 1;
        },
        showMoveButton() {
            return this.moreThanOneDataValues && this.isDraggable;
        },
        fieldSubtype() {
            return this.dataInfo.info?.subType;
        },
        placeholder() {
            const noPlaceholderArr = ['LINE', 'PARAGRAPH'];
            if (noPlaceholderArr.includes(this.fieldSubtype)) {
                return '';
            }
            const camel = _.camelCase(this.fieldSubtype);
            const label = this.$t(`labels.${camel}`);
            return label;
        },
        mainValue() {
            return this.element?.main || false;
        },
        listClasses() {
            return this.isList && !this.isListCount ? 'odd:bg-primary-50 px-2 py-2 rounded-lg' : '';
        },
        showClearButton() {
            // Currently only condition is whether it's a non-list money or salary field
            const isMoneyOrSalaryField = this.fieldType === 'MONEY' || this.fieldType === 'SALARY';
            return isMoneyOrSalaryField && !this.isList;
        },
    },
    methods: {
        updateElementValue(event) {
            this.$emit('updateElementValue', event, this.index);
        },
        updateLabel(event, path) {
            if (this.isList) {
                this.$proxyEvent(event, this.fullDataObj, `listValue.${this.index}.${path}`, 'update:dataValue');
            } else {
                this.$proxyEvent(event, this.fullDataObj, path, 'update:dataValue');
            }
        },
        setMainValue() {
            const newList = _.cloneDeep(this.fullDataObj);
            newList.listValue.forEach((_, i) => {
                if (i === this.index) {
                    newList.listValue[i].main = !newList.listValue[i].main;
                } else {
                    newList.listValue[i].main = false;
                }
            });
            this.emitDataValue(newList);
        },
        clearField() {
            this.emitDataValue(null);
        },
        removeFromList() {
            const removed = arrRemoveIndex(this.dataValues, this.index);
            this.emitDataValue({ listValue: removed });
        },
        emitDataValue(newVal) {
            this.$emit('update:dataValue', newVal);
        },
        focusOnInput() {
            this.$refs.element
                .$refs.input // DisplayerEdit... component
                ?.$refs.input // Input wrapper
                ?.focus(); // Input component
        },
    },
    created() {
        this.buttonSpecs = buttonSpecs;
    },
};
</script>

<style scoped>
/* .o-displayer-edit-element {

} */
</style>
