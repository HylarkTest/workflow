<template>
    <LayoutPageSimple
        class="o-customize-all pr-8"
    >
        <h1
            class="o-customize-all__header nav-spacing--sticky"
        >
            <BirdImage
                class="mr-4 h-16"
                whichBird="FlyingUpBird_72dpi.png"
            >
            </BirdImage>

            <ProfileNameImage
                class="mr-2"
                hideFullName
                :profile="profile"
            >
            </ProfileNameImage>

            {{ pageTitle }}
        </h1>

        <div
            v-if="spaces"
            class="flex flex-1 h-full min-h-0 pb-8 items-start"
        >
            <div class="o-customize-all__tabs">
                <RoundedVertical
                    :tabs="tabs"
                    :router="router"
                    :paramKey="paramKey"
                    @selectTab="selectTab"
                >
                </RoundedVertical>
            </div>

            <component
                :is="customizationComponent"
                class="flex-1"
                :spaces="spaces"
                :allPages="allPages"
            >
            </component>
        </div>
    </LayoutPageSimple>
</template>

<script>

import CustomizeSpaces from './CustomizeSpaces.vue';
import CustomizeNavigation from './CustomizeNavigation.vue';
import CustomizeExtras from './CustomizeExtras.vue';
import CustomizeCategories from './CustomizeCategories.vue';
import CustomizeTimekeeper from './CustomizeTimekeeper.vue';
import CustomizeStatuses from './CustomizeStatuses.vue';
import CustomizePipelines from './CustomizePipelines.vue';
import CustomizeTags from './CustomizeTags.vue';
import CustomizeFooter from './CustomizeFooter.vue';
import LayoutPageSimple from '@/components/layout/LayoutPageSimple.vue';
import RoundedVertical from '@/components/tabs/RoundedVertical.vue';
import ProfileNameImage from '@/components/profile/ProfileNameImage.vue';

import PAGES from '@/graphql/pages/queries/Pages.gql';

import { isActiveBasePersonal, activeBase } from '@/core/repositories/baseRepository.js';

import setsTabSelection from '@/vue-mixins/setsTabSelection.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';

export default {
    name: 'CustomizeAll',
    components: {
        CustomizeSpaces,
        CustomizeCategories,
        CustomizeExtras,
        CustomizeTimekeeper,
        CustomizeNavigation,
        CustomizeStatuses,
        CustomizePipelines,
        CustomizeTags,
        CustomizeFooter,
        RoundedVertical,
        LayoutPageSimple,
        ProfileNameImage,
    },
    mixins: [
        setsTabSelection,
    ],
    props: {

    },
    apollo: {
        spaces: {
            query: PAGES,
            update: (links) => initializeConnections(links).spaces,
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
            router: true,
            // selectedTab: tabs[0].value,
            paramKey: 'tab',
        };
    },
    computed: {
        routeTab() {
            return this.$route.params.tab;
        },
        customizationComponent() {
            const name = _.pascalCase(this.routeTab);
            return `Customize${name}`;
        },
        allPages() {
            return _(this.spaces).flatMap('pages').value();
        },
        profile() {
            return this.isPersonalActive ? this.user : this.activeBase;
        },
        user() {
            return this.$root.authenticatedUser;
        },
        activeBase() {
            return activeBase();
        },
        isPersonalActive() {
            return isActiveBasePersonal();
        },
        pageTitle() {
            return this.activeBase.name;
        },
        spaceTitle() {
            return this.isPersonalActive ? 'My spaces' : 'Spaces';
        },
        tabs() {
            return [
                {
                    value: 'SPACES',
                    name: this.spaceTitle,
                    paramName: 'spaces',
                    link: 'customizePage',
                },
                {
                    value: 'TAGS',
                    name: 'Tags',
                    paramName: 'tags',
                    link: 'customizePage',
                },
                {
                    value: 'PIPELINES',
                    name: 'Pipelines',
                    paramName: 'pipelines',
                    link: 'customizePage',
                },
                {
                    value: 'STATUSES',
                    name: 'Statuses',
                    link: 'customizePage',
                    paramName: 'statuses',
                },
                // {
                //     value: 'TIMEKEEPER',
                //     name: 'Timekeeper',
                //     link: 'customizePage',
                //     paramName: 'timekeeper',
                // },
                {
                    value: 'CATEGORIES',
                    name: 'Categories',
                    link: 'customizePage',
                    paramName: 'categories',
                },
                {
                    value: 'NAVIGATION',
                    name: 'Navigation',
                    link: 'customizePage',
                    paramName: 'navigation',
                },
                {
                    value: 'FOOTER',
                    name: 'Footer',
                    link: 'customizePage',
                    paramName: 'footer',
                },
                // {
                //     value: 'EXTRAS',
                //     name: 'Extras',
                //     link: 'customizePage',
                //     paramName: 'extras',
                // },
            ];
        },

    },
    methods: {
    },
    created() {
        // this.tabs = tabs;
    },
};
</script>

<style>

.o-customize-all {
    @apply
        flex
        flex-col
    ;

    &__header {
        @apply
            bg-cm-00
            flex
            font-bold
            items-center
            pb-12
            pt-8
            px-8
            sticky
            text-5xl
            text-primary-950
            z-over
        ;
    }

    &__tabs {
        top: calc(144px + var(--g-top-resp));

        @apply
            mr-8
            pl-8
            sticky
            w-36
        ;

        @media (min-width: 768px) {
            top: calc(144px);
        }
    }
}

</style>
