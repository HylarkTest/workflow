<template>
    <div class="o-filter-options text-xssm flex justify-between w-full items-center">
        <div>
            <div
                v-if="option.__typename === 'Marker'"
            >
                <component
                    :is="markerComponent"
                    :item="option"
                    size="sm"
                >
                </component>
            </div>

            <div
                v-else-if="option.name"
                class="cursor-pointer flex items-baseline"
            >
                <ImageOrFallback
                    v-if="showImage"
                    class="o-filter-options__image"
                    :class="{ 'border-2 border-primary-600 border-solid': isSelected }"
                    imageClass="rounded-full"
                    :image="option.avatar"
                    :name="option.name"
                >
                </ImageOrFallback>

                <i
                    v-if="showIcon"
                    class="fal mr-2 text-cm-400"
                    :class="option.icon"
                >
                </i>

                <p
                    :class="{ 'font-semibold text-primary-600': isSelected }"
                >
                    {{ option.name }}
                </p>
            </div>
            <div
                v-else
                class="inline-flex"
                :class="[option.classes, selectedClasses]"
            >
                <slot
                    :name="slotName"
                    :option="option"
                >
                    <PriorityFlag
                        v-if="isPriority"
                        class="mr-2"
                        :priority="option.value"
                    >
                    </PriorityFlag>

                    <template
                        v-if="option.text"
                    >
                        {{ option.text }}
                    </template>

                    <span
                        v-else
                        v-t="option.textPath"
                    >
                    </span>
                </slot>
            </div>
        </div>

        <CheckButton
            class="pointer-events-none"
            :modelValue="isSelected"
            size="sm"
            @click.stop
        >
        </CheckButton>
    </div>
</template>

<script>

import TagDisplay from '@/components/customize/TagDisplay.vue';
import StatusDisplay from '@/components/customize/StatusDisplay.vue';
import StageDisplay from '@/components/customize/StageDisplay.vue';
import PriorityFlag from '@/components/assets/PriorityFlag.vue';

export default {
    name: 'FilterDropdownOption',
    components: {
        TagDisplay,
        StatusDisplay,
        StageDisplay,
        PriorityFlag,
    },
    mixins: [
    ],
    props: {
        option: {
            type: Object,
            required: true,
        },
        slotName: {
            type: String,
            default: 'filterOption',
        },
        isSelected: Boolean,
    },
    emits: [
        'applyFilter',
        'removeFilter',
    ],
    data() {
        return {
        };
    },
    computed: {
        showImage() {
            return false;
        },
        showIcon() {
            return !!this.option.icon;
        },
        selectedClasses() {
            if (this.isSelected) {
                return this.option.selectedClasses;
            }
            return '';
        },
        markerComponent() {
            if (this.option.__typename !== 'Marker') {
                return null;
            }
            const type = this.option.group.type;
            if (type === 'STATUS') {
                return 'StatusDisplay';
            }
            if (type === 'TAG') {
                return 'TagDisplay';
            }
            return 'StageDisplay';
        },
        isPriority() {
            return this.option.optionType === 'PRIORITIES';
        },
    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>
.o-filter-options {
    &__option {
        @apply
            flex
            items-center
            justify-between
            px-3
            py-1
            relative
            w-full
        ;
    }

    &__image {
        @apply
            bg-primary-600
            h-6
            min-h-6
            min-w-6
            mr-3
            text-cm-00
            text-xs
            w-6
        ;
    }
}
</style>
