<template>
    <div
        class="c-displayer-toggle-list text-xssm"
    >
        <div
            v-for="button in formattedOptions"
            :key="button.selectKey"
            class="mb-1 flex items-center"
        >
            <ToggleButton
                v-model="dataArr"
                :val="button"
                predicate="selectKey"
                class="mr-2"
                size="sm"
            >
            </ToggleButton>

            <span>
                {{ button.selectValue }}
            </span>
        </div>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import interactsWithDisplayersSelfEdit from '@/vue-mixins/displayers/interactsWithDisplayersSelfEdit.js';

export default {
    name: 'DisplayerToggleList',
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
            typeKey: 'TOGGLE_LIST',
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

/*.c-displayer-toggle-list {

} */

</style>
