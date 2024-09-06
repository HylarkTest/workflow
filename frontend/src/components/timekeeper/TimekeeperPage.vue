<template>
    <FeaturePage
        class="o-timekeeper-page"
        :isLoading="isLoading"
        backgroundStyle="FEATURE"
        :featureType="featureType"
        historyPageType=""
    >
        <TimekeeperMain
            :stats="timekeeperStats"
            :page="page"
        >
        </TimekeeperMain>

    </FeaturePage>
</template>

<script>

import { gql } from '@apollo/client';
import TimekeeperMain from './TimekeeperMain.vue';
import FeaturePage from '@/components/features/FeaturePage.vue';
// import ViewsSelection from '@/components/design/ViewsSelection.vue';

export default {
    name: 'TimekeeperPage',
    components: {
        FeaturePage,
        // ViewsSelection,
        TimekeeperMain,
    },
    mixins: [
    ],
    props: {
        page: {
            type: Object,
            default: null,
        },
    },
    apollo: {
        timekeeperStats: {
            query: gql`
                query TimeKeeperStats($forMapping: ID) {
                    timekeeperStats(forMapping: $forMapping) {
                        OPEN: open
                        ACTIVE: active
                        WAITING_TO_START: waitingToStart
                        OVERDUE: overdue
                        COMPLETED: completed
                    }
                }
            `,
            variables() {
                return this.page?.mapping ? {
                    forMapping: this.page.mapping.id,
                } : {};
            },
            fetchPolicy: 'network-only',
        },
    },
    data() {
        return {
            // currentView: { id: 'LINE', viewType: 'LINE', categoryType: 'DASHBOARD' },
        };
    },
    computed: {
        featureType() {
            return 'TIMEKEEPER';
        },
        isLoading() {
            return this.$apollo.queries.timekeeperStats.loading;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.o-timekeeper-page {

} */

</style>
