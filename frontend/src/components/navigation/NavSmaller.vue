<template>
    <div class="o-nav-smaller">

        <div
            class="fixed top-0 left-0 w-full z-nav"
        >
            <div
                class="o-nav-smaller__top flex justify-between items-center"
            >
                <router-link
                    :to="{ name: 'home' }"
                >
                    <img
                        class="h-7 mt-1 hidden xs:block"
                        :src="'/images/logos/40h_logo.svg'"
                    />
                    <img
                        class="h-7 xs:hidden"
                        :src="'/images/logos/hylarkCircle.svg'"
                    />
                </router-link>

                <div class="flex items-center">
                    <NavLink
                        v-for="extra in extras"
                        :key="extra.val"
                        class=""
                        :link="extra"
                        activeStyle="HIGHLIGHT"
                        :isActive="isSupportActive(extra)"
                        @runAction="runAction"
                    >
                    </NavLink>

                    <NavAccount
                        v-if="user && links"
                        class="ml-4"
                        :user="user"
                        :links="links"
                        :popupOptions="popupOptions"
                    >
                    </NavAccount>

                    <Hamburger
                        class="ml-5 opacity-0"
                        :active="showFullNav"
                        @click="toggleNav"
                    >
                    </Hamburger>
                </div>
            </div>
        </div>

        <div
            class="fixed p-4 bottom-8 left-0 z-nav flex justify-center w-full"
        >
            <div
                class="o-nav-smaller__bar relative shadow-primary-900/30 opacity-90"
                :class="{ 'w-full': fiveOrMore }"
            >

                <div class="flex justify-center xxs:justify-between flex-wrap px-5">
                    <NavLink
                        v-for="link in icons"
                        :key="link.val"
                        :link="link"
                        hoverClass="hover:bg-cm-00"
                        activeStyle="HIGHLIGHT"
                    >
                    </NavLink>

                    <ButtonEl
                        class="o-nav-smaller__search shadow-xl hover:shadow-md z-over"
                        @click="openFinderModal"
                    >
                        <i
                            class="far fa-search fa-fw text-cm-400"
                        >
                        </i>
                    </ButtonEl>
                </div>
            </div>
        </div>

        <div
            v-if="showFullNav"
            class="fixed top-0 left-0 w-full h-screen z-nav"
        >
            <div
                class="w-full h-full opacity-50 absolute bg-cm-950"
            >
            </div>
            <div
                class="fixed h-full bg-cm-00 top-0 right-0 shadow-xl shadow-primary-900/60"
            >
                <div class="absolute top-0 right-0 w-full h-12 bg-cm-00">

                </div>
                <NavPages
                    paddingClass="px-8 pb-8 pt-12"
                    :spaces="spaces"
                    @selectPage="toggleNav"
                >
                </NavPages>
            </div>
        </div>
        <Hamburger
            class="fixed top-3 right-6 z-nav"
            :active="showFullNav"
            @click="toggleNav"
        >
        </Hamburger>
    </div>
</template>

<script>

import NavAccount from './NavAccount.vue';
import NavLink from './NavLink.vue';
import NavPages from './NavPages.vue';
import Hamburger from '@/components/buttons/Hamburger.vue';

import interactsWithNavBars from '@/vue-mixins/layout/interactsWithNavBars.js';

const popupOptions = {
    nudgeRightProp: '6.25rem',
    left: true,
    alignTop: true,
};

export default {
    name: 'NavSmaller',
    components: {
        NavAccount,
        Hamburger,
        NavLink,
        NavPages,
    },
    mixins: [
        interactsWithNavBars,
    ],
    props: {

    },
    data() {
        return {
            showFullNav: false,
        };
    },
    computed: {
        iconsLength() {
            return this.icons.length;
        },
        fiveOrMore() {
            return this.iconsLength >= 5;
        },
    },
    methods: {
        toggleNav() {
            this.showFullNav = !this.showFullNav;
        },
    },
    created() {
        this.popupOptions = popupOptions;
    },
};
</script>

<style scoped>

.o-nav-smaller {
    &__top {
        @apply
            bg-cm-00
            border-solid
            pl-6
            pr-8
            py-2
        ;
    }

    &__search {
        height: 34px;
        min-width: 34px;
        transition: 0.2s ease-in-out;
        width: 34px;

        @apply
            bg-cm-00
            flex
            items-center
            justify-center
            p-1
            rounded-full
            text-lg
        ;
    }

    &__bar {
        @apply
            bg-cm-100
            py-3
            rounded-2xl
            shadow-xl
        ;
    }
}

</style>
