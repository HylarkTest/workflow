<template>
    <div
        class="c-input-box"
        :class="displayClasses"
    >
        <InputField
            ref="input"
            :inputClass="inputClass"
            v-bind="$attrs"
            @onFocus="setFocus"
        >
            <label
                class="c-input-box__label transition-2eio"
                :class="labelClasses"
            >
                <slot name="label"></slot>
            </label>

            <template
                #afterInput
            >
                <slot
                    name="afterInput"
                >
                </slot>
            </template>
        </InputField>
    </div>
</template>

<script>

import InputField from '@/components/inputs/InputField.vue';

export default {

    name: 'InputBox',
    components: {
        InputField,
    },
    mixins: [
    ],
    props: {
        labelType: {
            type: String,
            default: 'integrated',
            validator(value) {
                return ['integrated', 'top'].includes(value);
            },
        },
        shape: {
            type: String,
            default: 'rounded',
            validator(value) {
                return ['rounded', 'oval'].includes(value);
            },
        },
        boxStyle: {
            type: String,
            default: 'plain',
            validator(value) {
                return ['plain', 'border'].includes(value);
            },
        },
        boxShape: {
            type: String,
            default: 'rounded',
            validator(value) {
                return ['rounded', 'oval'].includes(value);
            },
        },
        bgColor: {
            type: String,
            default: 'white',
            validator(val) {
                return ['white', 'gray'].includes(val);
            },
        },
        inputContainerClass: {
            type: String,
            default: '',
        },
        size: {
            type: String,
            default: 'base',
            validator(val) {
                return ['lg', 'base', 'sm', 'md'].includes(val);
            },
        },
    },
    emits: [
        'focusState',
    ],
    data() {
        return {
            inFocus: false,
        };
    },
    computed: {
        labelClasses() {
            return [{ 'c-input-box__label--focus': this.inFocus }, this.labelTypeClass];
        },
        labelTypeClass() {
            return `c-input-box__label--${this.labelType}`;
        },
        // inputClasses() {
        //     return [this.shapeClass, this.styleClass];
        // },
        // shapeClass() {
        //     return `c-input-box--${this.shape}`;
        // },
        // styleClass() {
        //     return `c-input-box--${this.inputStyle}`;
        // },
        inputClass() {
            return `c-input-box__input ${this.inputContainerClass} ${this.extraClasses}`;
        },

        sizeClass() {
            return `c-input-box__size--${this.size}`;
        },

        boxStyleClass() {
            return `c-input-box__style--${this.boxStyle}`;
        },
        bgClass() {
            return `c-input-box__bg--${this.bgColor}`;
        },
        shapeClass() {
            return `c-input-box__shape--${this.boxShape}`;
        },
        displayClasses() {
            return [this.boxStyleClass, this.bgClass, this.shapeClass, this.sizeClass];
        },
        extraClasses() {
            return this.inFocus
                ? 'c-input-box__input--focus shadow-primary-600/20'
                : 'c-input-box__input--hover shadow-primary-600/20';
        },
    },
    methods: {
        setFocus(focus) {
            this.inFocus = focus;
            this.$emit('focusState', focus);
        },
        focus() {
            this.$refs.input.focus();
        },
        select() {
            this.$refs.input.select();
        },
    },
    created() {

    },
};
</script>

<style scoped>
.c-input-box {
    @apply
        relative
    ;

    &__style {
        &--border {
            :deep(.c-input-box__input) {
                @apply
                    border
                    border-cm-300
                    border-solid
                ;

                &--hover:hover {
                    @apply
                        border-cm-500
                    ;
                }

                &--focus {
                    @apply
                        border-primary-600
                    ;
                }
            }
        }

        &--plain {
            :deep(.c-input-box__input) {
                transition: 0.2s ease-in-out;

                &--hover:hover {
                    @apply
                        shadow-md
                    ;
                }

                &--focus {
                    @apply
                        shadow-lg
                    ;
                }
            }
        }
    }

    &__bg {
        &--gray {
            :deep(.c-input-box__input) {
                @apply
                    bg-cm-100
                ;
            }
        }

        &--white {
            :deep(.c-input-box__input) {
                @apply
                    bg-cm-00
                ;
            }
        }
    }

    &__shape {
        &--rounded {
            :deep(.c-input-box__input) {
                @apply
                    p-2
                    rounded-lg
                ;
            }
        }
        &--oval {
            :deep(.c-input-box__input) {
                @apply
                    px-3
                    py-2
                    rounded-full
                ;
            }
        }
    }

    &__size {
        &--sm {
            @apply
                py-0
                text-xs
            ;
        }

        &--md {
            @apply
                py-0
                text-xssm
            ;
        }

        &--lg {
            @apply
                py-2
                text-base
            ;
        }
    }

    &__label {
        @apply
            text-xs
        ;

        &--top {
            @apply
                text-cm-500
            ;
        }

        &--integrated {
            @apply
                absolute
                bg-cm-00
                left-2
                px-1
                text-cm-600
                -top-2
                z-over
            ;
        }

        &--focus {
            @apply
                text-primary-700
            ;
        }
    }
}
</style>
