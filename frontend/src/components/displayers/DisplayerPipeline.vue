<template>
    <div
        class="c-displayer-pipeline"
        :class="{ unclickable: processing }"
    >
        <PipelinePicker
            size="sm"
            :bgColor="bgColor"
            :group="dataInfo"
            :displayClasses="displayClasses"
            :showInSelected="showInSelected"
            :alwaysShowPrompt="alwaysShowPrompt"
            :modelValue="dataValue"
            :isModifiable="isModifiable"
            :showAllMarkers="showAllMarkers"
            @select="editMarker($event.value)"
            @removePipeline="removeMarker"
        >
        </PipelinePicker>
    </div>
</template>

<script>

import PipelinePicker from '@/components/pickers/PipelinePicker.vue';

import interactsWithDisplayerMarker from '@/vue-mixins/displayers/interactsWithDisplayerMarker.js';
import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerPipeline',
    components: {
        PipelinePicker,
    },
    mixins: [
        interactsWithDisplayerMarker,
        interactsWithDisplayers,
    ],
    props: {
        bgColor: {
            type: String,
            default: 'gray',
        },
    },
    data() {
        return {
            typeKey: 'PIPELINE',
        };
    },
    computed: {
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-displayer-pipeline {
    &__add {
        transition: 0.2s ease-in-out;
        @apply
            bg-cm-100
            flex
            items-center
            px-2
            py-0.5
            rounded-full
            text-cm-600
            text-xs
        ;

        &:hover {
            @apply
                text-primary-600
            ;
        }
    }
}

</style>
