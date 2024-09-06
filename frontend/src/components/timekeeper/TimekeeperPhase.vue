<template>
    <div
        class="o-timekeeper-phase text-xssm sm:text-sm"
        :class="[phaseText, phaseBg]"
    >
        <div
            v-t="statNamePath"
        >
        </div>
    </div>
</template>

<script>

import { getPhaseInfo } from '@/core/display/timekeeperThings.js';
import providesColors from '@/vue-mixins/style/providesColors.js';

export default {
    name: 'TimekeeperPhase',
    components: {

    },
    mixins: [
        providesColors,
    ],
    props: {
        phase: {
            type: String,
            default: 'NO_STATUS',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        phaseInfo() {
            return getPhaseInfo(this.phase);
        },
        phaseColor() {
            return this.phaseInfo?.color;
        },
        phaseText() {
            return this.getTextColor(this.phaseColor, '600');
        },
        phaseBg() {
            return this.getBgColor(this.phaseColor, '100');
        },
        statNamePath() {
            return `timekeeper.phases.${_.camelCase(this.phase)}`;
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

.o-timekeeper-phase {
    @apply
        font-bold
        inline-block
        px-3
        py-2
        rounded-lg
    ;
}

</style>
