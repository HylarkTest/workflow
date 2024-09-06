<template>
    <div
        v-blur="closeDatePopup"
        class="c-date-input"
    >
        <div
            ref="dateDisplay"
            class="c-date-input__main"
            @click="openDatePopup"
        >
            <div class="mr-3 text-cm-400 text-xs">
                <i
                    class="fal fa-calendar"
                >
                </i>
            </div>
            <div class="flex">
                <div
                    v-for="input in dateInputs"
                    :key="input.val"
                    class="c-date-input__date"
                    :class="{ 'text-peach-600': invalidInput(input) }"
                >
                    <InputSized
                        :ref="setRefs(input.val)"
                        :modelValue="getDateObjectKey(input)"
                        :maxlength="maxLength(input)"
                        :placeholder="input.placeholder"
                        keydownValidationType="numberOnly"
                        @update:modelValue="updateDate($event, input.val)"
                        @click="highlightInput(input.val)"
                    >
                        {{ getDateObjectKey(input) || input.placeholder }}
                    </InputSized>

                    <span
                        class="c-date-input__dash"
                    >
                        /
                    </span>
                </div>
            </div>

            <ClearButton
                v-if="dateTime && showClear"
                positioningClass="ml-2"
                @click.stop="emitDate(null)"
            >
            </ClearButton>
        </div>

        <DatePickerPopup
            v-if="showDatePopup"
            :activator="$refs.dateDisplay"
            :dateTime="dateTime"
            :timeOptionsProp="timeOptionsProp"
            :viewedMonth="month"
            :viewedYear="year"
            containerClass="p-2"
            :dateNullable="dateNullable"
            @update:dateTime="emitDate"
        >
        </DatePickerPopup>
    </div>
</template>

<script>
import InputSized from '@/components/inputs/InputSized.vue';
import DatePickerPopup from '@/components/datePicker/DatePickerPopup.vue';
import ClearButton from '@/components/buttons/ClearButton.vue';

import useDateInput from '@/composables/useDateInput.js';

import {
    dateFormat,
} from '@/core/repositories/preferencesRepository.js';

const dateInputOptions = {
    D: {
        max: 2,
        placeholder: 'DD',
        val: 'D',
    },
    M: {
        max: 2,
        placeholder: 'MM',
        val: 'M',
    },
    Y: {
        max: 4,
        placeholder: 'YYYY',
        val: 'Y',
    },
};

export default {
    name: 'DateInput',
    components: {
        // InputField,
        InputSized,
        DatePickerPopup,
        ClearButton,
    },
    mixins: [
    ],
    props: {
        dateTime: {
            type: [String, null],
            default: null,
        },
        timeOptionsProp: {
            type: Object,
            default: () => ({}),
        },
        noTimezone: Boolean,
        isMicrosoftItem: Boolean,
        dateNullable: Boolean,
        showClear: Boolean,
    },
    emits: [
        'update:dateTime',
    ],
    setup(props, context) {
        const {
            modelValue,
            updateModelValue,
        } = useDateInput(props, context);

        return {
            modelValue,
            updateModelValue,
        };
    },
    data() {
        return {
            dateFormat,
            showDatePopup: false,
            inputRefs: {},
            tempYear: null,
            tempMonth: null,
            tempDay: null,
        };
    },
    computed: {
        splitOrder() {
            return _.split(this.dateFormat, '');
        },
        dateInputs() {
            return this.splitOrder.map((val) => {
                return dateInputOptions[val];
            });
        },
        splitFullDate() {
            return _.split(this.modelValue, ' ')[0];
        },
        splitDate() {
            return _.split(this.splitFullDate, '-');
        },
        year() {
            return _.isNull(this.tempYear) ? this.splitDate[0] : this.tempYear;
        },
        month() {
            return _.isNull(this.tempMonth) ? this.splitDate[1] : this.tempMonth;
        },
        day() {
            return _.isNull(this.tempDay) ? this.splitDate[2] : this.tempDay;
        },

        dateObject() {
            return {
                M: this.month,
                D: this.day,
                Y: this.year,
            };
        },

        // dateObjectPadded() {
        //     return {
        //         M: this.monthPadded,
        //         D: this.dayPadded,
        //         Y: this.year,
        //     };
        // },
        monthPadded() {
            if (this.month.length === 1) {
                return _.padStart(this.month, 2, '0');
            }
            return this.month;
        },
        dayPadded() {
            if (this.day.length === 1) {
                return _.padStart(this.day, 2, '0');
            }
            return this.day;
        },
    },
    methods: {
        getDateObjectKey(input) {
            return this.dateObject[input.val];
        },
        isInvalidPart(val, type) {
            if (type === 'Y') {
                return this.isInvalidYear(val);
            }
            if (type === 'M') {
                return this.isInvalidMonth(val);
            }
            return this.isInvalidDay(val);
        },
        isInvalidYear(year) {
            return !year || year.length !== 4;
        },
        isInvalidMonth(month) {
            return !month || month.length !== 2 || month < 1 || month > 12;
        },
        isInvalidDay(day) {
            if (!day || day.length !== 2 || day < 1) {
                return true;
            }
            if (this.isInvalidMonth(this.month)) {
                return day > 31;
            }
            // If the year is invalid then we just put in a leap year so 29 isn't automatically
            // invalid.
            const maxDays = this.$dayjs().month(this.month - 1).year(
                this.tempYear ? 2000 : this.year
            ).daysInMonth();
            return day > maxDays;
        },
        invalidInput(input) {
            return this.dateObject[input.val] && this.isInvalidPart(this.dateObject[input.val], input.val);
        },
        setRefs(val) {
            return (el) => {
                this.inputRefs[val] = el;
            };
        },
        openDatePopup() {
            this.showDatePopup = true;
        },
        closeDatePopup() {
            this.showDatePopup = false;
        },
        updateDate(val, type) {
            const obj = _.clone(this.dateObject);
            const paddedVal = type === 'Y' ? val : _.padStart(_.trimStart(val, '0'), 2, '0');

            if (type === 'Y') {
                this.tempYear = paddedVal;
                if (this.isInvalidYear(paddedVal)) {
                    return;
                }
            }
            if (type === 'M') {
                this.tempMonth = paddedVal;
                if (this.isInvalidMonth(paddedVal)) {
                    return;
                }
            }
            if (type === 'D') {
                this.tempDay = paddedVal;
                if (this.isInvalidDay(paddedVal)) {
                    return;
                }
            }
            obj[type] = paddedVal;
            if (this.isInvalidYear(obj.Y) || this.isInvalidMonth(obj.M) || this.isInvalidDay(obj.D)) {
                return;
            }
            const newVal = `${obj.Y}-${obj.M}-${obj.D}`;
            this.updateModelValue(newVal);
        },
        maxLength(input) {
            return _.get(this.dateObject, [input.val, 0]) === '0'
                ? input.max + 1
                : input.max;
        },
        async highlightInput(ref) {
            await this.$nextTick();
            this.inputRefs[ref].$refs?.input?.focus();
            this.inputRefs[ref].$refs?.input?.select();
        },
        emitDate(dateTime) {
            this.$emit('update:dateTime', dateTime);
            this.closeDatePopup();
        },
    },
    watch: {
        dateTime() {
            this.tempYear = null;
            this.tempMonth = null;
            this.tempDay = null;
        },
    },
    created() {
    },
};
</script>

<style scoped>

.c-date-input {
    min-width: 130px;

    &__main {
        @apply
            bg-cm-100
            flex
            items-center
            justify-between
            px-2
            py-1
            rounded-lg
        ;
    }

    &__date {
        @apply
            flex
            items-center
        ;

        &:last-child {
            .c-date-input__dash {
                @apply
                    hidden
                ;
            }
        }
    }

    &__dash {
        @apply
            mx-0.5
            text-cm-400
        ;
    }

    &__box {
        @apply
            bg-cm-100
            px-1
            py-0.5
            rounded-lg
        ;

        /*&--2 {
            width: 32px;
        }

        &--4 {
            width:  46px;
        }*/
    }
}

</style>
