<template>
    <div
        class="c-displayer-edit-multi border-secondary-100 bg-cm-00 border-solid rounded-xl border-4 p-2"
    >
        <div
            v-for="field in subFields"
            :key="field.id"
            class="mb-6 last:mb-0"
        >
            <span class="text-xssm font-bold mb-1">
                {{ field.name }}
            </span>

            <DisplayerEditContainer
                v-bind="$attrs"
                :dataValue="getDataValue(field.id)"
                :editComponentProp="editComponent(field)"
                :dataInfo="field"
                @update:dataValue="updateDataValue($event, field.id)"
            >
            </DisplayerEditContainer>
        </div>
    </div>
</template>

<script>

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';
import { getEditComponent } from '@/core/display/displayerInstructions.js';

export default {
    name: 'DisplayerEditMulti',
    components: {

    },
    mixins: [
        interactsWithDisplayersEdit,
    ],
    props: {

    },
    emits: [
        'update:dataValue',
    ],
    data() {
        return {

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
    },
    methods: {
        getDataValue(id) {
            if (this.modifiableFieldValue && this.modifiableFieldValue[id]) {
                return this.modifiableFieldValue[id];
            }
            return null;
        },
        editComponent(field) {
            return getEditComponent(null, field.info.subType);
        },
        updateDataValue(event, fieldId) {
            this.$proxyEvent(event, this.modifiableFieldValue, fieldId, 'update:dataValue');
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-edit-multi {

} */

</style>
