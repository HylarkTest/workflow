<template>
    <div
        class="c-displayer-timekeeper-phase"
        :class="displayClasses"
        @click.exact="openFull"
        @click.alt="goToTab('TIMEKEEPER')"
    >
        <TimekeeperPhase
            :phase="dataValue || 'NO_STATUS'"
        >
        </TimekeeperPhase>

        <FullViewModal
            v-if="isModalOpen"
            :item="item"
            :page="page"
            defaultTab="TIMEKEEPER"
            @closeModal="closeModal"
        >
        </FullViewModal>
    </div>
</template>

<script>

import TimekeeperPhase from '@/components/timekeeper/TimekeeperPhase.vue';

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import interactsWithViewItemsDisplayers from '@/vue-mixins/displayers/interactsWithViewItemsDisplayers.js';

export default {
    name: 'DisplayerTimekeeperPhase',
    components: {
        TimekeeperPhase,
    },
    mixins: [
        interactsWithDisplayers,
        interactsWithViewItemsDisplayers,
    ],
    props: {
        dataValue: {
            type: [null, String],
            default: null,
        },
    },
    data() {
        return {
            typeKey: 'TIMEKEEPER_PHASE',
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

/*.c-displayer-timekeeper-phase {

} */

</style>
