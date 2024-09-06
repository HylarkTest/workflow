<template>
    <div class="c-pipeline-picker">
        <MarkerPicker
            dropdownComponent="DropdownFree"
            :popupProps="{ widthProp: '12.5rem', maxHeightProp: '9.375rem', matchWidth: false }"
            :group="group"
            type="PIPELINE"
            comparator="id"
            :hasCircleForSelected="true"
            :modelValue="orderedStages"
            inputSearchPlaceholder="Find a pipeline stage..."
            v-bind="$attrs"
        >
            <template
                #selected="{ selectedEvents, original }"
            >
                <slot
                    :selectedEvents="selectedEvents"
                    name="callerButton"
                >
                    <button
                        v-if="showPlaceholderPrompt"
                        class="c-pipeline-picker__picker"
                        :class="[sizeClass, bgClass]"
                        type="button"
                        @click="selectedEvents.click"
                    >
                        <i
                            class="fal fa-diagram-next mr-2"
                        >
                        </i>

                        {{ adjustedPlaceholder }}
                    </button>
                </slot>

                <div
                    v-if="showStageInSelected"
                    class="flex items-center"
                >
                    <component
                        v-if="original"
                        :is="isModifiable ? 'ButtonEl' : 'div'"
                        @click="openPicker(selectedEvents.click)"
                    >
                        <PipelineBasic
                            :pipeline="original[0]"
                            :class="displayClasses"
                            :hideRemove="hideRemove"
                            :size="pipelineSize"
                            @removePipeline="removePipeline(original[0])"
                        >
                        </PipelineBasic>
                    </component>
                </div>
            </template>
        </MarkerPicker>

        <div
            v-if="showAllMarkers && valueLength"
            class="flex flex-wrap gap-1 mt-2"
        >
            <PipelineBasic
                v-for="pipeline in modelValue"
                :key="pipeline.id"
                :hideRemove="hideRemove"
                :displayClasses="displayClasses"
                :pipeline="pipeline"
                :size="pipelineSize"
                @removePipeline="removePipeline(pipeline)"
            >
            </PipelineBasic>
        </div>
    </div>
</template>

<script>

import { warn } from 'vue';

import MarkerPicker from '@/components/pickers/MarkerPicker.vue';
import PipelineStages from '@/components/pickers/PipelineStages.vue';

export default {
    name: 'PipelinePicker',
    components: {
        MarkerPicker,
        PipelineStages,
    },
    mixins: [
    ],
    props: {
        placeholder: {
            type: String,
            default: '',
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
        alwaysShowPrompt: Boolean,
        // Should not both be true (showInSelected and showAllMarkers)
        showInSelected: Boolean,
        showAllMarkers: Boolean,
        isModifiable: Boolean,
        modelValue: {
            type: [Array, null],
            default: null,
        },
        displayClasses: {
            type: String,
            default: '',
        },
        pipelineSize: {
            type: String,
            default: 'sm',
            validator(val) {
                return ['sm', 'xs'].includes(val);
            },
        },
    },
    emits: [
        'removePipeline',
    ],
    data() {
        return {

        };
    },
    computed: {
        sizeClass() {
            return `c-pipeline-picker__picker--${this.size}`;
        },
        adjustedPlaceholder() {
            if (this.placeholder) {
                return this.placeholder;
            }
            return this.valueLength ? 'Add a stage' : 'Start pipeline';
        },
        valueLength() {
            return this.modelValue?.length;
        },
        hideRemove() {
            return !this.isModifiable;
        },
        bgClass() {
            return `c-pipeline-picker__picker--${this.bgColor}`;
        },
        orderedStages() {
            return _.orderBy(this.modelValue, 'order', 'desc');
        },
        showPlaceholderPrompt() {
            return this.isModifiable
                && (!this.valueLength || this.alwaysShowPrompt);
        },
        showStageInSelected() {
            return this.showInSelected
                && this.valueLength;
        },
    },
    methods: {
        removePipeline(pipeline) {
            this.$emit('removePipeline', pipeline);
        },
        openPicker(clickEvent) {
            if (this.isModifiable) {
                clickEvent();
            }
        },
    },
    created() {
        if (this.showInSelected && this.showAllMarkers) {
            warn('showInSelected and showAllMarkers cannot both be true');
        }
    },
};
</script>

<style scoped>

.c-pipeline-picker {
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
