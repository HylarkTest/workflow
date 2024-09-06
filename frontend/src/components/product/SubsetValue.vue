<template>
    <div class="o-subset-value">
        <component
            v-if="markerFiltersLength"
            :is="markerComponent"
            :item="markerWithValue"
            size="sm"
        >
        </component>
        <div
            v-if="fieldFiltersLength"
            class="px-2 py-0.5 rounded"
            :class="bgColorClass"
        >
            {{ fieldValue }}
        </div>
    </div>
</template>

<script>

import TagDisplay from '@/components/customize/TagDisplay.vue';
import StatusDisplay from '@/components/customize/StatusDisplay.vue';
import StageDisplay from '@/components/customize/StageDisplay.vue';

import { getMarkerComponent } from '@/core/display/markerHelpers.js';

export default {
    name: 'SubsetValue',
    components: {
        TagDisplay,
        StatusDisplay,
        StageDisplay,
    },
    mixins: [
    ],
    props: {
        // Left as length because there is a ticket to have multiple filters
        // In which case knowing the length vs just a boolean might be helpful
        markerFiltersLength: {
            type: Number,
            default: 0,
        },
        fieldFiltersLength: {
            type: Number,
            default: 0,
        },
        markerWithValue: {
            type: [Object, null],
            default: null,
        },
        markerGroupWithValue: {
            type: [Object, null],
            default: null,
        },
        fieldValue: {
            type: String,
            default: '',
        },
        bgColorClass: {
            type: String,
            default: 'bg-cm-100',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        markerComponent() {
            return getMarkerComponent(this.markerGroupWithValue?.type) || '';
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.o-subset-value {

} */

</style>
