<template>
    <div
        class="c-displayer-document"
    >
        <DisplayerFeatureItem
            featureType="DOCUMENTS"
            :list="null"
            :dataValue="dataValue"
            v-bind="$attrs"
        >
            <template #featureItem="{ createFeatureFormModal }">
                <DocumentMini
                    :document="dataValue"
                    @selectDocument="createFeatureFormModal"
                >
                </DocumentMini>
            </template>
        </DisplayerFeatureItem>
    </div>
</template>

<script setup>
import { toRefs } from 'vue';

import DocumentMini from '@/components/documents/DocumentMini.vue';

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
