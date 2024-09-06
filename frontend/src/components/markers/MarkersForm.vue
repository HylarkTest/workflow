<template>
    <div class="o-markers-form">
        <div
            v-for="group in possibleTagGroups"
            :key="group.id"
            class="o-markers-form__line"
            :class="unclickableGroupClass(group.id)"
        >
            <label class="label-data--subtle shrink-0 w-32 flex items-baseline">
                <i
                    class="fal mr-2 text-primary-500"
                    :class="getIcon('TAG')"
                >
                </i>
                {{ group.name }}
            </label>

            <div class="flex flex-col items-end">
                <TagsPicker
                    :modelValue="markersObj[group.id]"
                    bgColor="white"
                    :group="group"
                    :feature="featureType"
                    :selectedOptionHasRemoveIcon="true"
                    @select="addMarker($event.value, group)"
                >
                    <template
                        #callerButton="{ selectedEvents }"
                    >
                        <button
                            class="button-rounded--sm button-primary--light"
                            type="button"
                            @click="selectedEvents.click"
                        >
                            Add tags
                        </button>
                    </template>
                </TagsPicker>
                <div
                    class="flex flex-wrap justify-end gap-0.5"
                >
                    <div
                        v-for="tag in markersObj[group.id]"
                        :key="tag.id"
                        class="m-1"
                    >
                        <TagBasic
                            :tag="tag"
                            @removeTag="removeMarker(tag, group)"
                        >
                        </TagBasic>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-for="group in possiblePipelineGroups"
            :key="group.id"
            class="o-markers-form__line"
            :class="unclickableGroupClass(group.id)"
        >
            <label class="label-data--subtle shrink-0 w-32 flex items-baseline">
                <i
                    class="fal mr-2 text-primary-500"
                    :class="getIcon('PIPELINE')"
                >
                </i>
                {{ group.name }}
            </label>

            <div>
                <PipelinePicker
                    class="flex flex-col items-end"
                    :modelValue="markersObj[group.id]"
                    bgColor="gray"
                    :group="group"
                    :feature="featureType"
                    comparator="id"
                    :showAllMarkers="true"
                    :isModifiable="true"
                    @select="addMarker($event.value, group)"
                    @removePipeline="removeMarker($event, group)"
                >
                    <template
                        #callerButton="{ selectedEvents }"
                    >
                        <button
                            class="button-rounded--sm button-primary--light"
                            type="button"
                            @click="selectedEvents.click"
                        >
                            {{ pipelinePlaceholder(group) }}
                        </button>
                    </template>
                </PipelinePicker>
            </div>
        </div>

        <div
            v-for="group in possibleStatusGroups"
            :key="group.id"
            class="o-markers-form__line"
            :class="unclickableGroupClass(group.id)"
        >
            <label class="label-data--subtle shrink-0 w-32 flex items-baseline">
                <i
                    class="fal mr-2 text-primary-500"
                    :class="getIcon('STATUS')"
                >
                </i>

                {{ group.name }}
            </label>

            <StatusPicker
                :modelValue="markersObj[group.id]"
                bgColor="gray"
                :feature="featureType"
                comparator="id"
                :group="group"
                @update:modelValue="addMarker($event, group)"
            >
            </StatusPicker>
        </div>
    </div>
</template>

<script>

import StatusPicker from '@/components/pickers/StatusPicker.vue';
import PipelinePicker from '@/components/pickers/PipelinePicker.vue';
import TagsPicker from '@/components/pickers/TagsPicker.vue';

import { getIcon } from '@/core/display/typenamesList.js';

export default {
    name: 'MarkersForm',
    components: {
        StatusPicker,
        PipelinePicker,
        TagsPicker,
    },
    mixins: [
    ],
    props: {
        markerGroupBeingProcessed: {
            type: [String, null],
            default: null,
        },
        markerGroups: {
            type: Array,
            required: true,
        },
        featureType: {
            type: [String, Array, null],
            default: null,
        },
        markerValues: {
            type: Array,
            required: true,
        },
    },
    emits: [
        'addMarker',
        'removeMarker',
    ],
    data() {
        return {

        };
    },
    computed: {
        possibleMarkersLength() {
            // Length of marker groups available to this record/feature
            return !!this.markerGroups?.length;
        },
        groupedMarkers() {
            // Grouped by type of marker
            return _(this.markerGroups).groupBy((group) => {
                return group.type;
            }).value();
        },
        possiblePipelineGroups() {
            return this.groupedMarkers.PIPELINE;
        },
        possibleTagGroups() {
            return this.groupedMarkers.TAG;
        },
        possibleStatusGroups() {
            return this.groupedMarkers.STATUS;
        },
        markerValuesFormatted() {
            return _(this.markerValues).map((marker) => {
                const groupId = marker.group?.id || marker.groupId;
                let val = marker.markers || marker.marker;
                if (_.isArray(val) && val.length && _.isString(val[0])) {
                    const original = _.find(this.markerGroups, { id: groupId });
                    const markers = val.map((item) => {
                        return _.find(original.items, { id: item });
                    });
                    if (original.type === 'STATUS') {
                        const status = markers[0];
                        val = status;
                    } else {
                        val = markers;
                    }
                }
                return [
                    groupId,
                    val,
                ];
            }).fromPairs().value();
        },
        markersObj() {
            return this.markerValuesFormatted;
        },
    },
    methods: {
        pipelinePlaceholder(group) {
            return this.markersObj[group.id] ? 'Add a stage' : 'Start pipeline';
        },
        addMarker(marker, group) {
            this.$emit('addMarker', marker, group);
        },
        removeMarker(marker, group) {
            this.$emit('removeMarker', marker, group);
        },
        unclickableGroupClass(id) {
            return { unclickable: id === this.markerGroupBeingProcessed };
        },
        getIcon(val) {
            return getIcon(val);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-markers-form {
    &__line {
        @apply
            flex
            items-start
            justify-between
            mb-2
        ;
    }
}

</style>
