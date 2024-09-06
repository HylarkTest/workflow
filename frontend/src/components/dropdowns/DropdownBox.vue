<template>
    <div
        class="c-dropdown-box"
        :class="dropdownClasses"
    >
        <DropdownLabel
            v-if="inlineLabel && (labelOutside || labelBeside)"
            :label="inlineLabel"
        >
        </DropdownLabel>
        <DropdownBasic
            :optionsPopupProps="optionsPopupProps"
            :modelValue="modelValue"
            clearPositioning="-top-1 -right-1 absolute"
            :hasCircleForSelected="hasCircleForSelected"
            :showClear="showClear"
            v-bind="$attrs"
        >
            <!-- <template
                #label="{ popupState }"
            >
                <label
                    class="c-dropdown-box__label transition-2eio"
                    :class="[labelTypeClass, { 'c-dropdown-box__label--focus': popupState }]"
                >
                    <slot name="label">
                    </slot>
                </label>
            </template> -->

            <template
                #selected="{
                    display, popupState, selectedEvents, original, closePopup,
                }"
            >
                <DropdownDisplay
                    class="c-dropdown-box__display"
                    :class="[displayClasses, extraClasses(popupState)]"
                    :display="display"
                    :popupState="popupState"
                    :size="size"
                    :selectedEvents="selectedEvents"
                    :inlineLabel="inlineLabel"
                    :original="original"
                    :showDivider="showDivider"
                    v-bind="$attrs"
                >
                    <template
                        #selected
                    >
                        <slot
                            name="selected"
                            :original="original"
                            :display="display"
                            :popupState="popupState"
                            :selectedEvents="selectedEvents"
                            :closePopup="closePopup"
                        >
                        </slot>
                    </template>

                    <template
                        #inlineDisplayAfter
                    >
                        <slot
                            name="inlineDisplayAfter"
                            :original="original"
                            :closePopup="closePopup"
                        >
                        </slot>
                    </template>
                </DropdownDisplay>
            </template>
            <template
                #option="scope"
            >
                <slot
                    name="wholeOption"
                    v-bind="scope"
                >
                    <DropdownOptions
                        :size="size"
                        :hasCircleForSelected="hasCircleForSelected"
                        v-bind="scope"
                    >
                        <template
                            #option
                        >
                            <slot
                                name="option"
                                v-bind="scope"
                            >
                            </slot>
                        </template>
                    </DropdownOptions>
                </slot>
            </template>

            <template
                #popupEnd="{ selectedEvents }"
            >
                <slot
                    name="popupEnd"
                    :selectedEvents="selectedEvents"
                >
                </slot>
            </template>

            <template
                v-for="(_, slot) of proxySlots()"
                #[slot]="scope"
            >
                <slot
                    :name="slot"
                    v-bind="scope"
                />
            </template>
        </DropdownBasic>

        <slot
            name="general"
        >
        </slot>
    </div>
</template>

<script>

import interactsWithDropdowns from '@/vue-mixins/interactsWithDropdowns.js';

export default {
    components: {
    },
    mixins: [
        interactsWithDropdowns,
    ],
    props: {
        // Integrated, top
        // labelType: {
        //     type: String,
        //     default: 'integrated',
        // },
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
        hasColorOnSelection: Boolean,
        hasCircleForSelected: Boolean,
        showClear: Boolean,
    },
    emits: [
        'input',
    ],
    data() {
        return {
        };
    },
    computed: {
        // labelTypeClass() {
        //     return `c-dropdown-box__label--${this.labelType}`;
        // },
        boxStyleClass() {
            return `c-dropdown-box__style--${this.boxStyle}`;
        },
        sizeClass() {
            return `c-dropdown-box__size--${this.size}`;
        },
        bgClass() {
            return `c-dropdown-box__bg--${this.bgColor}`;
        },
        shapeClass() {
            return `c-dropdown-box__shape--${this.boxShape}`;
        },
        displayClasses() {
            return [this.boxStyleClass, this.sizeClass, this.bgClass, this.shapeClass];
        },
        dropdownClasses() {
            return [
                { 'flex-col': this.labelOutside },
                { 'items-center': this.labelBeside },
                { 'c-dropdown-box--selection': this.highlightOnSelection },
            ];
        },
        highlightOnSelection() {
            return this.hasColorOnSelection && this.modelValue;
        },
    },
    methods: {
        // proxySlots needs to be a method because $slots is not reactive.
        // When the slots change Vue re-renders the component but does not
        // update computed properties that rely on $slots. So by making it a
        // method we can ensure it is called when the component re-renders.
        proxySlots() {
            return _.omit(this.$slots, ['selected', 'option', 'popupEnd']);
        },
        extraClasses(state) {
            return state
                ? 'c-dropdown-box__display--focus shadow-primary-600/20'
                : 'c-dropdown-box__display--hover shadow-primary-600/20';
        },
    },
};

</script>

<style scoped>
    .c-dropdown-box {
        @apply
            inline-flex
        ;

        /*
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
                ;
            }

            &--focus {
                @apply
                    text-primary-700
                ;
            }
        }
         */

        &__style {
            &--border {
                @apply
                    border
                    border-solid
                ;
            }

            &--plain {
                @apply
                    border
                    border-transparent
                ;

                :deep(&.c-dropdown-box__display) {
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

        &__size {
            &--lg {
                @apply
                    p-2
                    text-smbase
                ;
            }

            &--base {
                padding: 5px 8px;
            }

            &--sm {
                padding: 4px 6px;
            }
        }

        &__shape {
            &--rounded {
                @apply
                    rounded-lg
                ;
            }

            &--oval {
                @apply
                    rounded-full
                ;
            }
        }

        &__bg {
            &--white {
                @apply
                    bg-cm-00
                ;
            }

            &--gray {
                @apply
                    bg-cm-100
                ;
            }
        }

        &--selection {
            .c-dropdown-box__bg {
                &--white {
                    @apply
                        bg-primary-100
                    ;
                }

                &--gray {
                    @apply
                        bg-primary-100
                    ;
                }
            }

            .c-dropdown-box__style--plain {
                @apply
                    border
                    border-primary-600
                ;
            }
        }
    }
</style>
