<template>
    <div
        class="c-displayer-radio-list"
    >
        <div
            v-for="button in formattedOptions"
            :key="button.selectKey"
            class="mb-1 flex items-center"
        >
            <CheckHolder
                v-model="dataVal"
                :val="button.selectKey"
                type="radio"
                class="mr-2"
                size="sm"
            >
                <span>
                    {{ button.selectValue }}
                </span>
            </CheckHolder>
        </div>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import interactsWithDisplayersSelfEdit from '@/vue-mixins/displayers/interactsWithDisplayersSelfEdit.js';

export default {
    name: 'DisplayerRadioList',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
        interactsWithDisplayersSelfEdit,
    ],
    props: {

    },
    emits: [
        'saveField',
        'update:dataValue',
    ],
    data() {
        return {
            typeKey: 'RADIO_LIST',
        };
    },
    computed: {
        options() {
            return this.dataInfo.info?.options?.valueOptions;
        },
        formattedOptions() {
            return _(this.options).map((option, key) => {
                return {
                    selectKey: key,
                    selectValue: option,
                };
            }).value();
        },
        dataVal: {
            get() {
                return this.modelFieldValue?.selectKey;
            },
            set(val) {
                this.saveValue({
                    ...(this.modelFieldValue || {}),
                    selectKey: val,
                });
            },
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-radio-list {

} */

</style>
