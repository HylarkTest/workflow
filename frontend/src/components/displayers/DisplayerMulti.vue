<template>
    <div
        class="c-displayer-multi border-secondary-100 bg-cm-00 border-solid rounded-xl border-4 p-2"
        :class="displayClasses"
    >
        <div
            v-for="field in subFields"
            :key="field.id"
            class="mb-6 last:mb-0 flex flex-col"
        >
            <h4 class="text-xssm font-bold mb-1">
                {{ field.name }}
            </h4>

            <DisplayerContainer
                v-bind="$attrs"
                :dataInfo="field"
                :dataValue="getDataValue(field)"
                :path="dataInfo.formattedId"
                :isModifiable="isModifiable"
                :prefix="multiPrefix"
                :isMultiChild="true"
                :item="item"
                :showMock="showMock"
                :parentIndex="index"
                :mapping="mapping"
            >
            </DisplayerContainer>
        </div>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

import { gimmeThePathToGetTheValue } from '@/core/display/fullViewFunctions.js';

export default {
    name: 'DisplayerMulti',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    emits: [
        'saveField',
    ],
    data() {
        return {
            typeKey: 'MULTI',
        };
    },
    computed: {
        allSubfields() {
            return this.dataInfo.info?.options?.subFields
                || [];
        },
        subFields() {
            return this.allSubfields.filter((field) => {
                return !field.displayOption;
            });
        },
        multiPrefix() {
            return gimmeThePathToGetTheValue(this.dataInfo, this.index);
        },
    },
    methods: {
        getDataValue(field) {
            if (field && this.dataValue) {
                return this.dataValue.fieldValue?.[field.id];
            }
            return '';
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-multi {

} */

</style>
