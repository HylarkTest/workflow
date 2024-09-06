<template>
    <div class="c-displayer-edit-location">
        <div class="mb-3">
            <LocationPicker
                :location="dataLocation"
                :levels="levels"
                v-bind="$attrs"
                placeholder=""
                :showClear="!isMulti"
                @update:location="updateDataValue"
            >
            </LocationPicker>
        </div>

        <div
            v-if="isMulti"
            class="list-value-parent"
        >
            <ValueBasic
                v-for="val in modifiableFieldValue"
                :key="val.id"
                @removeValue="removeValue(val)"
            >
                {{ val.name }}
            </ValueBasic>
        </div>
    </div>
</template>

<script>

import LocationPicker from '@/components/pickers/LocationPicker.vue';

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';
import interactsWithMultiDisplayers from '@/vue-mixins/displayers/interactsWithMultiDisplayers.js';

export default {
    name: 'DisplayerEditLocation',
    components: {
        LocationPicker,
    },
    mixins: [
        interactsWithDisplayersEdit,
        interactsWithMultiDisplayers,
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
        dataLocation() {
            if (this.isMulti) {
                return this.modifiableFieldValue || [];
            }
            return this.modifiableFieldValue;
        },
        levels() {
            return this.dataInfo.info?.options?.levels;
        },
        fieldValue() {
            return this.modifiableFieldValue;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-edit-location {

} */

</style>
