<template>
    <div
        v-blur="selectStoreVal"
        class="c-time-options"
    >
        <ButtonEl
            ref="time"
            class="c-time-options__unit centered"
            :class="buttonClasses"
            type="button"
            @click="buttonClick"
            @keyup.enter="buttonClick"
            @keyup.space="buttonClick"
        >
            <InputSized
                ref="input"
                :key="reloadInput"
                v-model="timeVal"
                :placeholder="placeholder"
                alertClass="w-48"
                :alertPosition="{ bottom: '10px', right: 0 }"
                :class="inputClasses"
                keydownValidationType="numberOnly"
                maxlength="2"
            >
                {{ storeVal || timeVal || placeholder }}
            </InputSized>
        </ButtonEl>

        <PopupBasic
            v-if="showPopup"
            containerClass="p-1"
            maxHeightProp="11.25rem"
            widthProp="3.375rem"
            :activator="$refs.time"
            :alignCenter="true"
            nudgeDownProp="0.25rem"
        >
            <button
                v-for="option in options"
                :key="option"
                :ref="option"
                class="c-time-options__option"
                :class="optionsClasses(option)"
                type="button"
                @click="selectTime(option)"
                @keyup.enter="selectTime(highlightedVal)"
                @keyup.space="selectTime(highlightedVal)"
            >
                {{ option }}
            </button>
        </PopupBasic>
    </div>
</template>

<script>

// import InputField from '@/components/inputs/InputField.vue';
import InputSized from '@/components/inputs/InputSized.vue';
import hasArrowControls from '@/vue-mixins/hasArrowControls.js';

export default {
    name: 'TimeOptions',
    components: {
        InputSized,
    },
    mixins: [
        hasArrowControls,
    ],
    props: {
        optionType: {
            type: String,
            default: 'hours',
            validator(val) {
                return ['hours', 'minutes'].includes(val);
            },
        },
        timeOptions: {
            type: Object,
            required: true,
        },
        is24Hours: Boolean,
        modelValue: {
            type: [String, null],
            default: null,
        },
    },
    emits: [
        'update:modelValue',
    ],
    data() {
        return {
            showPopup: false,
            highlightedVal: '',
            startVal: this.modelValue,
            storeVal: null,
            reloadInput: 0,
        };
    },
    computed: {
        // input control
        timeVal: {
            get() {
                if (this.modelValue && this.isHours) {
                    return this.parseTime(this.modelValue);
                }
                return this.modelValue;
            },
            set(time) {
                if (this.isOptionsValue(time)) {
                    this.highlightedVal = this.isHours ? time : this.padTime(time);
                } else {
                    this.highlightedVal = '';
                }
                this.storeVal = time;
            },
        },
        validTypedVal() {
            return this.isOptionsValue(this.storeVal);
        },

        // options
        hoursRange() {
            return this.is24Hours ? [0, 23] : [1, 12];
        },
        minutesRange() {
            return [0, 59];
        },
        range() {
            return this[`${this.optionType}Range`];
        },
        hoursOptions() {
            const range = _.range(this.hoursRange[0], this.hoursRange[1] + 1);
            return range.map((hour) => _.toString(hour));
        },
        minutesOptions() {
            const range = _.range(this.minutesRange[0], this.minutesRange[1] + 1, this.timeOptions.minuteInterval);
            return range.map((minute) => this.padTime(minute));
        },
        options() {
            return this[`${this.optionType}Options`];
        },
        isHours() {
            return this.optionType === 'hours';
        },
        placeholder() {
            return this.isHours ? 'H' : 'm';
        },
        selectedOption() {
            return this.options.find((option) => this.isOptionSelected(option));
        },

        buttonClasses() {
            return { 'c-time-options__unit--highlight': this.showPopup };
        },

        inputClasses() {
            if (this.optionType === 'hours') {
                return 'text-right w-5';
            }
            return 'w-5';
        },
    },
    methods: {
        // input control
        parseTime(time) {
            const stringTime = _.chain(time).parseInt(10).toString();
            return this.padTime(stringTime);
        },
        padTime(time) {
            return _.padStart(time, 2, '0');
        },
        selectStoreVal() {
            if (!_.isNull(this.storeVal)) {
                this.selectTime(_.clamp(this.storeVal, ...this.range));
            }
            this.closePopup();
        },
        selectTime(option) {
            this.$emit('update:modelValue', this.parseTime(option));
            this.closePopup();
        },

        // options
        isTimeAnOption(time, option) {
            return this.parseTime(time) === this.parseTime(option);
        },
        isOptionsValue(value) {
            const matchingValue = this.options.find((option) => {
                return this.isTimeAnOption(value, option);
            });
            return matchingValue;
        },
        optionsClasses(option) {
            return [
                { 'c-time-options__option--selected': this.selectedOption === option },
                { 'bg-primary-100': this.highlightedVal === option },
            ];
        },
        isOptionSelected(option) {
            if (_.isNull(this.storeVal)) {
                return option === this.timeVal;
            }
            return this.isTimeAnOption(this.storeVal, option);
        },

        // popup
        buttonClick() {
            if (this.showPopup) {
                if (this.highlightedVal) {
                    this.selectTime(this.highlightedVal);
                } else {
                    this.selectStoreVal();
                }
            } else {
                this.openPopup();
            }
        },
        async openPopup() {
            this.showPopup = true;
            this.highlightedVal = this.timeVal;
            await this.$nextTick();
            this.$refs.input.$refs.input.focus();
            this.$refs.input.$refs.input.select();
        },
        closePopup() {
            this.showPopup = false;
            this.storeVal = null;
            this.reloadInput += 1;
        },

        // arrow keys
        advanceSelectedOption(increment = 1) {
            const optionIndex = this.options.indexOf(this.highlightedVal);
            let nextIndex = optionIndex + increment;
            if (nextIndex >= this.options.length) {
                nextIndex = 0;
            } else if (nextIndex < 0) {
                nextIndex = this.options.length - 1;
            }
            this.highlightedVal = _.toString(this.options[nextIndex]);
        },
        onDownArrow() {
            this.advanceSelectedOption();
        },
        onUpArrow() {
            this.advanceSelectedOption(-1);
        },
    },
    watch: {
        modelValue(val) {
            if (val) {
                this.storeVal = null;
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-time-options {
    &__unit {

        @apply
            rounded-lg
        ;

        &--highlight {
            @apply
                shadow-center-lg
                shadow-primary-300/50
            ;
        }
    }

    &__option {
        padding: 1px 3px;

        @apply
            block
            rounded-lg
            text-center
            text-smbase
            w-full
        ;

        &:hover {
            @apply
                bg-primary-100
            ;
        }

        &--selected {
            @apply
                bg-primary-600
                text-cm-00
            ;

            &:hover {
                @apply
                    bg-primary-600
                ;
            }

        }
    }
}

</style>
