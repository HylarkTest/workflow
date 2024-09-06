<template>
    <label
        class="c-check-button"
        :class="[checkButtonClass, sizeClass, colorClass]"
    >
        <input
            ref="button"
            role="button"
            class="c-check-button__input"
            :type="type"
            :value="predicateFn(val)"
            :checked="isChecked"
            v-bind="$attrs"
            @change="setValue"
            @click="uncheckIfChecked"
            @keyup.enter="uncheckIfChecked"
        />
        <div
            class="c-check-button__indicator"
            :class="[indicatorType, indicatorClass]"
        >
        </div>
        <AlertTooltip
            v-if="errorMessage"
            :alertPosition="{ bottom: '40px', left: '100px', minWidth: '200px' }"
        >
            {{ errorMessage }}
        </AlertTooltip>
    </label>
</template>

<script>
import formWrapperChild from '@/vue-mixins/formWrapperChild.js';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';

export default {

    name: 'CheckButton',
    components: {
        AlertTooltip,
    },
    mixins: [
        formWrapperChild,
    ],
    props: {
        /* eslint-disable vue/require-prop-types */
        val: {
            default: undefined,
        },
        type: {
            type: String,
            default: 'checkbox',
        },
        checkButtonClass: {
            default: '',
            type: String,
        },
        indicatorClass: {
            type: String,
            default: '',
        },
        predicate: {
            type: [String, Function, null],
            default: () => _.identity,
        },
        disabled: Boolean,
        size: {
            type: String,
            default: 'base',
            validator: (value) => ['sm', 'base'].includes(value),
        },
        canRadioClear: Boolean,
        colorName: {
            type: String,
            default: 'primary',
            validator(val) {
                return ['primary', 'secondary'].includes(val);
            },
        },
    },
    data() {
        return {

        };
    },
    computed: {
        indicatorType() {
            return this.type === 'checkbox'
                ? 'c-check-button__indicator--checkbox'
                : 'c-check-button__indicator--radio';
        },
        isChecked() {
            if (this.type === 'radio') {
                return !!this.formValue && this.compare(this.val, this.formValue);
            }
            if (_.isBoolean(this.formValue)) {
                return this.formValue;
            }
            return (this.compare(this.val, this.formValue))
                || (_.isArray(this.formValue)
                    && (this.formValue.some((value) => this.compare(this.val, value))))
                || (_.isPlainObject(this.formValue) && this.formValue[this.val]);
        },
        sizeClass() {
            return `c-check-button--${this.size}`;
        },
        colorClass() {
            return `c-check-button--${this.colorName}`;
        },
    },
    methods: {
        compare(val, value) {
            const valCompare = this.predicateFn(val);
            const valueCompare = this.predicateFn(value);
            return valCompare === valueCompare;
        },
        predicateFn(value) {
            if (_.isString(this.predicate)) {
                return value[this.predicate];
            }
            return this.predicate(value);
        },
        setValue() {
            if (this.type === 'checkbox' && this.val && this.formValue) {
                if (Array.isArray(this.formValue)) {
                    const index = this.predicate !== _.identity
                        ? _.findIndex(this.formValue, (value) => this.compare(value, this.val))
                        : this.formValue.findIndex((item) => (item instanceof Proxy ? item.target : item) === this.val);
                    if (~index) {
                        return this.emitInput([
                            ...this.formValue.slice(0, index),
                            ...this.formValue.slice(index + 1),
                        ]);
                    }
                    return this.emitInput([
                        ...this.formValue,
                        this.val,
                    ]);
                }
                return this.emitInput(
                    _.chain(this.formValue).clone().set(this.val, this.$refs.button.checked).value()
                );
            }
            return this.emitInput(
                typeof this.val !== 'undefined'
                    ? this.val
                    : this.$refs.button.checked
            );
        },
        uncheckIfChecked() {
            if (this.canRadioClear && this.type === 'radio' && this.isChecked) {
                this.$refs.button.checked = false;
                this.setValue();
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>
.c-check-button {
    cursor: pointer;

    @apply
        relative
    ;

    &:hover .c-check-button__indicator {
        @apply
            border-cm-700
        ;
    }

    &__input {
        opacity: 0;
        position: absolute;
        z-index: 0;

        &:checked ~ .c-check-button__indicator--radio {

            @apply
                border-primary-600
            ;

            &::after {
                transform: scale(1);
            }
        }

        &:checked ~ .c-check-button__indicator--checkbox {

            @apply
                bg-primary-600
                border-primary-600
            ;

            &::after {
                opacity: 1;
            }
        }

        &:focus ~ .c-check-button__indicator {

            @apply
                border-primary-600
                shadow-center-dark
            ;
        }
    }

    &--secondary {
        .c-check-button__input {
            &:checked ~ .c-check-button__indicator--radio {

                @apply
                    border-secondary-600
                ;
            }

            &:checked ~ .c-check-button__indicator--checkbox {

                @apply
                    bg-secondary-600
                    border-secondary-600
                ;
            }

            &:focus ~ .c-check-button__indicator {

                @apply
                    border-secondary-600
                ;
            }
        }
    }

    &__indicator {
        border-style: solid;
        border-width: 1px;
        height: 20px;
        left: 0;
        position: relative;
        top: 0;
        width: 20px;

        @apply
            bg-cm-00
            border-cm-400
            rounded
        ;

        &--radio {
            border-radius: 50%;

            &::after {
                border-radius: 50%;
                height: 10px;
                left: 4px;
                top: 4px;
                transform: scale(0);
                transition: 0.1s ease-in-out;
                width: 10px;

                @apply bg-primary-600;
            }
        }

        &--checkbox::after {
            border-bottom: solid 2px #fff;
            border-right: solid 2px #fff;
            height: 10px;
            left: 6px;
            opacity: 0;
            top: 2px;
            transform: rotate(45deg);
            width: 6px;
        }

        &::after {
            content: "";
            display: block;
            position: absolute;
        }
    }

    &--sm {
        .c-check-button__indicator {
            height: 16px;
            width: 16px;

            &--checkbox::after {
                height: 8px;
                left: 5px;
                opacity: 0;
                top: 2px;
                transform: rotate(45deg);
                width: 4px;
            }

            &--radio {
                &::after {
                    left: 2px;
                    top: 2px;
                }
            }
        }
    }
}
</style>
