<template>
    <component
        :is="layoutComponent"
        v-model:currentView="currentView"
        class="o-calendar-page"
        customHeaderPath="features.calendar.title"
        v-bind="layoutProps"
    >
        <FeatureMain
            v-if="!isLoading"
            :lists="calendars"
            :currentView="currentView"
            :responsiveBreakpoint="900"
            :integrations="integrationsForScope"
            :integrationLists="integrationLists"
            :renewals="renewals"
            v-bind="featureMainProps"
        >
        </FeatureMain>
    </component>
</template>

<script>

import interactsWithFeaturePage from '@/vue-mixins/features/interactsWithFeaturePage.js';
import providesEventsPageEssentials from '@/vue-mixins/events/providesEventsPageEssentials.js';

export default {
    name: 'CalendarPage',
    components: {
    },
    mixins: [
        interactsWithFeaturePage,
        providesEventsPageEssentials,
    ],
    props: {

    },
    data() {
        return {
            currentView: {
                id: 'CALENDAR',
                viewType: 'CALENDAR',
                categoryType: 'DASHBOARD',
            },
        };
    },
    computed: {
        featureType() {
            return 'EVENTS';
        },
        historyPageType() {
            return 'Event';
        },
    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>

/*.o-calendar-page {
}*/

</style>
