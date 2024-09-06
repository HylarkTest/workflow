<template>
    <div
        class="c-displayer-checkbox-list text-xssm"
    >
        <div
            v-for="button in formattedOptions"
            :key="button.selectKey"
            class="mb-1 flex items-center"
        >
            <CheckHolder
                v-model="dataArr"
                :val="button"
                predicate="selectKey"
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
    name: 'DisplayerCheckboxList',
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
            typeKey: 'CHECKBOX_LIST',
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
        dataArr: {
            get() {
                return this.modelFieldValue || [];
            },
            set(val) {
                this.saveValue(val);
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

/*.c-displayer-checkbox-list {

} */

</style>
