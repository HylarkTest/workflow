<template>
    <div class="o-timekeeper-stats-outcomes">
        <HalfCircleGraph
            :graphValues="outcomes"
        >
        </HalfCircleGraph>
    </div>
</template>

<script>

import HalfCircleGraph from '@/components/graphs/HalfCircleGraph.vue';

const outcomes = [
    {
        count: 5,
        val: 'WAITING',
        color: {
            hue: 'turquoise',
            intensity: '600',
        },
        textPath: 'timekeeper.statuses.waiting',
    },
    {
        count: 5,
        val: 'OPEN',
        color: {
            hue: 'gray',
            intensity: '300',
        },
        textPath: 'timekeeper.statuses.open',
    },
    {
        count: 9,
        val: 'COMPLETED',
        color: {
            hue: 'emerald',
            intensity: '700',
        },
        textPath: 'timekeeper.statuses.completed',
    },
    {
        count: 2,
        val: 'ABANDONED',
        color: {
            hue: 'gray',
            intensity: '700',
        },
        textPath: 'timekeeper.statuses.abandoned',
    },
];

export default {
    name: 'TimekeeperStatsOutcomes',
    components: {
        HalfCircleGraph,
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
        this.outcomes = outcomes;
    },
};
</script>

<style scoped>

/*.o-timekeeper-stats-outcomes {

} */

</style>
