<template>
    <div class="o-deadline-item feature feature__item--style">
        <!-- {{ deadline }} -->

        <div class="flex items-center">
            <div class="mr-6">
                <IconsComposition
                    sizeClass="w-12 h-12 text-lg"
                    :iconsComposition="icons"
                    showRemainingIcons
                >
                </IconsComposition>
            </div>

            <div>
                <ConnectedRecord
                    :item="fullObj"
                    :isMinimized="true"
                >
                </ConnectedRecord>

                <div
                    v-if="startAt"
                    class="flex items-center mt-0.5 text-xssm"
                >
                    <i
                        class="fa-light fa-flag-checkered text-cm-400 mr-1.5"
                    >
                    </i>
                    <div class="text-cm-600">
                        {{ startFormatted }}
                    </div>

                    <div
                        v-if="isOpen"
                        class="ml-2.5 bg-primary-200 text-primary-700 rounded-md px-1 text-xs"
                        title="Duration active"
                    >
                        {{ daysOpen }}
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end">

            <TimekeeperPhase
                v-if="phase"
                :phase="phase"
            >
            </TimekeeperPhase>

            <div
                class="o-deadline-item__due"
            >
                <template
                    v-if="dueBy"
                >
                    <div class="text-lg font-bold text-primary-800 leading-none">
                        {{ dueObj.format('D') }}
                    </div>
                    <div>
                        {{ dueObj.format('MMM') }}
                    </div>
                </template>
            </div>

            <button
                class="ml-4 inline-flex shrink-0"
                type="button"
                @click="completeItem"
            >
                <div
                    class="button bg-emerald-600 hover:bg-emerald-500 text-cm-00 shadow-lg shadow-emerald-600/20"
                >
                    <i
                        class="far fa-circle-check mr-1"
                    >
                    </i>
                    Done
                </div>
            </button>
        </div>
    </div>
</template>

<script>

// import IconButton from '@/components/buttons/IconButton.vue';

import TimekeeperPhase from './TimekeeperPhase.vue';
import IconsComposition from '@/components/assets/IconsComposition.vue';

import sortsOutTypes from '@/vue-mixins/sortsOutTypes.js';
import { completeItem } from '@/core/repositories/itemRepository.js';

export default {
    name: 'DeadlineItem',
    components: {
        // IconButton,
        IconsComposition,
        TimekeeperPhase,
    },
    mixins: [
        sortsOutTypes,
    ],
    props: {
        deadline: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        // For sortsOutTypes mixin
        fullObj() {
            return this.deadline;
        },

        startAt() {
            return this.deadline.deadlines?.startAt;
        },
        startObj() {
            return this.$dayjs(this.startAt);
        },
        startFormatted() {
            return this.startObj.format('ll');
        },
        dueBy() {
            return this.deadline.deadlines?.dueBy;
        },
        dueObj() {
            return this.$dayjs(this.dueBy);
        },
        phase() {
            return this.deadline.deadlines.status;
        },
        isOpen() {
            return this.startObj.isBefore(this.$dayjs());
        },
        daysOpen() {
            return this.$dayjs().to(this.startObj, true);
        },
    },
    methods: {
        completeItem() {
            completeItem(this.deadline, this.mapping);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-deadline-item {
    @apply
        flex
        flex-col
        px-4
        py-2
        rounded-xl
        text-sm
    ;

    @media (min-width: 768px) {
        @apply
            flex-row
            items-center
            justify-between
        ;
    }

    &__due {
        width: 50px;

        @apply
            ml-6
            relative
            text-center
        ;
    }
}

</style>
