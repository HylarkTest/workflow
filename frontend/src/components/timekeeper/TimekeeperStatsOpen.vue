<template>
    <div class="o-timekeeper-stats-open">
        <CircleGraph
            :graphValues="open"
            :showInnerCircle="true"
        >

            <template
                #header
            >
                Open projects
            </template>
        </CircleGraph>
    </div>
</template>

<script>

import CircleGraph from '@/components/graphs/CircleGraph.vue';

const open = [
    {
        count: 7,
        val: 'BEHIND',
        color: {
            hue: 'rose',
            intensity: '600',
        },
        textPath: 'timekeeper.statuses.behind',
    },
    {
        count: 0,
        val: 'HOLD',
        color: {
            hue: 'gold',
            intensity: '600',
        },
        textPath: 'timekeeper.statuses.hold',
    },
    {
        count: 5,
        val: 'ON_TIME',
        color: {
            hue: 'green',
            intensity: '700',
        },
        textPath: 'timekeeper.statuses.onTime',
    },
];

export default {
    name: 'TimekeeperStatsOpen',
    components: {
        CircleGraph,
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
        this.open = open;
    },
};
</script>

<style scoped>

/*.o-timekeeper-stats-open {

} */

</style>
