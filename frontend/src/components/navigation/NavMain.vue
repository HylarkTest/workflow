<template>
    <div class="o-nav-main">
        <template
            v-if="!isFirstLoad"
        >
            <NavDesktop
                class="hidden md:block"
                :extras="validExtras"
                :links="links"
                :pages="pages"
                :spaces="spaces"
                :icons="icons"
                :isSupportOpen="isSupportOpen"
                @openFinderModal="openFinderModal"
                @openSupportModal="openSupportModal"
            >
            </NavDesktop>

            <NavSmaller
                class="md:hidden"
                :extras="validExtras"
                :links="links"
                :pages="pages"
                :spaces="spaces"
                :icons="icons"
                :isSupportOpen="isSupportOpen"
                @openFinderModal="openFinderModal"
                @openSupportModal="openSupportModal"
            >
            </NavSmaller>
        </template>

        <SupportOptions
            v-if="isSupportOpen"
            @closeModal="closeSupportModal"
        >
        </SupportOptions>

        <Modal
            v-if="isModalOpen"
            containerClass="p-2 sm:p-4 md:p-8 w-full sm:w-4/5 md:w-1/2"
            positioning="TOP"
            @closeModal="closeModal"
        >
            <FinderScreen
                @closeModal="closeModal"
            >
            </FinderScreen>
        </Modal>
    </div>
</template>

<script>

import LINKS from '@/graphql/Links.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

import NavDesktop from '@/components/navigation/NavDesktop.vue';
import NavSmaller from '@/components/navigation/NavSmaller.vue';
import SupportOptions from '@/components/support/SupportOptions.vue';
import FinderScreen from '@/components/finder/FinderScreen.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import { featurePages } from '@/core/display/typenamesList.js';

const main = [
    {
        val: 'HOME',
        link: 'home',
        icon: 'fa-home-lg',
        __typename: 'Home',
    },
    featurePages.TODOS,
    featurePages.CALENDAR,
];

export default {
    name: 'NavMain',
    components: {
        NavDesktop,
        NavSmaller,
        FinderScreen,
        SupportOptions,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {

    },
    apollo: {
        links: {
            query: LINKS,
            update: initializeConnections,
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
            isSupportOpen: false,
        };
    },
    computed: {
        isLoading() {
            return this.$apollo.queries.links.loading;
        },
        isFirstLoad() {
            return !this.links && this.isLoading;
        },

        // Main links
        spaces() {
            return this.links?.spaces;
        },
        pages() {
            return _(this.spaces).flatMap((link) => {
                return link.pages;
            }).value();
        },
        shortcuts() {
            return this.$root.authenticatedUser?.baseSpecificPreferences().shortcuts;
        },
        displayedShortcuts() {
            return this.shortcuts?.length
                ? [main[0], ...this.shortcuts]
                : [...main, ...this.pages.slice(0, 3)];
        },
        icons() {
            if (this.links && this.displayedShortcuts?.length) {
                const shortcutLinks = _(this.displayedShortcuts).map((shortcut) => {
                    if (shortcut?.__typename !== 'Shortcut') {
                        return shortcut;
                    }
                    if (shortcut.type === 'PAGE') {
                        return _.find(this.pages, ['id', shortcut.id]);
                    }
                    const id = _.upperSnake(shortcut.id);
                    return _.find(featurePages, ['val', id]);
                }).compact().value();

                return [
                    ...shortcutLinks,
                ];
            }
            return [];
        },

        // Additional links
        extras() {
            return [
                {
                    val: 'NOTIFICATIONS',
                    icon: 'fa-bell',
                    link: 'notifications',
                    alertCircle: !!this.$root.authenticatedUser?.newNotificationsCount,
                },
                {
                    val: 'SUPPORT',
                    action: 'openSupportModal',
                    icon: 'fa-question-circle',
                    component: 'ButtonEl',
                },
                // {
                //     val: 'SIGN_OUT',
                //     icon: 'fa-sign-out',
                //     action: 'signOut',
                //     langPath: 'common.signOut',
                //     component: 'ButtonEl',
                // },
            ];
        },
        validExtras() {
            return this.extras.filter((extra) => {
                if (_.has(extra, 'condition')) {
                    return extra.condition;
                }
                return true;
            });
        },
    },
    methods: {
        openFinderModal() {
            this.openModal();
        },
        openSupportModal() {
            this.isSupportOpen = true;
        },
        closeSupportModal() {
            this.isSupportOpen = false;
        },
    },
    watch: {
        '$route.params.baseId': function onBaseChange() {
            this.$apollo.queries.links.refresh();
        },
    },
    created() {
        this.main = main;
    },
};
</script>

<style scoped>

/*.o-nav-main {
}*/

</style>
