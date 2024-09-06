<template>
    <div
        class="c-displayer-feature-note"
    >
        <DisplayerFeatureItem
            featureType="NOTES"
            :list="null"
            :dataValue="dataValue"
            v-bind="$attrs"
        >
            <template #featureItem="{ createFeatureFormModal }">
                <NoteMini
                    :note="dataValue"
                    @selectNote="createFeatureFormModal"
                >
                </NoteMini>
            </template>
        </DisplayerFeatureItem>
    </div>
</template>

<script setup>
import { toRefs } from 'vue';

import NoteMini from '@/components/notes/NoteMini.vue';

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
