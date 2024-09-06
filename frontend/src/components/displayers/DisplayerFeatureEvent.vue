<template>
    <div
        class="c-displayer-event"
    >
        <DisplayerFeatureItem
            featureType="EVENTS"
            :list="null"
            :dataValue="dataValue"
            v-bind="$attrs"
        >
            <template #featureItem="{ createFeatureFormModal }">
                <EventMini
                    :event="dataValue"
                    :hoverable="true"
                    bgClass=""
                    @selectEvent="createFeatureFormModal"
                >
                </EventMini>
            </template>
        </DisplayerFeatureItem>
    </div>
</template>

<script setup>
import { toRefs } from 'vue';

import EventMini from '@/components/events/EventMini.vue';

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
