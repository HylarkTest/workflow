<template>
    <EditFoundation
        class="c-group-edit"
        :headerLabelPath="headerLabelPath"
        :tabs="validTabs"
        :selectedTab="selectedTab"
        :selectedTabHeader="selectedTabHeader"
        :selectedTabDescription="selectedTabDescription"
        @selectTab="selectTab"
    >
        <template #headerName>
            {{ group.name }}
        </template>

        <component
            :is="selectedComponent"
            :group="group"
            :groupType="groupType"
            :hideDescription="hideDescription"
            :hideColor="hideColor"
            bgClass="bg-cm-100"
            :itemDisplayComponent="itemDisplayComponent"
            :repository="repository"
        >
        </component>
    </EditFoundation>
</template>

<script>

import EditFoundation from './EditFoundation.vue';
import GroupEditUses from './GroupEditUses.vue';
import GroupEditGeneral from './GroupEditGeneral.vue';
import GroupEditItems from './GroupEditItems.vue';

import setsTabSelection from '@/vue-mixins/setsTabSelection.js';

export default {
    name: 'GroupEdit',
    components: {
        GroupEditUses,
        GroupEditGeneral,
        GroupEditItems,
        EditFoundation,
    },
    mixins: [
        setsTabSelection,
    ],
    props: {
        group: {
            type: Object,
            required: true,
        },
        groupType: {
            type: String,
            required: true,
        },
        defaultTab: {
            type: String,
            default: '',
        },
        hideDescription: Boolean,
        customTabs: {
            type: [Array, null],
            default: null,
        },
        hideColor: Boolean,
        itemDisplayComponent: {
            type: String,
            required: true,
        },
        repository: {
            type: Object,
            required: true,
        },
    },
    apollo: {
    },
    data() {
        const selected = this.defaultTab
            || (this.customTabs && this.customTabs[0])
            || 'GENERAL';
        return {
            selectedTab: selected,
            componentKey: 'GROUP_EDIT',
        };
    },
    computed: {
        tabs() {
            return [
                {
                    value: 'GENERAL',
                    name: 'General',
                },
                {
                    value: 'USES',
                    name: 'Uses',
                },
                {
                    value: 'ITEMS',
                    name: this.$t(this.headerLabelPath),
                },
            ];
        },
        validTabs() {
            if (this.customTabs) {
                return this.customTabs.map((tab) => {
                    return _.find(this.tabs, { value: tab });
                });
            }
            return this.tabs;
        },
        selectedTabHeader() {
            if (this.selectedTab === 'ITEMS') {
                return this.headerLabelPath;
            }
            return `common.${_.camelCase(this.selectedTab)}`;
        },
        selectedTabDescription() {
            if (['USES'].includes(this.selectedTab)) {
                return this[`${_.camelCase(this.selectedTab)}Description`];
            }
            return '';
        },
        usesDescription() {
            return {
                path: 'customizations.uses.description',
                args: { groupName: this.group.name },
            };
        },
        groupLabel() {
            return _.camelCase(this.groupType);
        },
        headerLabelPath() {
            return `customizations.${this.groupLabel}.name`;
        },
    },
    methods: {

    },
    created() {
    },
};
</script>

<style scoped>

/*.c-group-edit {

} */

</style>
