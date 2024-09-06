<template>
    <div class="c-tags-picker">
        <MarkerPicker
            dropdownComponent="DropdownFree"
            :popupProps="{ widthProp: '12.5rem', maxHeightProp: '9.375rem', matchWidth: false }"
            :group="group"
            comparator="id"
            :hasCircleForSelected="true"
            inputSearchPlaceholder="Find a tag..."
            type="TAG"
            :modelValue="modelValue"
            :isModifiable="isModifiable"
            v-bind="$attrs"
        >
            <template
                #selected="{ selectedEvents }"
            >
                <slot
                    :selectedEvents="selectedEvents"
                    name="callerButton"
                >
                    <button
                        v-if="isModifiable && (!valueLength || alwaysShowPrompt)"
                        class="c-tags-picker__picker"
                        :class="[sizeClass, bgClass]"
                        type="button"
                        @click="selectedEvents.click"
                    >
                        <i
                            class="fal fa-tags mr-2"
                        >
                        </i>

                        {{ placeholder }}
                    </button>
                </slot>

                <component
                    v-if="showInSelected && valueLength"
                    :is="isModifiable ? 'ButtonEl' : 'div'"
                    class="flex flex-wrap gap-1"
                    @click="openPicker(selectedEvents.click)"
                >
                    <TagBasic
                        v-for="tag in modelValue"
                        :key="tag.id"
                        :class="displayClasses"
                        :tag="tag"
                        :hideRemove="hideRemove"
                        @removeTag="removeTag(tag)"
                    >
                    </TagBasic>
                </component>
            </template>
        </MarkerPicker>
        <div
            v-if="showAllMarkers && valueLength"
            class="flex flex-wrap gap-1 mt-2"
        >
            <TagBasic
                v-for="tag in modelValue"
                :key="tag.id"
                :class="displayClasses"
                :tag="tag"
                :hideRemove="hideRemove"
                @removeTag="removeTag(tag)"
            >
            </TagBasic>
        </div>
    </div>
</template>

<script>

import MarkerPicker from '@/components/pickers/MarkerPicker.vue';

export default {
    name: 'TagsPicker',
    components: {
        MarkerPicker,
    },
    mixins: [
    ],
    props: {
        placeholder: {
            type: String,
            default: 'Select tags',
        },
        group: {
            type: [Object, null],
            default: null,
        },
        size: {
            type: String,
            default: 'base',
        },
        bgColor: {
            type: String,
            default: 'gray',
        },
        showAllMarkers: Boolean,
        alwaysShowPrompt: Boolean,
        showInSelected: Boolean,
        isModifiable: Boolean,
        modelValue: {
            type: [Array, null],
            default: null,
        },
        displayClasses: {
            type: String,
            default: '',
        },
    },
    emits: [
        'removeTag',
    ],
    data() {
        return {

        };
    },
    computed: {
        sizeClass() {
            return `c-tags-picker__picker--${this.size}`;
        },
        bgClass() {
            return `c-tags-picker__picker--${this.bgColor}`;
        },
        valueLength() {
            return this.modelValue?.length;
        },
        hideRemove() {
            return !this.isModifiable;
        },
    },
    methods: {
        removeTag(tag) {
            this.$emit('removeTag', tag);
        },
        openPicker(clickEvent) {
            if (this.isModifiable) {
                clickEvent();
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-tags-picker {
    &__picker {
        transition: 0.2s ease-in-out;
        @apply
            bg-cm-100
            flex
            items-center
            px-4
            py-2
            rounded-full
            text-xs
        ;

        &:hover {
            @apply
                bg-cm-200
            ;
        }

        &--white {
            @apply
                bg-cm-00
            ;

            &:hover {
                @apply
                    bg-primary-100
                    text-primary-600
                ;
            }
        }

        &--sm {
            @apply
                px-2
                py-0.5
            ;
        }
    }
}

</style>
