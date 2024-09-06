<template>
    <div class="c-displayer-edit-address">

        <div
            v-for="line in lines"
            :key="line"
            class="flex items-center mb-1"
        >
            <span class="text-xs mr-2 w-32">
                {{ $t(lineLabel(line)) }}
            </span>
            <div class="flex-1">
                <InputBox
                    :modelValue="lineData(line)"
                    size="sm"
                    v-bind="$attrs"
                    placeholder=""
                    @update:modelValue="updateDataValue($event, line)"
                >
                </InputBox>
            </div>
        </div>
    </div>
</template>

<script>

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';

const lines = [
    'LINE1',
    'LINE2',
    'CITY',
    'STATE',
    'COUNTRY',
    'POSTCODE',
];

export default {
    name: 'DisplayerEditAddress',
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

    },
    methods: {
        lineLabel(line) {
            return `labels.${_.camelCase(line)}`;
        },
        updateDataValue(event, line) {
            const formatted = _.camelCase(line);
            this.$proxyEvent(event, this.modifiableFieldValue || {}, formatted, 'update:dataValue');
        },
        lineData(line) {
            const formatted = _.camelCase(line);
            if (this.modifiableFieldValue && this.modifiableFieldValue[formatted]) {
                return this.modifiableFieldValue[formatted];
            }
            return '';
        },
    },
    created() {
        this.lines = lines;
    },
};
</script>

<style scoped>

/*.c-displayer-edit-address {

} */

</style>
