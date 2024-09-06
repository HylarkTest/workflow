<template>
    <div class="o-timekeeper-main">
        <TimekeeperStats
            v-if="stats"
            class="mb-10"
            :stats="stats"
        >
        </TimekeeperStats>

        <TimekeeperDeadlines
            :page="page"
        >
        </TimekeeperDeadlines>

        <!-- <div
            class="h-full overflow-y-auto"
        >
            <TimekeeperPulse>
            </TimekeeperPulse>
        </div> -->
    </div>
</template>

<script>

import TimekeeperStats from './TimekeeperStats.vue';
// import TimekeeperPulse from './TimekeeperPulse.vue';
import TimekeeperDeadlines from './TimekeeperDeadlines.vue';

export default {
    name: 'TimekeeperMain',
    components: {
        TimekeeperStats,
        TimekeeperDeadlines,
        // TimekeeperPulse,
    },
    mixins: [
    ],
    props: {
        stats: {
            type: [Object, null],
            default: null,
        },
        page: {
            type: [Object, null],
            default: null,
        },
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

/*.o-timekeeper-main {

} */

</style>
