<template>
    <div
        class="c-displayer-tag inline-flex"
        :class="{ unclickable: processing }"
    >
        <TagsPicker
            placeholder="Add a tag"
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
            @removeTag="removeMarker"
        >
        </TagsPicker>
    </div>
</template>

<script>

import TagsPicker from '@/components/pickers/TagsPicker.vue';

import interactsWithDisplayerMarker from '@/vue-mixins/displayers/interactsWithDisplayerMarker.js';
import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerTag',
    components: {
        TagsPicker,
    },
    mixins: [
        interactsWithDisplayerMarker,
        interactsWithDisplayers,
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        bgColor: {
            type: String,
            default: 'gray',
        },
    },
    data() {
        return {
            typeKey: 'TAG',
        };
    },
    computed: {
        valLength() {
            return this.dataValue?.length;
        },
        showButtonTags() {
            return this.areMarkersButtons && !!this.valLength;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-tag {
}*/

</style>
