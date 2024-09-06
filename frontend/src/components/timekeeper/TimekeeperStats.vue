<template>
    <div class="o-timekeeper-stats">

        <!-- <div class="mb-6">
            <div class="rounded-xl">
                <BarGraph
                    :yAxisLabel="false"
                    :xAxisLabel="false"
                    xValType="WEEKLY_RANGE"
                    :graphValues="weeksAheadValues"
                >
                    <template
                        #header
                    >
                        Your upcoming deadlines
                    </template>
                </BarGraph>
            </div>
        </div>
 -->
        <div class="o-timekeeper-stats__boxes grid gap-5">
            <div
                v-for="stat in statsList"
                :key="stat.id"
                class="o-timekeeper-stats__box shadow-primary-700/20"
            >
                <i
                    class="fa-duotone text-lg mb-2"
                    :class="[stat.icon, duotoneClass(stat)]"
                    :style="duotoneStyle(stat)"
                >
                </i>

                <div class="text-3xl font-semibold text-primary-900">
                    {{ stats[stat.id] }}
                </div>

                <div
                    v-t="statNamePath(stat.id)"
                    class="font-medium text-xs mt-1 text-cm-500"
                >

                </div>
            </div>
        </div>
    </div>
</template>

<script>

// import BarGraph from '@/components/graphs/BarGraph.vue';

import providesColors from '@/vue-mixins/style/providesColors.js';

const statsList = [
    {
        id: 'OPEN',
        icon: 'fa-flag-checkered',
        dtCustom: {
            primary: 'sky',
            secondary: 'gray',
            secondaryIntensity: '00',
        },
    },
    {
        id: 'ACTIVE',
        icon: 'fa-chart-line',
        dtCustom: {
            primary: 'violet',
            primaryIntensity: '600',
            secondary: 'gray',
            secondaryIntensity: '300',
        },
    },
    {
        id: 'WAITING_TO_START',
        icon: 'fa-hourglass-clock',
        dtCustom: {
            primary: 'gray',
            primaryIntensity: '300',
            secondary: 'gold',
            secondaryIntensity: '600',
        },
    },
    // {
    //     id: 'HOLD',
    //     icon: 'fa-circle-pause',
    //     dtCustom: {
    //         primary: 'gray',
    //         primaryIntensity: '00',
    //         secondary: 'gold',
    //         secondaryIntensity: '600',
    //     },
    // },
    {
        id: 'COMPLETED',
        icon: 'fa-circle-check',
        dtCustom: {
            primary: 'gray',
            primaryIntensity: '00',
            secondary: 'emerald',
            secondaryIntensity: '600',
        },
    },
    {
        id: 'OVERDUE',
        icon: 'fa-alarm-exclamation',
        dtCustom: {
            primary: 'gray',
            primaryIntensity: '300',
            secondary: 'rose',
            secondaryIntensity: '600',
        },
    },
];

// Left commented for data structure
// const weeksAheadValues = [
//     {
//         xVal: '2022/08/22',
//         yVal: 2,
//     },
// ];

export default {
    name: 'TimekeeperStats',
    components: {
        // BarGraph,
    },
    mixins: [
        providesColors,
    ],
    props: {
        stats: {
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
        statNamePath(id) {
            return `timekeeper.phases.${_.camelCase(id)}`;
        },
        duotoneStyle(stat) {
            const dt = stat.dtCustom;
            if (dt) {
                return {
                    '--fa-primary-color': this.getColorHex(dt.primary, dt.primaryIntensity),
                    '--fa-secondary-color': this.getColorHex(dt.secondary, dt.secondaryIntensity),
                };
            }
            return this.duotoneColors(this.accentColor);
        },
        duotoneClass(stat) {
            if (stat.dtCustom) {
                return `o-timekeeper-stats__box--${_.camelCase(stat.id)}`;
            }
            return '';
        },
    },
    created() {
        this.statsList = statsList;
        // this.weeksAheadValues = weeksAheadValues;
    },
};
</script>

<style scoped>

.o-timekeeper-stats {
    &__boxes {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        justify-content: center;
    }

    &__box {
        @apply
            bg-cm-00
            px-4
            py-3
            rounded-xl
            shadow-lg
        ;
    }
}

</style>
