<template>
    <div class="o-timekeeper-stats-project">
        <CircleGraph
            :graphValues="projects"
            mode="PIE"
        >
            <template
                #header
            >
                Project stages
            </template>

            <template
                #extra
            >
                <div class="self-end flex mb-1 w-full">
                    <DropdownBox
                        v-model="selectedProject"
                        class="w-full"
                        boxShape="oval"
                        size="sm"
                        :options="projectOptions"
                        :displayRule="projectsDisplay"
                        :inlineLabel="inlineLabel"
                    >
                    </DropdownBox>
                </div>
            </template>
        </CircleGraph>
    </div>
</template>

<script>

import CircleGraph from '@/components/graphs/CircleGraph.vue';

const projects = [
    {
        count: 1,
        val: 'WAITING',
        color: {
            hue: 'turquoise',
            intensity: '600',
        },
        textPath: 'timekeeper.statuses.waiting',
    },
    {
        count: 1,
        val: 'STARTED',
        color: {
            hue: 'sky',
            intensity: '600',
        },
        textPath: 'timekeeper.statuses.started',
    },
    {
        count: 1,
        val: 'IC',
        color: {
            hue: 'violet',
            intensity: '200',
        },
        text: 'Stage 1',
    },
    {
        count: 1,
        val: 'DE',
        color: {
            hue: 'violet',
            intensity: '300',
        },
        text: 'Stage 2',
    },
    {
        count: 1,
        val: 'PR',
        color: {
            hue: 'violet',
            intensity: '400',
        },
        text: 'Stage 3',
    },
    {
        count: 2,
        val: 'RE',
        color: {
            hue: 'violet',
            intensity: '500',
        },
        text: 'Stage 4',
    },
];

export default {
    name: 'TimekeeperStatsProject',
    components: {
        CircleGraph,
    },
    mixins: [
    ],
    props: {

    },
    data() {
        return {
            inlineLabel: {
                text: 'Project type',
                position: 'beside',
                textClass: 'whitespace-nowrap text-xssm font-semibold text-cm-500',
            },
            selectedProject: null,

        };
    },
    computed: {
        projectOptions() {
            return [
                {
                    value: 1,
                    name: 'Design projects',
                },
                {
                    value: 2,
                    name: 'Content writing',
                },
            ];
        },
    },
    methods: {

    },
    created() {
        this.selectedProject = this.projectOptions[0];
        this.projects = projects;
        this.projectsDisplay = (option) => option.name;
    },
};
</script>

<style scoped>

/*.o-timekeeper-stats-project {

} */

</style>
