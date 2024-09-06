<template>
    <div class="o-preferences-money">
        <SettingsHeaderLine>
            <template
                #header
            >
                Money amount format
            </template>

            <div class="">
                <div
                    v-for="option in moneyOptions"
                    :key="option.val"
                    class="mr-8 my-2"
                >
                    <CheckHolder
                        :modelValue="moneyFormat"
                        :val="option.format"
                        :predicate="({ separator, decimal }) => `1${separator}000${decimal}00`"
                        type="radio"
                        @update:modelValue="selectMoneyFormat(option.format)"
                    >
                        {{ option.val }}
                    </CheckHolder>
                </div>
            </div>

        </SettingsHeaderLine>
    </div>
</template>

<script>

const moneyOptions = [
    {
        val: '1 000.00',
        format: {
            separator: ' ',
            decimal: '.',
        },
    },
    {
        val: '1000.00',
        format: {
            separator: '',
            decimal: '.',
        },
    },
    {
        val: '1,000.00',
        format: {
            separator: ',',
            decimal: '.',
        },
    },
    {
        val: '1 000,00',
        format: {
            separator: ' ',
            decimal: ',',
        },
    },
    {
        val: '1000,00',
        format: {
            separator: '',
            decimal: ',',
        },
    },
    {
        val: '1.000,00',
        format: {
            separator: '.',
            decimal: ',',
        },
    },
];

export default {
    name: 'PreferencesMoney',
    components: {
    },
    mixins: [
    ],
    props: {
        moneyFormat: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:moneyFormat',
    ],
    data() {
        return {
        };
    },
    computed: {
        selectedVal() {
            const obj = this.moneyOptions.find((option) => {
                return option.format.separator === this.moneyFormat.separator
                    && option.format.decimal === this.moneyFormat.decimal;
            });
            return obj.val;
        },
    },
    methods: {
        selectMoneyFormat(val) {
            this.$emit('update:moneyFormat', val);
        },
    },
    created() {
        this.moneyOptions = moneyOptions;
    },
};
</script>

<!-- <style scoped>
.o-preferences-money {

}
</style> -->
