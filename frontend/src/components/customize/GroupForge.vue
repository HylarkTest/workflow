<template>
    <div
        class="c-group-forge"
        :class="bgClass"
    >
        <label
            v-t="headerPath"
            class="text-sm uppercase text-cm-500 font-semibold mb-4 block"
        >
        </label>

        <FormWrapper
            :form="form"
            dontscroll
        >
            <div class="c-group-forge__top flex items-center">
                <div class="flex-1">
                    <InputLine
                        ref="newInput"
                        formField="name"
                        :placeholder="$t(placeholderPath)"
                    >
                    </InputLine>
                </div>

                <div class="flex items-center ml-4">
                    <div
                        v-if="!hideColor"
                        v-blur="closeColorPicker"
                    >
                        <div class="shadow-center-lg rounded-full mr-4">
                            <button
                                ref="color"
                                class="c-group-forge__color"
                                :style="{ backgroundColor: formDisplayColor }"
                                type="button"
                                @click="toggleColorPicker"
                            >
                            </button>
                        </div>

                        <ColorPickerDropdown
                            v-if="showColorPicker"
                            v-model:color="form.color"
                            :activator="$refs.color"
                            nudgeDownProp="0.625rem"
                            nudgeRightProp="0rem"
                        >
                        </ColorPickerDropdown>
                    </div>

                    <button
                        class="button--sm button-primary"
                        :class="{ unclickable: disabled }"
                        type="submit"
                        :disabled="disabled"
                        @click="submitGroupItem"
                    >
                        <span
                            v-t="editMode ? 'common.save' : 'common.add'"
                        >
                        </span>
                    </button>
                </div>
            </div>
        </FormWrapper>
    </div>
</template>

<script>

import ColorPickerDropdown from '@/components/inputs/ColorPickerDropdown.vue';

import providesColors from '@/vue-mixins/style/providesColors.js';
import { randomExtraColor } from '@/core/display/accentColors.js';

export default {
    name: 'GroupForge',
    components: {
        ColorPickerDropdown,
    },
    mixins: [
        providesColors,
    ],
    props: {
        group: {
            type: [Object, null],
            default: null,
        },
        groupType: {
            type: String,
            required: true,
        },
        item: {
            type: [Object, null],
            default: null,
        },
        editMode: Boolean,
        hideColor: Boolean,
        bgClass: {
            type: String,
            default: 'bg-cm-00',
        },
        processing: Boolean,
        hasFocusOnOpen: Boolean,
    },
    emits: [
        'submitGroupItem',
    ],
    data() {
        return {
            form: this.$apolloForm(() => {
                const data = {
                    groupId: this.group?.id || this.item?.group.id,
                    name: this.item?.name || '',
                };
                if (!this.hideColor) {
                    data.color = this.item?.color || randomExtraColor(); // Random color
                }
                if (this.item) {
                    data.id = this.item.id;
                }
                return data;
            }, { clear: !this.item }),
            showColorPicker: false,
        };
    },
    computed: {
        disabled() {
            return !this.form.name || this.processing;
        },
        typeString() {
            return _.camelCase(this.groupType);
        },
        placeholderPath() {
            return this.editMode ? this.textPath('name') : this.textPath('new');
        },
        headerPath() {
            return this.editMode ? this.textPath('edit') : this.textPath('add');
        },
        formDisplayColor() {
            return this.$root.extraColorDisplay(this.form.color);
        },
    },
    methods: {
        focusNew() {
            this.$refs.newInput.focus();
        },
        selectInput() {
            this.$refs.newInput.select();
        },
        submitGroupItem() {
            if (!this.disabled) {
                this.$emit('submitGroupItem', this.form);
                this.focusNew();
            }
        },
        toggleColorPicker() {
            this.showColorPicker = !this.showColorPicker;
        },
        closeColorPicker() {
            this.showColorPicker = false;
        },
        textPath(textKey) {
            return `customizations.${this.typeString}.item.${textKey}`;
        },
    },
    created() {

    },
    mounted() {
        if (this.hasFocusOnOpen) {
            this.selectInput();
        }
    },
};
</script>

<style scoped>

.c-group-forge {
    &__color {
        @apply
            block
            h-8
            relative
            rounded-xl
            w-8
        ;
    }
}

</style>
