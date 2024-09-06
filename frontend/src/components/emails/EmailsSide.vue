<template>
    <div class="o-emails-side">
        <FeatureSide
            :filterables="filterables"
            :activeFilters="activeFilters"
            :basicFilters="basicFilters"
            :getFilterCount="randomFunction"
            :bases="bases"
            :sources="sources"
            :hideColorSquare="true"
            :hideAllLineOptions="true"
            :hideBasicFilters="true"
            :hideAddList="true"
            :stopReordering="true"
            countColorClasses="text-primary-600"
            v-bind="$attrs"
            @update:activeFilters="$emit('update:activeFilters', $event)"
        >

            <template
                #listTitle
            >
                Mailboxes
            </template>
        </FeatureSide>
    </div>
</template>

<script>

import FeatureSide from '@/components/features/FeatureSide.vue';

import interactsWithFeatureSide from '@/vue-mixins/features/interactsWithFeaturesSide.js';

export default {
    name: 'EmailsSide',
    components: {
        FeatureSide,
    },
    mixins: [
        interactsWithFeatureSide,
    ],
    props: {

    },
    emits: [
        'update:activeFilters',
    ],
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

/*.o-emails-side {

} */

</style>
