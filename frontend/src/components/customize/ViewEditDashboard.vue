<!-- Keep but delete if just using ViewEditModal.vue
 -->
<template>
    <div class="o-view-edit-dashboard">
        <EditFoundation
            :hideHeader="true"
            :hideTabs="true"
            :tabs="tabs"
            :selectedTab="selectedTab"
            :selectedTabHeader="selectedTab.name"
        >
        </EditFoundation>
        Edit and add a new dashboard view
    </div>
</template>

<script>

import EditFoundation from './EditFoundation.vue';

import setsTabSelection from '@/vue-mixins/setsTabSelection.js';

const tabs = [
    {
        value: 'GENERAL',
        name: 'General',
    },
    {
        value: 'USES',
        name: 'Uses',
    },
];

export default {
    name: 'ViewEditDashboard',
    components: {
        EditFoundation,
    },
    mixins: [
        setsTabSelection,
    ],
    props: {

    },
    data() {
        return {
            selectedTab: 'GENERAL',
        };
    },
    computed: {

    },
    methods: {

    },
    created() {
        this.tabs = tabs;
    },
};
</script>

<style scoped>

/*.o-view-edit-dashboard {

} */

</style>
