<template>
    <div
        class="c-displayer-feature-item"
        :class="cantModifyClass"
    >

        <div
            v-if="dataValue"
            class="feature__item--summary"
        >
            <div class="flex justify-end mt-1 mr-1">
                <button
                    type="button"
                    class="button-rounded--xs button-primary--medium"
                    @click.exact="createFullViewModal"
                    @click.alt="$router.push(itemRoute)"
                >
                    View all
                </button>
            </div>

            <!-- Display the item or the new button, however later we may add the OR feature on slots -->
            <slot
                name="featureItem"
                :createFeatureFormModal="createFeatureForm"
            >
            </slot>
        </div>

        <button
            v-else
            class="feature__item--summary bg-cm-100 text-xs px-2 py-1 hover:shadow-md transition-2eio"
            type="button"
            @click="createFeatureForm"
        >
            <i class="fal fa-circle-plus mr-0.5">
            </i>
            {{ $t(featureAddTextPath) }}
        </button>
    </div>
</template>

<script setup>
import { toRefs, computed } from 'vue';

import useDisplayerFeatureItem from '@/composables/useDisplayerFeatureItem.js';

import { openFullEntityViewModal } from '@/vue-mixins/views/interactsWithViewsItem.js';

const props = defineProps({
    // Information about what is being displayed and how
    dataInfo: {
        type: Object,
        required: true,
    },
    // The data being displayed
    dataValue: {
        type: [String, Object, Number, Boolean, null],
        required: true,
    },
    item: {
        type: [Object, null],
        default: null,
    },
    page: {
        type: [Object, null],
        required: true,
    },
    list: {
        type: [Object, null],
        required: true,
    },
    isModifiable: Boolean,
});

const {
    dataValue,
    item,
    page,
} = toRefs(props);

const {
    featureKey,
    featureFormatted,
    featureAddTextPath,
    cantModifyClass,
    createFeatureFormModal,
} = useDisplayerFeatureItem(props);

const itemRoute = computed(() => {
    return {
        name: 'entityPage',
        params: {
            itemId: item.value.id,
            pageId: page.value.id,
            tab: featureFormatted.value,
        },
    };
});

function createFeatureForm() {
    createFeatureFormModal();
}

function createFullViewModal() {
    openFullEntityViewModal({
        item: item.value,
        page: page.value,
        defaultTab: featureKey.value,
    });
}
</script>

<script>
export default {
    name: 'DisplayerFeatureItem',
};
</script>

<style scoped>

.c-displayer-feature-item {
    min-width: 90px;
    @apply
        relative
        w-full
    ;
}

</style>
