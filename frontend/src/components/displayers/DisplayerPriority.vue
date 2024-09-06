<template>
    <div
        class="c-displayer-priority"
    >
        <PriorityFlag
            :isModifiable="isModifiable"
            :priority="dataValue || 0"
            @selectPriority="setPriority"
        >
            <template #default="{ priority, priorityColor }">
                <div
                    class="bg-cm-00"
                    :class="[containerClass, colorClass(priorityColor)]"
                >
                    <i
                        class="fa-flag"
                        :class="[iconClass(priority), flagClass]"
                    >
                    </i>
                </div>
            </template>
        </PriorityFlag>
    </div>
</template>

<script>

import PriorityFlag from '@/components/assets/PriorityFlag.vue';

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import providesColors from '@/vue-mixins/style/providesColors.js';

const flagCombos = {
    1: '',
    2: '',
    3: 'text-cm-00',
};

const containerCombos = {
    1: 'h-8 w-8 circle-center border border-dashed text-sm',
    2: '',
    3: 'h-8 w-8 circle-center',
};

export default {
    name: 'DisplayerPriority',
    components: {
        PriorityFlag,
    },
    mixins: [
        interactsWithDisplayers,
        providesColors,
    ],
    props: {
        dataValue: {
            type: Number,
            default: 0,
        },
    },
    data() {
        return {
            typeKey: 'PRIORITY',
        };
    },
    computed: {
        flagClass() {
            return flagCombos[this.combo];
        },
        containerClass() {
            return containerCombos[this.combo];
        },
    },
    methods: {
        setPriority(priority) {
            this.updateDisplayerValue('priority', priority);
        },
        iconClass(priority) {
            return priority ? 'fas' : 'far';
        },
        colorClass(color) {
            const intensity = color === 'gray' ? '300' : '600';
            if (this.combo === 3) {
                return this.getBgColor(color, intensity);
            }
            if (this.combo === 1) {
                return this.getBorderColor(color, intensity);
            }
            return '';
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-priority {

} */

</style>
