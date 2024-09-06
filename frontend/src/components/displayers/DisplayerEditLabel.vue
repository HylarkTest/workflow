<template>
    <div class="c-displayer-edit-label">
        <span class="label-data--light mr-2">
            Label:
        </span>
        <div
            v-if="labelInfo.freeText"
            class="w-32"
        >
            <InputBox
                :modelValue="labelVal"
                size="sm"
                bgColor="gray"
                placeholder="Label"
                @update:modelValue="$emit('update:labelVal', $event)"
            >
            </InputBox>
        </div>
        <div
            v-else
            class="w-32"
        >
            <DropdownBox
                :modelValue="labelKey"
                class="w-full"
                :options="labels"
                bgColor="gray"
                displayRule="selectValue"
                property="selectKey"
                size="sm"
                :showClear="true"
                placeholder="Label"
                @update:modelValue="$emit('update:labelKey', $event)"
            >
            </DropdownBox>
        </div>
    </div>
</template>

<script>

export default {
    name: 'DisplayerEditLabel',
    components: {

    },
    mixins: [
    ],
    props: {
        labelInfo: {
            type: Object,
            required: true,
        },
        labelKey: {
            type: String,
            default: '',
        },
        labelVal: {
            type: String,
            default: '',
        },
    },
    emits: [
        'update:labelKey',
        'update:labelVal',
    ],
    data() {
        return {

        };
    },
    computed: {
        labelOptions() {
            return this.labelInfo.labels;
        },
        labels() {
            return _(this.labelOptions).map((option, key) => {
                return {
                    selectKey: key,
                    selectValue: option,
                };
            }).value();
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-edit-label {

} */

</style>
