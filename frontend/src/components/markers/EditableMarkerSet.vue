<template>
    <div
        class="c-editable-marker-sets flex flex-wrap items-center gap-1"
    >
        <template
            v-if="tags?.length"
        >
            <TagsPicker
                v-for="tag in tags"
                :key="tag.group.id"
                :group="tag.group"
                :modelValue="tag.markers"
                :isModifiable="true"
                :showInSelected="true"
                @select="editMarker($event.value, tag.markers)"
                @removeTag="removeMarker($event, tag.markers)"
                @click.stop
            >
            </TagsPicker>
        </template>

        <template
            v-if="statuses?.length"
        >
            <StatusPicker
                v-for="status in statuses"
                :key="status.group.id"
                :group="status.group"
                :modelValue="status.marker"
                @update:modelValue="editMarker($event, status.marker)"
                @click.stop
            >
            </StatusPicker>
        </template>

        <template
            v-if="pipelines?.length"
        >
            <PipelinePicker
                v-for="pipeline in pipelines"
                :key="pipeline.group.id"
                :modelValue="pipeline.markers"
                :group="pipeline.group"
                :isModifiable="true"
                :showInSelected="true"
                @select="editMarker($event.value, pipeline.markers)"
                @removePipeline="removeMarker($event, pipeline.markers)"
                @click.stop
            >
            </PipelinePicker>
        </template>
    </div>
</template>

<script>

import PipelinePicker from '@/components/pickers/PipelinePicker.vue';
import StatusPicker from '@/components/pickers/StatusPicker.vue';
import TagsPicker from '@/components/pickers/TagsPicker.vue';

import { removeMarker, setMarker } from '@/core/repositories/markerRepository.js';

export default {
    name: 'EditableMarkerSet',
    components: {
        PipelinePicker,
        StatusPicker,
        TagsPicker,
    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        tags: {
            type: Array,
            default() {
                return [];
            },
        },
        statuses: {
            type: Array,
            default() {
                return [];
            },
        },
        pipelines: {
            type: Array,
            default() {
                return [];
            },
        },
    },
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        editMarker(selectedMarker, currentMarkers) {
            const canHaveMultipleMarkers = _.isArray(currentMarkers);
            const existingMarker = canHaveMultipleMarkers
                ? !!currentMarkers.find((marker) => marker.id === selectedMarker.id)
                : currentMarkers.id === selectedMarker.id;

            if (existingMarker) {
                this.removeMarker(selectedMarker);
            } else {
                this.addMarker(selectedMarker);
            }
        },
        addMarker(marker) {
            setMarker(this.item, marker);
        },
        removeMarker(marker) {
            removeMarker(this.item, marker);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-editable-marker-sets {

} */

</style>
