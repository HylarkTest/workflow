<template>
    <div
        class="c-displayer-feature-pin"
    >
        <DisplayerFeatureItem
            featureType="PINBOARD"
            :list="null"
            :dataValue="dataValue"
            v-bind="$attrs"
        >
            <template #featureItem="{ createFeatureFormModal }">
                <PinMini
                    :pin="dataValue"
                    @selectPin="createFeatureFormModal"
                >
                </PinMini>
            </template>
        </DisplayerFeatureItem>
    </div>
</template>

<script setup>
import { toRefs } from 'vue';

import PinMini from '@/components/pinboard/PinMini.vue';

const props = defineProps({
    dataValue: {
        type: [Object, null],
        required: true,
    },
});

const {
    dataValue,
} = toRefs(props);
</script>
