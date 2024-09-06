<template>
    <div
        class="c-displayer-timekeeper-start text-xssm flex-inline items-center"
        :class="{ 'pointer-events-none': !isModifiable }"
        @click.exact="openFull"
        @click.alt="goToTab('TIMEKEEPER')"
    >
        <template v-if="dataValue">
            <i
                class="far fa-flag-checkered mr-2 text-cm-400"
            >
            </i>
            {{ dateTime }}
        </template>
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

import interactsWithDateDisplayers from '@/vue-mixins/displayers/interactsWithDateDisplayers.js';
import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import interactsWithViewItemsDisplayers from '@/vue-mixins/displayers/interactsWithViewItemsDisplayers.js';

export default {
    name: 'DisplayerTimekeeperStart',
    components: {

    },
    mixins: [
        interactsWithDateDisplayers,
        interactsWithDisplayers,
        interactsWithViewItemsDisplayers,
    ],
    props: {
        dataValue: {
            type: [String, null],
            default: null,
        },
    },
    data() {
        return {
            typeKey: 'TIMEKEEPER_START',
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

/*.c-displayer-timekeeper-start {

} */

</style>
