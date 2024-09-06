<template>
    <div class="o-finder-result-markers mt-1">
        <EditableMarkerSet
            :tags="tags"
            :statuses="statuses"
            :pipelines="pipelines"
            :item="item"
        >
        </EditableMarkerSet>
    </div>
</template>

<script setup>

import {
    computed,
} from 'vue';

import EditableMarkerSet from '@/components/markers/EditableMarkerSet.vue';

const props = defineProps({
    markerGroups: {
        type: Array,
        required: true,
    },
    item: {
        type: Object,
        required: true,
    },
});

const markerGroupsByType = computed(() => {
    return props.markerGroups.reduce((groupedMarkers, markerGroup) => {
        const markerType = _.camelCase(markerGroup.group.type);
        // Concat marker group to existing arr of that marker type, if present, OR to empty array
        const updatedMarkersArr = (groupedMarkers[markerType] || []).concat(markerGroup);
        // Return object with updated value for that marker type
        return {
            ...groupedMarkers,
            [markerType]: updatedMarkersArr,
        };
    }, {});
});

const tags = computed(() => {
    return markerGroupsByType.value.tag;
});
const statuses = computed(() => {
    return markerGroupsByType.value.status;
});
const pipelines = computed(() => {
    return markerGroupsByType.value.pipeline;
});

// const emit = defineEmits([
// ]);

</script>

<style scoped>
/* .o-finder-result-markers {

} */
</style>
