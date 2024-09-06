<template>
    <div
        class="c-input-field"
    >
        <slot></slot>

        <div
            class="c-input-field__main transition-2eio"
            :class="inputClass"
        >
            <div
                class="flex flex-1"
                :class="elOrderClass"
            >
                <input
                    ref="focus"
                    class="bg-transparent w-full"
                    :disabled="disabled"
                    :value="formValue"
                    :data-form-type="dataFormType"
                    :maxLength="inputMaxLimit"
                    :autocomplete="autocomplete"
                    :name="name"
                    v-bind="$attrs"
                    @focus="onFocus"
                    @blur="offFocus"
                    @input="emitInput($event.target.value)"
                    @keydown="validateKeydown"
                />

                <component
                    v-if="icon"
                    :is="icon.component"
                    class="transition-2eio"
                    :class="iconClasses"
                    :type="iconType"
                    @click.stop="clickIcon"
                >
                    <i
                        :class="icon.symbol"
                    >
                    </i>
                </component>
            </div>

            <div
                v-if="$slots.afterInput"
                class="ml-2"
            >
                <slot
                    name="afterInput"
                >
                </slot>
            </div>

            <ClearButton
                v-if="showClear"
                class="ml-2 transition-2eio"
                :class="hasText ? 'opacity-100' : 'opacity-0'"
                positioningClass="relative self-center"
                @click="emitInput('')"
            >
            </ClearButton>
        </div>

        <CharactersRemaining
            v-if="displayRemainingCharacters"
            positioningClasses="absolute -bottom-3.5 right-0"
            :maxLength="maxLength"
            :length="textLength"
        >
        </CharactersRemaining>

        <transition name="t-fade">
            <AlertTooltip
                v-if="inputFieldError"
                :alertPosition="alertPosition"
                :customClass="alertClass"
            >
                {{ inputFieldError }}
            </AlertTooltip>
        </transition>
    </div>
</template>

<script>
import AlertTooltip from '@/components/popups/AlertTooltip.vue';
import CharactersRemaining from '@/components/assets/CharactersRemaining.vue';
import ClearButton from '@/components/buttons/ClearButton.vue';

import formWrapperChild from '@/vue-mixins/formWrapperChild.js';

import { characterValidationMap, validateInputByCharacterType } from '@/core/validation.js';

export default {
    name: 'InputField',
    components: {
        AlertTooltip,
        CharactersRemaining,
        ClearButton,
    },
    mixins: [
        formWrapperChild,
    ],
    props: {
        alertClass: {
            type: String,
            default: '',
        },
        alertPosition: {
            type: [Object, null],
            default: null,
        },
        disabled: Boolean,
        icon: {
            type: Object,
            default: () => ({}),
            // Keys: symbol, position (right, left), and component (button, div)
        },
        highlightIconOnFocus: Boolean,
        inputClass: {
            type: String,
            default: '',
        },
        showClear: Boolean,
        dataFormType: {
            type: String,
            default: 'other',
        },
        maxLength: {
            type: Number,
            default: 255,
        },
        autocomplete: {
            type: String,
            default: 'off',
        },
        name: {
            type: String,
            default: 'other',
        },
        keydownValidationType: {
            type: [String, null],
            default: null,
            validator(val) {
                return Object.keys(characterValidationMap).includes(val);
            },
        },
        showRemainingCharactersProp: Boolean,
        bufferLimit: {
            type: Number,
            default: 0,
        },
    },
    emits: [
        'input',
        'onFocus',
        'clickIcon',
    ],
    data() {
        return {
            inFocus: false,
            keydownErrorMessage: '',
        };
    },
    computed: {
        textLength() {
            // In mixin interactsWithFormWrapperValue
            return this.formValue?.length;
        },
        hasText() {
            return !!this.textLength;
        },
        iconIsButton() {
            return this.icon?.component === 'button';
        },
        iconType() {
            return this.iconIsButton ? 'button' : '';
        },
        iconClasses() {
            let classes = '';

            const color = this.icon.color || 'text-cm-500';
            classes = color;

            if (this.highlightIconOnFocus && this.inFocus) {
                classes = classes.concat(' text-primary-600');
            }
            if (this.iconIsButton) {
                classes = classes.concat(' hover:text-primary-600 cursor-pointer');
            }
            if (this.icon.position === 'left') {
                classes = classes.concat(' mr-2');
            }
            if (this.icon.position === 'right') {
                classes = classes.concat(' ml-2');
            }
            return classes;
        },
        elOrderClass() {
            if (this.icon?.position === 'left') {
                return 'flex-row-reverse';
            }
            return '';
        },
        inputFieldError() {
            return this.errorMessage || this.keydownErrorMessage;
        },
        inputMaxLimit() {
            return this.maxLength + this.bufferLimit;
        },
        displayRemainingCharacters() {
            const closeToLimit = this.maxLength * 0.9 <= this.textLength;
            return this.showRemainingCharactersProp && closeToLimit;
        },
    },
    methods: {
        onFocus() {
            this.inFocus = true;
            this.$emit('onFocus', this.inFocus);
        },
        offFocus() {
            this.inFocus = false;
            this.$emit('onFocus', this.inFocus);
        },
        focus() {
            this.$refs.focus?.focus();
        },
        select() {
            this.$refs.focus?.select();
        },
        clickIcon() {
            if (this.iconIsButton) {
                this.$emit('clickIcon');
            }
        },
        isValidKeydown(keydownCharacter) {
            return !this.keydownValidationType
            || validateInputByCharacterType(keydownCharacter, this.keydownValidationType);
        },
        validateKeydown(event) {
            if (this.isValidKeydown(event.key)) {
                this.unsetKeydownErrorMessage();
            } else {
                this.setKeydownErrorMessage();
                event.preventDefault();
            }
        },
        setKeydownErrorMessage() {
            this.keydownErrorMessage = this.$t(`feedback.responses.inputValidation.${this.keydownValidationType}`);
            setTimeout(() => {
                this.unsetKeydownErrorMessage();
            }, 5000);
        },
        unsetKeydownErrorMessage() {
            this.keydownErrorMessage = '';
        },
    },
    created() {

    },
};
</script>

<style scoped>
.c-input-field {
    &__main {
        @apply
            flex
            items-end
            relative
        ;
    }

}
</style>
