<template>
    <nav
        class="o-landing-nav transition-2eio"
        :class="navClass"
    >
        <div>
            <img
                class="o-landing-nav__logo"
                :src="'/images/logos/20h_logo.svg'"
                alt="Hylark logo"
            />
        </div>

        <div
            class="o-landing-nav__links"
        >
            <router-link
                v-for="link in landingLinks"
                :key="link"
                :to="{ name: link }"
                class="o-landing-nav__button o-landing-nav__link hover:bg-azure-100"
                :class="{ 'o-landing-nav__link--active': isActive(link) }"
            >
                <span
                    v-t="'landing.general.' + link"
                >
                </span>
                <span
                    v-if="isActive(link)"
                    class="o-landing-nav__underline"
                >

                </span>
            </router-link>
        </div>

        <div class="flex items-center">
            <div>
                <ButtonEl
                    v-if="$root.isGuest"
                    @click="logout"
                >
                    <span
                        v-t="'common.logOut'"
                    >
                    </span>
                </ButtonEl>
                <router-link
                    v-else
                    class="o-landing-nav__button o-landing-nav__login hover:bg-azure-100"
                    :to="{ name: 'access.login' }"
                >
                    <span
                        v-t="'common.logIn'"
                    >
                    </span>
                </router-link>

                <router-link
                    class="o-landing-nav__button o-landing-nav__signup bg-azure-600 hover:bg-azure-500"
                    :to="{ name: $root.isGuest ? 'register.initial' : 'home' }"
                >
                    <span
                        v-t="`common.${$root.isGuest ? 'signUp' : 'enter'}`"
                    >
                    </span>
                </router-link>
            </div>

            <div
                v-if="showBurger"
                class="o-landing-nav__menu ml-4 "
            >
                <div
                    class="o-landing-nav__burger cursor-pointer"
                    @click="toggleMenu"
                >
                    <div class="o-landing-nav__circle circle-center">

                    </div>
                    <LandingBurger
                        :isActive="isMenuOpen"
                    >
                    </LandingBurger>
                </div>
                <LandingMenu
                    v-if="isMenuOpen"
                    :landingLinks="landingLinks"
                >
                </LandingMenu>
            </div>
        </div>
    </nav>
</template>

<script>

import LandingBurger from '@/components/landing/LandingBurger.vue';

import listensToScrollandResizeEvents from '@/vue-mixins/listensToScrollAndResizeEvents.js';
import ButtonEl from '@/components/assets/ButtonEl.vue';
import { logout } from '@/core/auth.js';
import LandingMenu from '@/components/landing/LandingMenu.vue';

const landingLinks = [
    'home',
    'product',
    'pricing',
    'contact',
];

const widthForBurger = 800;

export default {
    name: 'LandingNav',
    components: {
        ButtonEl,
        LandingBurger,
        LandingMenu,
    },
    mixins: [
        listensToScrollandResizeEvents,
    ],
    props: {

    },
    data() {
        return {
            isMenuOpen: null, // Boolean or null, for animation effects
            showBurger: window.innerWidth <= widthForBurger,
            isScrolling: window.scrollY > 0,
        };
    },
    computed: {
        navClass() {
            return this.isScrolling ? 'o-landing-nav--scroll' : 'o-landing-nav--up';
        },
    },
    methods: {
        isActive(link) {
            return this.$route.name === link;
        },
        toggleMenu() {
            this.isMenuOpen = !this.isMenuOpen;
        },
        closeMenu() {
            this.isMenuOpen = false;
        },
        onResize() {
            const webWidth = window.innerWidth;
            if (webWidth <= widthForBurger) {
                this.isMenuOpen = null;
            }
            this.showBurger = webWidth <= widthForBurger;
        },
        onScroll() {
            this.isScrolling = window.scrollY > 0;
        },
        logout() {
            logout();
        },
    },
    created() {
        this.landingLinks = landingLinks;
    },
};
</script>

<style scoped>

.o-landing-nav {
    @apply
        flex
        items-center
        justify-between
        px-12
        py-2
        w-full
        z-20
    ;

    &--up {
        @apply
            absolute
        ;
    }

    &--scroll {
        @apply
            bg-white
            fixed
            shadow-md
        ;
    }

    &__logo {
        height: 28px;
    }

    &__button {
        @apply
            px-5
            py-2
            relative
            rounded-xl
            text-sm
        ;
    }

    &__login {
        @apply
            border
            border-azure-600
            border-solid
            mr-2
            text-azure-600
        ;
    }

    &__signup {
        @apply
            border
            border-azure-600
            border-solid
            mr-1
            text-white
        ;
    }

    &__underline {
        height: 1px;

        @apply
            absolute
            bg-azure-600
            bottom-0
            left-0
            w-full
        ;
    }

    &__links {
        @apply
            hidden
        ;
    }

    &__link {
        @apply
            mx-1
            px-3
            text-gray-600
        ;

        &--active {
            @apply
                text-azure-600
            ;
        }
    }

    &__menu {
        @apply
            relative
        ;
    }

    &__burger {
        @apply
            relative
        ;

        &:hover {
            .o-landing-nav__circle {
                @apply
                    bg-azure-100
                ;
            }
        }
    }

    &__circle {
        height: 38px;
        left: -8px;
        top: -8px;
        width: 38px;

        @apply
            absolute
            -z-1
        ;
    }

    @media (min-width: 800px) {
        &__links {
            @apply
                block
            ;
        }
    }
}

</style>
