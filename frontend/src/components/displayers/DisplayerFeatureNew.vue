<template>
    <div
        class="c-displayer-feature-new cardDesigns__button"
        :class="cantModifyClass"
    >
        <ButtonEl
            :class="displayClasses"
            @click.exact="createFeatureFormModal"
            @click.alt="createFeatureFormModal"
        >
            <i
                class="far fa-circle-plus mr-2"
            >
            </i>

            <span
                v-t="featureAddTextPath"
            >
            </span>
        </ButtonEl>
    </div>
</template>

<script setup>
import { toRefs, computed } from 'vue';
import useDisplayerFeatureItem from '@/composables/useDisplayerFeatureItem.js';

import {
    getCombo,
} from '@/core/display/displayerInstructions.js';

const props = defineProps({
    // Information about what is being displayed and how
    dataInfo: {
        type: Object,
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
    isModifiable: Boolean,
});

const {
    dataInfo,
} = toRefs(props);

const {
    featureAddTextPath,
    cantModifyClass,
    createFeatureFormModal,
} = useDisplayerFeatureItem(props);

const combo = computed(() => dataInfo.value?.combo || 1);
const selectedCombo = computed(() => getCombo('FEATURE_NEW', combo.value));

const displayClasses = computed(() => {
    if (!selectedCombo.value) {
        return '';
    }
    if (_.isObject(selectedCombo.value)) {
        return selectedCombo.value.classes;
    }
    return selectedCombo.value;
});
</script>

<script>
export default {
    name: 'DisplayerFeatureNew',
};
</script>

<style scoped>

/*.c-displayer-feature-new {

} */

</style>
