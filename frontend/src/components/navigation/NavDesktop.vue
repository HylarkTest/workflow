<template>
    <div class="o-nav-desktop o-nav-desktop--resp p-4">

        <div
            class="relative h-full flex shadow-xl shadow-primary-900/40 rounded-2xl"
        >
            <!-- <div
                class="absolute top-0 left-0 bg-cm-100 opacity-90 h-full w-full z-0"
                :class="roundedBarClass"
            >

            </div> -->
            <div
                class="h-full z-over bg-cm-100 opacity-90 relative"
                :class="roundedBarClass"
            >
                <AngleButton
                    class="o-nav-desktop__angle"
                    :angleDirection="angleDirection"
                    @click="toggleExtended"
                >
                </AngleButton>

                <div
                    class="o-nav-desktop__bar"
                    :class="barClass"
                >
                    <div
                        class="sticky top-0 bg-cm-100 relative z-cover rounded-t-2xl"
                    >
                        <div
                            class="flex items-center mb-6 mt-8 px-4"
                            :class="{ 'justify-center': !isExtended }"
                        >
                            <!-- v-show rather than conditional due to lagging transition when a computed property -->
                            <router-link
                                :to="{ name: 'home' }"
                            >
                                <img
                                    v-show="isExtended"
                                    class="o-nav-desktop__logo ml-2"
                                    :src="'/images/logos/40h_logo.svg'"
                                />
                                <img
                                    v-show="!isExtended"
                                    class="o-nav-desktop__logo"
                                    :src="'/images/logos/hylarkCircle.svg'"
                                />
                            </router-link>
                        </div>

                        <div class="px-5 mb-6">

                            <ButtonEl
                                class="o-nav-desktop__search hover:shadow-md bg-cm-00"
                                :class="isExtended ? 'o-nav-desktop__search--extended' : 'o-nav-desktop__search--mini'"
                                @click="openFinderModal"
                            >
                                <i
                                    class="far fa-search fa-fw text-cm-400"
                                >
                                </i>

                                <span
                                    v-if="isExtended"
                                    v-t="'finder.findAnything'"
                                    class="ml-2 text-cm-400"
                                >
                                </span>
                            </ButtonEl>
                        </div>
                    </div>

                    <div class="flex-1 py-2 mt-6">

                        <div class="px-4 mb-2 flex flex-col">
                            <NavLink
                                v-for="icon in icons"
                                :key="icon.val || icon.id"
                                class="o-nav-desktop__page"
                                hoverClass="hover:bg-cm-00"
                                :class="isExtended ? '' : 'align-center'"
                                :isExtended="isExtended"
                                :link="icon"
                            >
                            </NavLink>
                        </div>

                        <ButtonEl
                            class="o-nav-desktop__opener"
                            :class="allPagesClasses"
                            :title="$t('navigation.viewAllPages')"
                            @click="toggleAllPages"
                        >
                            <span
                                v-t="isExtended ? 'navigation.allPages' : 'common.all'"
                            >
                            </span>

                            <i
                                class="o-nav-desktop__chevrons far fa-fw"
                                :class="allPagesArrow"
                            >
                            </i>
                        </ButtonEl>

                    </div>

                    <div class="px-4 flex flex-col">
                        <NavLink
                            v-for="link in extras"
                            :key="link.val"
                            class="o-nav-desktop__page"
                            :class="isExtended ? '' : 'items-center'"
                            :link="link"
                            hoverClass="hover:bg-cm-00"
                            :isExtended="isExtended"
                            :isActive="isSupportActive(link)"
                            @runAction="runAction"
                        >
                        </NavLink>
                    </div>

                    <NavAccount
                        v-if="user && links"
                        class="pt-4 sticky bottom-0 pb-4 px-4 bg-cm-100 z-over"
                        :user="user"
                        :links="links"
                        :isExtended="isExtended"
                    >
                    </NavAccount>
                </div>

            </div>

            <div
                class="py-2 bg-cm-00 rounded-r-2xl"
            >
                <NavPages
                    v-if="showAllPages"
                    class="o-nav-desktop__all h-full"
                    :spaces="spaces"
                >
                </NavPages>
            </div>
        </div>
    </div>
</template>

<script>

import { gql } from '@apollo/client';
import NavAccount from './NavAccount.vue';
import NavPages from './NavPages.vue';
import NavLink from './NavLink.vue';
import GET_UI from '@/graphql/client/GetUI.gql';
import AngleButton from '@/components/buttons/AngleButton.vue';

import interactsWithNavBars from '@/vue-mixins/layout/interactsWithNavBars.js';

export default {
    name: 'NavDesktop',
    components: {
        NavAccount,
        NavPages,
        NavLink,
        AngleButton,
    },
    mixins: [
        interactsWithNavBars,
    ],
    props: {

    },
    apollo: {
        ui: {
            query: GET_UI,
            update(data) {
                return data.ui;
            },
            client: 'defaultClient',
        },
    },
    data() {
        return {
            showAllPages: false,
        };
    },
    computed: {
        isExtended() {
            return this.ui?.isNavExtended;
        },
        angleDirection() {
            return this.isExtended ? 'left' : 'right';
        },
        barClass() {
            return this.isExtended
                ? 'o-nav-desktop__bar--extended'
                : 'o-nav-desktop__bar--small';
        },
        allPagesClasses() {
            return this.showAllPages
                ? 'bg-primary-600 text-cm-00'
                : 'text-primary-700 bg-primary-200';
        },
        allPagesArrow() {
            return this.showAllPages
                ? 'o-nav-desktop__chevrons--open fa-chevrons-left'
                : 'o-nav-desktop__chevrons--closed fa-chevrons-right';
        },
        roundedBarClass() {
            return this.showAllPages ? 'rounded-l-2xl' : 'rounded-2xl';
        },
    },
    methods: {
        async toggleExtended() {
            await this.$apollo.mutate({
                mutation: gql`
                    mutation {
                        toggleExtendedNav @client
                    }
                `,
                optimisticResponse: {
                    toggleNavExtended: !this.isExtended,
                },
                client: 'defaultClient',
            });
            if (this.isExtended) {
                this.showAllPages = false;
            }
        },
        toggleAllPages() {
            this.showAllPages = !this.showAllPages;
            if (this.showAllPages && this.isExtended) {
                this.toggleExtended();
            }
        },
    },
    watch: {
        $route() {
            this.showAllPages = false;
        },
    },
};
</script>

<style scoped>

.o-nav-desktop {
    --icon-size: 30px;
    /* For safari ipad and iphone fixes */
    /* stylelint-disable plugin/no-unsupported-browser-features */
    height: 100vh;
    max-height: 100vh;
    max-height: -webkit-fill-available;
    /* stylelint-enable */

    @apply
        fixed
        top-0
        z-nav
    ;

    &__bar {
        @apply
            flex
            flex-col
            h-full
            justify-between
            overflow-x-hidden
            overflow-y-auto
            rounded-2xl
            text-sm
        ;

        &--small {
            max-width: var(--g-side-mini);
            width: var(--g-side-mini);

            @apply
                items-center
            ;
        }

        &--extended {
            width: var(--g-side-extended);
        }
    }

    &__angle {
        @apply
            absolute
            -right-2
            top-6
            z-alert
        ;
    }

    &__section {
        @apply
            flex
            flex-col
        ;
    }

    &__logo {
        height: 26px;
    }

    &__page {
        margin: 1px 0;
    }

    &__icon {
        height: var(--icon-size);
        min-width: var(--icon-size);
        width: var(--icon-size);
    }

    &__search {
        height: var(--icon-size);
        transition: box-shadow 0.2s ease-in-out;

        @apply
            flex
            items-center
            p-1
            rounded-full
        ;

        &--extended {
            @apply
                px-3
            ;
        }

        &--mini {
            min-width: var(--icon-size);
            width: var(--icon-size);

            @apply
                justify-center
            ;
        }
    }

    &__opener {
        transition: 0.2s ease-in-out;

        @apply
            flex
            font-semibold
            items-center
            justify-between
            px-3
            py-3
            text-xs
            uppercase
            w-full
        ;

        &:hover {
            .o-nav-desktop__chevrons {
                &--closed {
                    margin-right: -4px;
                }

                &--open {
                    margin-right: 0;
                }
            }
        }
    }

    &__chevrons {
        transition: 0.2s ease-in-out;

        &--closed {
            margin-right: 0;
        }

        &--open {
            margin-right: -4px;
        }
    }

    &__all {
        width: 200px;
    }

}
</style>
