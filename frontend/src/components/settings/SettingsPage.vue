<template>
    <div
        v-if="!showMainLoader"
        class="o-settings-page relative footer-spacing--bottom"
    >
        <div class="fixed top-2 left-2 bg-primary-200 hover:bg-primary-100 p-2 rounded-lg z-alert md:hidden">
            <LandingBurger
                :isActive="menuOpen"
                @click="menuOpen = !menuOpen"
            >
            </LandingBurger>
        </div>

        <BackRounded
            class="fixed top-2 right-2 md:right-auto md:left-2 block z-over"
            colorClasses="text-primary-600 bg-primary-100 hover:text-cm-00 hover:bg-primary-600"
            :buttonTextPath="backButtonText"
            @click="goBack"
        >
        </BackRounded>

        <div
            class="o-settings-page__side md:block"
            :class="menuOpen ? 'o-settings-page__side--resp' : 'hidden'"
        >

            <div
                class="mb-6 pb-6 border-b border-solid border-cm-300 max-w-full min-w-0"
            >
                <ProfileNameImage
                    :vertical="true"
                    size="lg"
                    :profile="user"
                    :showEmail="true"
                >
                </ProfileNameImage>
            </div>

            <div class="flex mb-6 text-cm-700 text-xl items-center">
                <i class="far fa-cog mr-2"></i>
                <h4
                    v-t="'links.settings'"
                    class="font-semibold"
                >
                </h4>
            </div>

            <BasicVertical
                :tabs="tabs"
                :selectedTab="selectedTab"
                :router="true"
                paramKey="baseId"
                @selectTab="menuOpen = false"
            >
                <template
                    #headerImage="{ tab }"
                >
                    <ProfileNameImage
                        v-if="tab.imageObj"
                        class="mr-2 shrink-0"
                        hideFullName
                        size="xs"
                        :profile="tab.imageObj"
                    >
                    </ProfileNameImage>
                </template>

                <template
                    v-if="allowNewBase"
                    #afterTab
                >
                    <RouterLink
                        :to="{ name: 'newBase' }"
                        class="o-settings-page__base text-center"
                    >
                        Create a collaborative base
                    </RouterLink>

                </template>
            </BasicVertical>
        </div>

        <div class="flex-1 p-12 md:p-10">
            <div
                v-if="hasBase"
                class="flex items-center mb-5"
            >
                <ProfileNameImage
                    class="mr-3"
                    :profile="currentBaseDisplay"
                    hideFullName
                >
                </ProfileNameImage>

                <p
                    class="font-semibold text-cm-400 text-lg"
                >
                    {{ activeBase.name }}
                </p>
            </div>
            <h1
                v-if="selectedTabPath"
                v-t="selectedTabPath"
                class="text-4xl font-bold mb-6"
            >
            </h1>
            <RouterView
                :key="routerKey"
                :user="user"
            >
            </RouterView>
        </div>
    </div>
</template>

<script>

import BasicVertical from '../tabs/BasicVertical.vue';
import LandingBurger from '@/components/landing/LandingBurger.vue';

import { getInitials } from '@/core/utils.js';

// import LINKS from '@/graphql/Links.gql';
import SPACES from '@/graphql/spaces/queries/Spaces.gql';
// import TEAMS from '@/graphql/teams/queries/Teams.gql';
// import SHARED_DOMAINS from '@/graphql/shared-domains/queries/SharedDomains.gql';

import { matchedMeta } from '@/core/routerUtils.js';
import { maxBases } from '@/core/data/bases.js';
// import SettingsSecondary from './SettingsSecondary.vue';
import BackRounded from '@/components/buttons/BackRounded.vue';
import ProfileNameImage from '@/components/profile/ProfileNameImage.vue';

// import providesBackLinks from '@/vue-mixins/providesBackLinks.js';

const generalTab = (base) => ({
    namePath: 'links.general',
    link: 'settings.general',
    value: 'general',
    params: { baseId: base.id },
});

const integrationsTab = (base) => ({
    namePath: 'links.integrations',
    link: 'settings.integrations',
    value: 'integrations',
    params: { baseId: base.id },
});

const plansTab = (base) => ({
    namePath: 'links.plans',
    link: 'settings.plans',
    value: 'plans',
    params: { baseId: base.id },
});

const peopleTab = (base) => ({
    namePath: 'links.people',
    link: 'settings.people',
    value: 'people',
    params: { baseId: base.id },
});

const profileTab = (base) => ({
    namePath: 'links.profile',
    link: 'settings.profile',
    value: 'profile',
    params: { baseId: base.id },
});

const personalBaseObj = (base) => ({
    namePath: 'common.myBase',
    header: true,
    subs: [
        generalTab(base),
        integrationsTab(base),
        plansTab(base),
    ],
    paramName: base.id,
    value: base.id,
});

export default {
    name: 'SettingsPage',
    components: {
        BasicVertical,
        BackRounded,
        ProfileNameImage,
        LandingBurger,
        // SettingsSecondary,
    },
    mixins: [
        // providesBackLinks,
    ],
    props: {
        showMainLoader: Boolean,
    },
    apollo: {
        spaces: SPACES,
        // links: {
        //     query: LINKS,
        //     update: _.identity,
        //     fetchPolicy: 'cache-first',
        // },
        // teams: TEAMS,
        // sharedDomains: SHARED_DOMAINS,
    },
    data() {
        return {
            firstLinkPath: window.history.state.previousLinkPath,
            menuOpen: false,
        };
    },
    computed: {
        routerKey() {
            return this.$route.path;
        },
        selectedTabObj() {
            return _.find(this.flattenedTabs, { link: this.selectedTab });
        },
        selectedTabPath() {
            return this.selectedTabObj?.namePath;
        },
        user() {
            return this.$root.authenticatedUser;
        },
        bases() {
            return this.user.allBases();
        },
        activeBase() {
            return this.user.activeBase();
        },
        hasBase() {
            return this.$route.params.baseId;
        },
        flattenedTabs() {
            return _.flatMap(this.tabs, (tab) => {
                return tab.subs ? tab.subs : tab;
            });
        },
        tabs() {
            return [
                {
                    namePath: 'links.account',
                    link: 'settings.account',
                    value: 'account',
                },
                {
                    namePath: 'links.preferences',
                    link: 'settings.preferences',
                    value: 'preferences',
                },
                {
                    namePath: 'links.notifications',
                    link: 'settings.notifications',
                    value: 'notifications',
                    hasAfterTab: true,
                },
                ...this.baseTabs,
            ];
        },
        baseTabs() {
            return this.bases.map((base) => {
                return this.getBaseTabs(base);
            });
        },
        backButtonText() {
            return this.firstLinkPath ? 'common.back' : 'links.home';
        },
        selectedTab() {
            return this.$route.name;
        },
        profileName() {
            return this.user ? this.user.name : '';
        },
        nameInitials() {
            return getInitials(this.profileName);
        },
        isPersonalInvitedOrTeam() {
            return /personal|invited|teams/i.test(this.selectedTab);
        },
        sectionType() {
            return matchedMeta(this.$route, 'section');
        },
        section() {
            if (this.sectionType === 'personal') {
                const id = this.$route.params.spaceId;
                return _.find(this.spaces?.edges, ['node.id', id])?.node;
            }
            // if (this.sectionType === 'teams') {
            //     const id = this.$route.params.teamId;
            //     return _.find(this.teams?.edges, ['node.id', id])?.node;
            // }
            // if (this.sectionType === 'invited') {
            //     const id = this.$route.params.inviterId;
            //     return _.find(this.sharedDomains?.edges, ['node.owner.id', id])?.node?.owner;
            // }
            return undefined;
        },
        sectionName() {
            return this.section?.name;
        },
        whichSectionLogo() {
            if (this.sectionType === 'teams') {
                return this.section?.logo;
            }
            if (this.sectionType === 'invited') {
                return this.section?.avatar;
            }
            return this.section?.logo ?? this.personalLogo;
        },
        personalLogo() {
            return this.user?.avatar;
        },
        currentBaseDisplay() {
            if (this.activeBase.baseType === 'PERSONAL') {
                return this.user;
            }
            return this.activeBase;
        },
        allowNewBase() {
            return this.bases.length < maxBases;
        },
    },
    methods: {
        goBack() {
            if (this.firstLinkPath) {
                return this.$router.push(this.firstLinkPath);
            }
            return this.$router.push({ name: 'home' });
        },
        getBaseTabs(base) {
            if (base.baseType === 'PERSONAL') {
                return {
                    ...personalBaseObj(base),
                    imageObj: this.user,
                };
            }
            return {
                name: base.name,
                header: true,
                imageObj: base,
                subs: this.getSubTabs(base),
                paramName: base.id,
                value: base.id,
            };
        },
        getSubTabs(base) {
            const fullList = [
                {
                    ...generalTab(base),
                    condition: this.isOwnerOrAdmin(base),
                },
                {
                    ...peopleTab(base),
                    condition: this.isOwnerOrAdmin(base),
                },
                {
                    ...plansTab(base),
                    condition: this.isOwner(base),
                },
                {
                    ...profileTab(base),
                    separate: true,
                    namePath: 'links.myProfile',
                    hasDividerAbove: this.isOwnerOrAdmin(base),
                },
                {
                    ...integrationsTab(base),
                    separate: true,
                    namePath: 'links.myIntegrations',
                },
            ];
            return fullList.filter((tab) => {
                return _.has(tab, 'condition') ? tab.condition : true;
            });
        },
        isAdmin(base) {
            return base.pivot.role === 'ADMIN';
        },
        isOwner(base) {
            return base.pivot.role === 'OWNER';
        },
        isOwnerOrAdmin(base) {
            return this.isOwner(base) || this.isAdmin(base);
        },

    },
    created() {

    },
};
</script>

<style scoped>
.o-settings-page {
    @apply
        bg-cm-00
        flex
    ;

    &__side {
        height: calc(100% - var(--g-bottom-desktop));
        max-height: calc(100vh - var(--g-bottom-desktop));

        @apply
            bg-cm-00
            overflow-y-auto
            p-8
            sticky
            top-0
            w-64
        ;

        &--resp {
            @apply
                fixed
                shadow-lg
                z-cover
            ;
            @media(min-width: 768px) {
                & {
                    @apply
                        shadow-none
                        sticky
                    ;
                }
            }
        }
    }

    &__base {
        transition: all 0.2s ease;

        @apply
            border
            border-cm-400
            border-dashed
            font-medium
            inline-flex
            mt-1
            px-3
            py-1.5
            rounded-lg
            text-cm-500
            text-sm
        ;

        &:hover {
            @apply
                bg-cm-100
            ;
        }
    }

}
</style>
