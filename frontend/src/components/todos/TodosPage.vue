<template>
    <component
        :is="layoutComponent"
        v-model:currentView="currentView"
        class="o-todos-page"
        v-bind="layoutProps"
    >
        <FeatureMain
            v-if="!isLoading"
            :lists="todoLists"
            :featureStats="todoStats"
            :currentView="currentView"
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
import providesTodosPageEssentials from '@/vue-mixins/todos/providesTodosPageEssentials.js';

export default {
    name: 'TodosPage',
    components: {
    },
    mixins: [
        interactsWithFeaturePage,
        providesTodosPageEssentials,
    ],
    props: {
    },
    data() {
        return {
        };
    },
    computed: {
        featureType() {
            return 'TODOS';
        },
        historyPageType() {
            return 'Todo';
        },
    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>

/*.o-todos-page {
}*/

</style>
