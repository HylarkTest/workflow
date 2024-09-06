<template>
    <nav
        class="o-support-nav transition-2eio px-6 md:px-12 py-6"
        :class="navClass"
    >
        <div class="o-support-nav__left">
            <router-link
                :to="{ name: 'home' }"
            >
                <img
                    class="h-6 md:h-8"
                    :src="'/images/logos/20h_logo.svg'"
                    alt="Hylark logo"
                />
            </router-link>

            <ButtonEl
                class="o-support-nav__search hover:bg-cm-100 transition-2eio ml-4 md:ml-8"
                @click="openModal"
            >
                <i class="far fa-magnifying-glass mr-1.5">
                </i>

                {{ $t('common.search') }}

                <!-- <div

                >
                    CMD + K
                </div> -->
            </ButtonEl>

            <!-- <p
                class="relative uppercase text-cm-400 text-sm font-semibold -top-2 ml-1"
            >
                Knowledge base
            </p> -->
        </div>

        <div
            v-if="!inMobileApp"
            class="o-support-nav__right"
        >
            <router-link
                :to="{ name: 'home' }"
                class="button bg-azure-600 hover:bg-azure-500 text-cm-00 mr-3"
            >
                <span
                    class="hidden md:inline"
                >
                    Back to main site
                </span>
                <span
                    v-t="'common.back'"
                    class="md:hidden"
                >
                </span>
            </router-link>
        </div>

        <SupportSearch
            v-if="isModalOpen"
            @closeModal="closeModal"
        >
        </SupportSearch>
    </nav>
</template>

<script>

import SupportSearch from '@/components/supportSite/SupportSearch.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import listensToScrollandResizeEvents from '@/vue-mixins/listensToScrollAndResizeEvents.js';

import { logout } from '@/core/auth.js';

export default {
    name: 'SupportNav',
    components: {
        SupportSearch,
    },
    mixins: [
        interactsWithModal,
        listensToScrollandResizeEvents,
    ],
    props: {

    },
    data() {
        return {
            isScrolling: window.scrollY > 0,
            inMobileApp: this.$route.query.mobileapp === 'true',
        };
    },
    computed: {
        navClass() {
            return this.isScrolling ? 'o-support-nav--scroll' : '';
        },
    },
    methods: {
        signOut() {
            this.$root.showSignoutLoader = true;
            logout();
        },
        onScroll() {
            this.isScrolling = window.document.body.scrollTop > 0;
        },
    },
    created() {
    },
};
</script>

<style scoped>

.o-support-nav {
    @apply
        bg-cm-00
        border-b
        border-cm-200
        border-solid
        flex
        justify-between
        py-3
        sticky
        top-0
        z-nav
    ;

    &--scroll {
        @apply
            bg-white
            shadow-lg
        ;
    }

    &__left {
        @apply
            flex
            items-center
        ;
    }

    &__search {
        @apply
            flex
            items-center
            px-2
            py-1
            rounded-md
            text-cm-600
            text-smbase
        ;
    }

    &__right {
        @apply
            flex
        ;
    }
}

</style>
