<template>
    <div
        class="c-displayer-feature-link"
    >
        <DisplayerFeatureItem
            featureType="LINKS"
            :list="null"
            :dataValue="dataValue"
            v-bind="$attrs"
        >
            <template #featureItem="{ createFeatureFormModal }">
                <LinkMini
                    :link="dataValue"
                    @selectLink="createFeatureFormModal"
                >
                </LinkMini>
            </template>

        </DisplayerFeatureItem>
    </div>
</template>

<script setup>
import { toRefs } from 'vue';

import LinkMini from '@/components/links/LinkMini.vue';

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
