<template>
    <div class="o-duration-boxes flex flex-wrap gap-2">
        <div
            v-for="line in lines"
            :key="line"
            class="flex items-center m-2"
        >
            <span class="text-xs mr-2">
                {{ $t(lineLabel(line)) }}
            </span>
            <div class="w-20">
                <InputBox
                    :modelValue="lineData(line)"
                    size="sm"
                    min="0"
                    type="number"
                    v-bind="$attrs"
                    placeholder=""
                    @update:modelValue="updateLineData($event, line)"
                >
                </InputBox>
            </div>
        </div>
    </div>
</template>

<script>

const lines = [
    'MONTHS',
    'WEEKS',
    'DAYS',
    'HOURS',
    'MINUTES',
];

export default {
    name: 'DurationBoxes',
    components: {

    },
    mixins: [
    ],
    props: {
        duration: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'update:duration',
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
        lineData(line) {
            const formatted = _.camelCase(line);
            const val = this.duration?.[formatted];
            return _.isNumber(val) ? val : null;
        },
        updateLineData(val, line) {
            this.$emit('update:duration', { val, line });
        },
    },
    created() {
        this.lines = lines;
    },
};
</script>

<style scoped>

/*.o-duration-boxes {

} */

</style>
