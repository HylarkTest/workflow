<template>
    <div class="o-timekeeper-pulse">

        <div class="mb-16">
            <TimekeeperStatsOpen>
            </TimekeeperStatsOpen>
        </div>

        <div class="mb-16">
            <TimekeeperStatsProject>
            </TimekeeperStatsProject>
        </div>

        <div class="">
            <TimekeeperStatsOutcomes>
            </TimekeeperStatsOutcomes>
        </div>

    </div>
</template>

<script>

import TimekeeperStatsOutcomes from './TimekeeperStatsOutcomes.vue';
import TimekeeperStatsOpen from './TimekeeperStatsOpen.vue';
import TimekeeperStatsProject from './TimekeeperStatsProject.vue';

export default {
    name: 'TimekeeperPulse',
    components: {
        TimekeeperStatsOutcomes,
        TimekeeperStatsOpen,
        TimekeeperStatsProject,
    },
    mixins: [
    ],
    props: {

    },
    data() {
        return {

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

.o-timekeeper-pulse {
    width: 280px;

    @apply
        bg-primary-100
        p-8
        rounded-xl
    ;
}

</style>
