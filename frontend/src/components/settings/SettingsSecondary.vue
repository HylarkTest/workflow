<template>
    <div class="o-settings-secondary">
        <BasicTabs
            :tabs="whichTabs"
            direction="column"
            :router="true"
        >
            <template
                #item="{ tab }"
            >
                <RouterLink
                    :to="{ name: tab.value, params: tab.params || {} }"
                    class="o-settings-secondary__tab"
                    :class="{ 'bg-primary-500': isSelectedTab(tab.value) }"
                >
                    <i
                        class="fa-fw mb-1 text-2xl text-center text-cm-00"
                        :class="tab.icon"
                    >
                    </i>
                    <h6
                        class="font-semibold text-f4 text-xs"
                    >
                        {{ tab.name }}
                    </h6>
                </RouterLink>
            </template>
        </BasicTabs>
    </div>
</template>

<script>

import setsTabSelection from '../../vue-mixins/setsTabSelection.js';
import BasicTabs from '@/components/tabs/BasicTabs.vue';

import { matchedMeta } from '@/core/routerUtils.js';

const tabs = [
    {
        value: 'settings.*.pages',
        name: 'Pages',
        icon: 'fal fa-columns',
        relevance: ['personal', 'teams', 'invited'],
    },
    {
        value: 'settings.*.data',
        name: 'Data',
        icon: 'fal fa-window-alt',
        relevance: ['personal', 'teams'],
    },
    {
        value: 'settings.*.profile',
        name: 'Profile',
        icon: 'fal fa-address-card',
        relevance: ['personal', 'teams', 'invited'],
    },
    {
        value: 'settings.*.team',
        name: 'Team',
        icon: 'fal fa-users',
        relevance: ['teams'],
    },
    {
        value: 'settings.*.theme',
        name: 'Theme',
        icon: 'fal fa-palette',
        relevance: ['personal', 'teams'],
    },
    {
        value: 'settings.*.spaces',
        name: 'Spaces',
        icon: 'fal fa-spade',
        relevance: ['invited', 'teams'],
        // condition: only if the user was invited to a space
    },
    {
        value: 'settings.*.upgrade',
        name: 'Upgrade',
        icon: 'fal fa-user-plus',
        // relevance: ['personal', 'teams'],
        relevance: ['teams'],
    },
];

export default {
    name: 'SettingsSecondary',
    components: {
        BasicTabs,
    },
    mixins: [
        setsTabSelection,
    ],
    props: {
    },
    data() {
        return {

        };
    },
    computed: {
        selectedTab() {
            return this.$route.name;
        },
        section() {
            return matchedMeta(this.$route, 'section');
        },
        whichTabs() {
            return this[`${this.section}Tabs`];
        },
        invitedTabs() {
            return this.checkIncludes('invited');
        },
        teamsTabs() {
            return this.checkIncludes('teams');
        },
        personalTabs() {
            return this.checkIncludes('personal');
        },
    },
    methods: {
        checkIncludes(section) {
            return tabs.filter((tab) => {
                return tab.relevance.includes(section) && (!_(tab).has('condition') || tab.condition);
            }).map((tab) => {
                return {
                    ..._.omit(tab, 'value'),
                    value: tab.value.replace('*', section),
                };
            });
        },
        isSelectedTab(tabValue) {
            return this.selectedTab.includes(tabValue);
        },
    },
    created() {
        this.tabs = tabs;
    },
};
</script>

<style scoped>
.o-settings-secondary {

    @apply
        bg-primary-600
        flex
        flex-col
        items-center
        px-2
        py-10
    ;

    &__tab {
        min-width: 68px;

        @apply
            border
            border-solid
            border-transparent
            cursor-pointer
            flex
            flex-col
            items-center
            px-2
            py-3
            rounded
            w-full
        ;

        &:hover {
            @apply
                border-primary-500
            ;
        }
    }
}
</style>
