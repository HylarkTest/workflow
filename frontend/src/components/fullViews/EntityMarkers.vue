<template>
    <div
        class="o-entity-markers bg-cm-100 rounded-xl p-6"
    >
        <!-- <h4 class="text-2xl font-bold mb-4">
            Markers
        </h4> -->

        <div
            v-if="tagGroups && tagGroups.length"
            class="mb-8 last:mb-0"
        >
            <div
                v-for="tagGroup in tagGroups"
                :key="tagGroup.id"
                class="mb-5 last:mb-0"
            >
                <span
                    class="block mb-2 label-data--dark"
                >
                    {{ tagGroup.name }}
                </span>

                <DisplayerTag
                    bgColor="white"
                    :dataValue="getItemMarkers(tagGroup)"
                    :dataInfo="tagGroup"
                    :mapping="mapping"
                    :item="item"
                    :alwaysShowPrompt="true"
                    :showAllMarkers="true"
                    :isModifiable="true"
                >
                </DisplayerTag>
            </div>
        </div>

        <div
            v-if="statusGroups && statusGroups.length"
            class="mb-8 last:mb-0"
        >
            <div
                v-for="statusGroup in statusGroups"
                :key="statusGroup.id"
                class="mb-5 last:mb-0"
            >
                <span
                    class="block mb-2 label-data--dark"
                >
                    {{ statusGroup.name }}
                </span>

                <DisplayerStatus
                    :dataValue="getItemMarkers(statusGroup)"
                    :dataInfo="statusGroup"
                    :mapping="mapping"
                    :item="item"
                    :isModifiable="true"
                >
                </DisplayerStatus>
            </div>
        </div>

        <div v-if="pipelineGroups && pipelineGroups.length">
            <div
                v-for="pipelineGroup in pipelineGroups"
                :key="pipelineGroup.id"
                class="mb-5 last:mb-0"
            >
                <span
                    class="block mb-2 label-data--dark"
                >
                    {{ pipelineGroup.name }}
                </span>

                <DisplayerPipeline
                    :dataValue="getItemMarkers(pipelineGroup)"
                    :dataInfo="pipelineGroup"
                    :mapping="mapping"
                    :item="item"
                    bgColor="white"
                    :isModifiable="true"
                    :alwaysShowPrompt="true"
                    :showAllMarkers="true"
                >
                </DisplayerPipeline>
            </div>
        </div>
    </div>
</template>

<script>

import { getBasicFormattedData } from '@/core/display/theStandardizer.js';

export default {
    name: 'EntityMarkers',
    components: {
    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        markerGroups() {
            return this.mapping.markerGroups;
        },
        formattedMarkerGroups() {
            return getBasicFormattedData(this.markerGroups, 'MARKERS');
        },
        grouped() {
            return _(this.formattedMarkerGroups).groupBy((group) => {
                return group.info.subType;
            }).value();
        },
        tagGroups() {
            return this.grouped.TAG;
        },
        pipelineGroups() {
            return this.grouped.PIPELINE;
        },
        statusGroups() {
            return this.grouped.STATUS;
        },
    },
    methods: {
        getItemMarkers(group) {
            const mappingMarkerGroup = _.find(this.mapping.markerGroups, ['group.id', group.id]);
            return this.item.markers[mappingMarkerGroup.id];
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-entity-markers {

} */

</style>
