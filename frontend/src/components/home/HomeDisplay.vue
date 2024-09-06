<template>
    <div class="o-home-display">
        <div class="o-home-display__banner mb-8">
            <h3 class="flex text-xl md:text-2xl text-primary-1000">
                <ProfileNameImage
                    class="mr-3"
                    hideFullName
                    size="lg"
                    :profile="displayedBase"
                >
                </ProfileNameImage>
                <div>
                    <div class="uppercase text-xssm leading-tight font-medium text-cm-400">
                        {{ baseName }}
                    </div>
                    <span class="font-bold">
                        {{ firstTime ? 'Welcome,' : 'Welcome back,' }}
                    </span>
                    <span>&nbsp;{{ user.name }}</span>
                </div>
            </h3>

            <RoundedIcon
                icon="fa-sliders-simple"
                title="Home page customizations"
                @click="openHomeCustomizations"
            >
            </RoundedIcon>
        </div>

        <div class="mb-8">
            <div
                v-if="!isVerified"
                class="rounded-xl p-4 shadow-lg bg-primary-800 shadow-primary-600/20"
            >
                <p class="font-bold mb-1 text-cm-00">
                    <i class="far fa-star-sharp mr-1">
                    </i>
                    {{ activatePrompt }}
                </p>
                <p
                    v-t="activateDescriptionPath"
                    class="font-medium text-xssm text-primary-200"
                >
                </p>
                <a
                    class="o-home-display__activate button--sm"
                    href="/email/verification-notification"
                >
                    Resend activation email
                </a>
            </div>

            <div
                v-if="hasPersonalBasePrompt"
                class="mt-8 text-center"
            >
                <p
                    class="font-semibold mb-3"
                >
                    Want to use Hylark for yourself, not only to collaborate?
                </p>
                <div
                    class="text-cm-600 text-sm"
                >
                    <p
                        class="mb-2"
                    >
                        Your Hylark account includes a personal base that only you can access.
                        You can switch to it at any time from the navigation.
                    </p>
                    <p
                        class="mb-2"
                    >
                        Have an idea and want to get started?
                    </p>
                    <RouterLink
                        class="button--sm button-secondary"
                        :to="{ name: 'customizePage', params: { baseId: personalBase.id } }"
                    >
                        Customize your personal base
                    </RouterLink>
                </div>
            </div>
        </div>

        <HomeFeatures
            class="mb-10"
            :isFirstTime="firstTime"
        >
        </HomeFeatures>

        <HomeDive
            v-if="diveLength"
            :recentItems="diveBackIn"
        >
        </HomeDive>

        <LoaderFetch
            v-if="isLoading"
            class="my-5"
            :isFull="true"
        >
        </LoaderFetch>

        <div
            v-else-if="pageContentAvailable"
            class="mt-10"
        >
            <h2 class="font-bold text-2xl text-primary-900 mb-4">
                My pages
            </h2>

            <NoContentText
                v-if="!spacesWithPagesLength"
                class="mt-4"
                customHeaderPath="home.spaces.noContent.header"
                customMessagePath="home.spaces.noContent.description"
                customIcon="fa-memo"
                iconBgClass="bg-cm-00"
            >
                <RouterLink
                    class="button-primary--border button--sm mt-2 inline-flex"
                    :to="{ name: 'customizePage' }"
                >
                    Go to customizations
                </RouterLink>
            </NoContentText>

            <div
                v-if="spacesWithPagesLength"
            >
                <div
                    v-for="space in orderedSpaces"
                    :key="space.id"
                    class="mb-16 last:mb-0"
                >
                    <HomeSpace
                        :space="space"
                    >
                    </HomeSpace>
                </div>
            </div>
        </div>

        <HomeCustomize
            v-if="isModalOpen"
            :displayedBase="displayedBase"
            @closeModal="closeModal"
        >
        </HomeCustomize>
    </div>
</template>

<script>

import HomeSpace from './HomeSpace.vue';
import HomeDive from './HomeDive.vue';
import HomeFeatures from './HomeFeatures.vue';
import HomeCustomize from './HomeCustomize.vue';
import RoundedIcon from '@/components/buttons/RoundedIcon.vue';
import ProfileNameImage from '@/components/profile/ProfileNameImage.vue';
import providesSpaceFolderHelpers from '@/vue-mixins/providesSpaceFolderHelpers.js';
import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';
import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import { isActiveBasePersonal, activeBase } from '@/core/repositories/baseRepository.js';

import DIVE_BACK_IN from '@/graphql/history/queries/DiveBackIn.gql';
import LINKS from '@/graphql/Links.gql';
import LINKS_WITH_EXTRAS from '@/graphql/LinksWithExtras.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

const prompts = [
    {
        val: 'TODOS',
        route: { name: 'todos' },
    },
    {
        val: 'CALENDAR',
        route: { name: 'calendar' },
    },
    // {
    //     val: 'NETWORK',
    // },
    {
        val: 'INTEGRATIONS',
        route: { name: 'settings.integrations' },
    },
];

export default {
    name: 'HomeDisplay',
    components: {
        HomeSpace,
        HomeDive,
        HomeFeatures,
        HomeCustomize,
        ProfileNameImage,
        RoundedIcon,
    },
    mixins: [
        providesSpaceFolderHelpers,
        interactsWithApolloQueries,
        interactsWithModal,
    ],
    props: {
        user: {
            type: Object,
            required: true,
        },
        personalBasePreferences: {
            type: Object,
            required: true,
        },
        everyoneBasePreferences: {
            type: [Object, null],
            default: null,
        },
    },
    apollo: {
        links: {
            query: LINKS,
            update: initializeConnections,
            fetchPolicy: 'cache-first',
        },
        diveBackIn: {
            query: DIVE_BACK_IN,
            update: ({ history }) => initializeConnections(history),
            variables() {
                return {
                    performer: this.user.activeBaseMemberId,
                };
            },
        },
        _linksWithExtras: {
            query: LINKS_WITH_EXTRAS,
            update: _.identity,
        },
    },
    data() {
        return {
        };
    },
    computed: {
        isLoading() {
            return this.$isLoadingQueries(['links']);
        },
        pageContentAvailable() {
            // Addresses the two scenarios of has hidden all pages
            // or doesn't have pages
            // Still want the prompt to show if they have no page
            if (this.hasExistingPages) {
                return this.spacesWithPagesLength;
            }
            return true;
        },
        diveLength() {
            return this.diveBackIn?.length;
        },
        defaultSpaces() {
            return this.everyoneBasePreferences?.homepage.spaces;
        },
        personalSpaces() {
            return this.personalBasePreferences?.homepage.spaces;
        },
        shownPagesInSpaces() {
            const shownSpaces = {};
            this.spaces.forEach((space) => {
                const spaceId = space.id;
                const spaceValue = this.personalSpaces?.[spaceId] || this.defaultSpaces?.[spaceId] || null;
                shownSpaces[space.id] = spaceValue;
            });
            return shownSpaces;
        },
        // defaultPages() {
        //     return this.everyoneBasePreferences?.homepage.pages;
        // },
        // personalPages() {
        //     return this.personalBasePreferences?.homepage.pages;
        // },
        // shownPages() {
        //     return this.personalPages || this.defaultPages;
        // },
        spaces() {
            return this.links?.spaces || [];
        },
        firstTime() {
            return window.firstArrival;
        },
        hasPersonalBasePrompt() {
            return window.hasPersonalBasePrompt && !this.isPersonalActive;
        },
        isVerified() {
            return this.user.verified;
        },
        filteredSpaces() {
            return this.spaces.map((space) => {
                const allPages = space.pages;
                let filteredPages;
                const spaceVal = this.shownPagesInSpaces[space.id];

                const noFilterOrAll = !spaceVal || spaceVal.pages === 'ALL';

                if (noFilterOrAll) {
                    filteredPages = allPages;
                } else {
                    filteredPages = allPages.filter((page) => {
                        return spaceVal.pages.includes(page.id);
                    });
                }
                const existingPages = allPages;
                return {
                    ...space,
                    existingPages,
                    pages: filteredPages,
                };
            });
        },
        hasExistingPages() {
            return this.filteredSpaces.some((space) => {
                return space.existingPages?.length;
            });
        },
        spacesWithPagesLength() {
            return this.spacesWithPages?.length;
        },
        spacesWithPages() {
            return this.filteredSpaces.filter((space) => {
                return space.pages?.length;
            });
        },
        orderedSpaces() {
            return this.spacesWithPages.map((space) => {
                const pages = space.pages;
                const grouped = this.groupedByFolder(pages);
                const flat = _.flatMap(grouped, 'pages');
                return {
                    ...space,
                    pages: flat,
                };
            });
        },
        activeBase() {
            return activeBase();
        },
        displayedBase() {
            return this.isPersonalActive ? this.user : this.activeBase;
        },
        isPersonalActive() {
            return isActiveBasePersonal();
        },
        baseName() {
            return this.activeBase.name;
        },
        allBases() {
            return this.user.allBases();
        },
        personalBase() {
            return this.user.personalBase();
        },
        activatePrompt() {
            return this.isPersonalActive
                ? 'Activate your account!'
                : 'Authenticate your email address';
        },
        activateDescriptionPath() {
            return this.isPersonalActive
                ? 'home.activate.descriptions.personal'
                : 'home.activate.descriptions.collaborative';
        },
    },
    methods: {
        getPromptString(val, textKey) {
            return `home.prompts.${_.camelCase(val)}.${textKey}`;
        },
        openHomeCustomizations() {
            this.openModal();
        },
    },
    created() {
        this.prompts = prompts;
    },
};
</script>

<style scoped>

.o-home-display {
    @apply
        h-full
    ;

    &__banner {
        @apply
            bg-cm-00
            flex
            justify-between
            p-4
            rounded-xl
        ;
    }

    &__prompt {
        @apply
            bg-secondary-100
            flex
            flex-col
            m-2
            px-4
            py-6
            rounded-xl
            shadow-lg
            w-full
        ;

        @media (min-width: 768px) {
            & {
                @apply
                    flex-1
                ;
            }
        }

        @media (min-width: 1024px) {
            & {
                @apply
                    flex-none
                ;
            }
        }

        @media (min-width: 1155px) {
            & {
                @apply
                    flex-1
                ;
            }
        }
    }

    &__activate {
        @apply
            border
            border-cm-00
            border-solid
            inline-block
            mt-3
            text-cm-00
        ;

        &:hover {
            @apply
                bg-primary-700
            ;
        }
    }

    /*
    &__add {
        @apply
            bg-primary-100
            flex
            h-16
            items-center
            justify-center
            mb-2
            rounded-md
            text-3xl
            text-primary-600
            w-16
        ;
    }
     */
}

</style>
