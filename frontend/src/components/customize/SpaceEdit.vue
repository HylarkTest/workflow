<template>
    <EditFoundation
        class="o-space-edit"
        headerLabelPath="common.space"
        :tabs="tabs"
        :selectedTab="selectedTab"
        :selectedTabHeader="selectedTabHeader"
        @selectTab="selectTab"
    >
        <template #headerName>
            {{ space.name }}
        </template>

        <component
            :is="selectedComponent"
            :space="space"
            @closeModal="$emit('closeModal')"
        >
        </component>
    </EditFoundation>
</template>

<script>

import EditFoundation from './EditFoundation.vue';
import SpacePages from './SpacePages.vue';
import SpaceGeneral from './SpaceGeneral.vue';

import setsTabSelection from '@/vue-mixins/setsTabSelection.js';

const tabs = [
    {
        value: 'GENERAL',
        name: 'General',
    },
    {
        value: 'PAGES',
        name: 'Pages',
    },

];

export default {
    name: 'SpaceEdit',
    components: {
        SpacePages,
        SpaceGeneral,
        EditFoundation,
    },
    mixins: [
        setsTabSelection,
    ],
    props: {
        space: {
            type: Object,
            required: true,
        },
        defaultTab: {
            type: String,
            default: '',
        },
    },
    emits: [
        'closeModal',
    ],
    data() {
        return {
            selectedTab: this.defaultTab || tabs[0].value,
            componentKey: 'SPACE',
        };
    },
    computed: {
        selectedTabHeader() {
            return `common.${_.camelCase(this.selectedTab)}`;
        },
    },
    methods: {

    },
    created() {
        this.tabs = tabs;
    },
};
</script>

<style scoped>

/*.o-space-edit {

} */

</style>
