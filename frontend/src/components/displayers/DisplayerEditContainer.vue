<template>
    <div
        class="c-displayer-edit-container relative"
    >
        <transition name="t-fade">
            <AlertTooltip
                v-if="error"
                :alertPosition="{ top: '-20px', right: '0' }"
            >
                {{ error }}
            </AlertTooltip>
        </transition>

        <div>
            <Draggable
                :modelValue="dataValues"
                :itemKey="(dataValue) => dataValues.indexOf(dataValue)"
                :disabled="!isDraggable"
                handle=".drag-this"
                @update:modelValue="updateOrder"
            >
                <template #item="{ element, index }">
                    <DisplayerEditElement
                        :ref="setRefs(element)"
                        :dataInfo="dataInfo"
                        :fullDataObj="dataValue"
                        :dataValues="dataValues"
                        :element="element"
                        :index="index"
                        :isDraggable="isDraggable"
                        v-bind="$attrs"
                        @updateElementValue="updateDataValue"
                        @update:dataValue="emitDataValue"
                    >
                    </DisplayerEditElement>
                </template>
            </Draggable>
        </div>

        <button
            v-if="isList"
            v-t="'common.add'"
            class="button-rounded--sm button-primary--light mt-2"
            type="button"
            @click="addAnotherVal"
        >
        </button>
    </div>
</template>

<script>

import Draggable from 'vuedraggable';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';
import DisplayerEditElement from '@/components/displayers/DisplayerEditElement.vue';

import interactsWithDisplayerContainers from '@/vue-mixins/displayers/interactsWithDisplayerContainers.js';

export default {
    name: 'DisplayerEditContainer',
    components: {
        AlertTooltip,
        DisplayerEditElement,
        Draggable,
    },
    mixins: [
        interactsWithDisplayerContainers,
    ],
    props: {
        dataValue: {
            type: [String, Number, Array, Object, Boolean, null],
            required: true,
        },
        dataInfo: {
            type: [Object, null],
            default: null,
        },
        error: {
            type: [String, null],
            default: null,
        },
    },
    emits: [
        'update:dataValue',
    ],
    data() {
        return {
            fieldRefs: {},
        };
    },
    computed: {
        dataValues() {
            if (this.isList) {
                // Blank if no values to avoid confusion around what data is there or not
                const withTempId = this.dataValue?.listValue
                    ? this.addTempIds(this.dataValue.listValue)
                    : [];
                return withTempId;
            }
            const withTempId = this.dataValue ? this.addTempIds([this.dataValue]) : [null];
            return withTempId;
        },
        isDraggable() {
            return this.isList;
        },
        fieldName() {
            return this.dataInfo.name;
        },
    },
    methods: {
        updateOrder(event) {
            this.$proxyEvent(event, this.dataValue, 'listValue', 'update:dataValue');
        },
        updateDataValue(event, index) {
            const path = [];
            if (this.isList) {
                path.push('listValue');
                path.push(index);
            }
            path.push('fieldValue');
            const joined = path.join('.');
            this.$proxyEvent(event, this.dataValue, joined, 'update:dataValue');
        },
        emitDataValue(event) {
            this.$emit('update:dataValue', event);
        },
        addAnotherVal() {
            let valueLength = this.dataValues?.length;
            const nextIndex = valueLength || 0;
            this.updateDataValue(this.defaultItemValue, nextIndex);

            this.$nextTick(() => {
                // This is just a little convenience improvement to focus in
                // when adding a list item. If it does not work for some
                // data types, add the ref in the DisplayerEdit[Component].
                // It may not work 100% of the time, but if no errors, it's
                // still more convenient than if not there.
                // Feel free to improve.

                // Re-trigger value length after the event
                valueLength = this.dataValues?.length;
                const lastDataValue = this.dataValues[valueLength - 1];
                if (lastDataValue) {
                    this.fieldRefs[lastDataValue.tempId].focusOnInput(); // DisplayerEditElement component
                }
            });
        },
        setRefs(editElement) {
            return (el) => {
                if (editElement) {
                    this.fieldRefs[editElement.tempId] = el;
                }
            };
        },
        addTempIds(vals) {
            return vals.map((val) => {
                return {
                    ...val,
                    tempId: Date.now(),
                };
            });
        },
    },
    created() {
    },
};
</script>

<style scoped>

/*.c-displayer-edit-container {

} */

</style>
