<template>
    <div
        class="c-layout-widget stop-blur"
        @click.stop
    >
        <component
            :is="buttonComponent"
            :type="displayOnly ? '' : 'button'"
            class="c-layout-widget__button centered transition-2eio"
            :class="buttonClasses"
            @click.stop="createFeatureFormModal"
        >
            <i
                class="c-layout-widget__icon far"
                :class="widgetIcon"
            >
            </i>

            <i
                class="c-layout-widget__state fas fa-circle-plus"
            >
            </i>
        </component>
    </div>
</template>

<script setup>
import {
    defineProps,
    toRefs,
    computed,
    reactive,
    watch,
} from 'vue';

import useFeatureItemModal from '@/composables/useFeatureItemModal.js';

import { featureTypes } from '@/core/display/typenamesList.js';

const props = defineProps({
    widgets: {
        type: Object,
        default: () => ({}),
    },
    displayOnly: Boolean,
    size: {
        type: String,
        default: '',
    },
});

const {
    widgets,
    displayOnly,
    size,
} = toRefs(props);

const buttonComponent = computed(() => (displayOnly.value ? 'div' : 'button'));

const buttonClasses = computed(() => {
    const buttonSize = `c-layout-widget__circle--${size.value}`;
    const buttonDisplay = displayOnly.value ? 'bg-cm-00' : 'bg-cm-00 hover:bg-primary-200';

    return [
        buttonDisplay.value,
        buttonSize.value,
    ];
});

const addType = computed(() => widgets.value.addShortcuts[0]);

const widgetIcon = computed(() => _.find(featureTypes, { val: addType.value }).symbol);

const createModalProps = reactive({ featureType: addType.value });
watch(() => addType.value, (value) => { createModalProps.featureType = value; });

const { createFeatureFormModal } = useFeatureItemModal(createModalProps);
</script>

<style scoped>

.c-layout-widget {
    &__button {
        transition: 0.2s ease-in-out;

        @apply
            h-8
            relative
            rounded-md
            text-primary-600
            w-8
        ;

        &--xs {
            @apply
                h-6
                w-6
            ;

            .c-layout-widget__icon {
                @apply
                    text-sm
                ;
            }

            .c-layout-widget__state {
                @apply
                    right-0
                    text-xs
                    top-0
                ;
            }
        }
    }

    &__icon {
        @apply
            text-xl
        ;
    }

    &__state {
        @apply
            absolute
            -right-0.5
            text-primary-800
            text-smbase
            -top-0.5
        ;
    }
}

</style>
