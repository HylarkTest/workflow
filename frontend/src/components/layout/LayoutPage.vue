<template>
    <div
        class="c-layout-page nav-spacing--top min-h-screen"
        :class="{ 'h-screen': isMaxFullScreen }"
    >
        <transition
            name="t-fade"
        >
            <div
                v-if="$slots.top && !isLoading"
                class="c-layout-page__top sticky nav-spacing--sticky z-alert"
                :class="topClass"
            >
                <slot name="top">
                </slot>
            </div>
        </transition>

        <component
            :is="backgroundComponent"
            class="c-layout-page__background"
            :class="backgroundClass"
        >
        </component>

        <div
            class="c-layout-page__main nav-spacing--bottom"
        >
            <div
                class="c-layout-page__header"
                :class="headerClasses"
            >
                <LayoutHeader
                    v-if="headerProps && !isLoading"
                    class="pb-6 pt-4"
                    :class="headerPaddingClass"
                    v-bind="headerProps"
                    @openPageEdit="$emit('openPageEdit', $event)"
                >
                </LayoutHeader>
            </div>

            <LoaderFetch
                v-if="isLoading"
                class="flex-1"
                :class="navigationClass"
                :isFull="true"
            >
            </LoaderFetch>

            <!-- Use conditionalDirective prop to use either v-if or v-show -->
            <div
                v-if="vIfVal"
                v-show="vShowVal"
                class="c-layout-page__content flex flex-col"
                :class="[paddingClass, navigationClass]"
            >
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script>

import LayoutHeader from '@/components/layout/LayoutHeader.vue';
import BackgroundFeature from '@/components/backgrounds/BackgroundFeature.vue';
import BackgroundSubfeature from '@/components/backgrounds/BackgroundSubfeature.vue';
import BackgroundEntities from '@/components/backgrounds/BackgroundEntities.vue';
import BackgroundGeneral from '@/components/backgrounds/BackgroundGeneral.vue';

import listensToScrollandResizeEvents from '@/vue-mixins/listensToScrollAndResizeEvents.js';

import GET_UI from '@/graphql/client/GetUI.gql';

const styleInfo = {
    FEATURE: {
        component: 'BackgroundFeature',
        class: 'c-layout-page__background--feature',
    },
    SUBFEATURE: {
        component: 'BackgroundSubfeature',
        class: 'c-layout-page__background--subfeature',
    },
    ENTITIES: {
        component: 'BackgroundEntities',
        class: 'c-layout-page__background--entities',
    },
    GENERAL: {
        component: 'BackgroundGeneral',
        class: 'c-layout-page__background--general',
    },
};

export default {
    name: 'LayoutPage',
    components: {
        LayoutHeader,
        BackgroundFeature,
        BackgroundSubfeature,
        BackgroundEntities,
        BackgroundGeneral,
    },
    mixins: [
        listensToScrollandResizeEvents,
    ],
    props: {
        paddingClass: {
            type: String,
            default: '',
        },
        isLoading: Boolean,
        headerPaddingClass: {
            type: String,
            default: 'px-8 md:px-4',
        },
        isMaxFullScreen: Boolean,
        headerProps: {
            type: [Object, null],
            default: null,
        },
        backgroundStyle: {
            type: String,
            default: 'GENERAL',
        },
        conditionalDirective: {
            type: String,
            default: 'show',
        },
    },
    emits: [
        'openPageEdit',
    ],
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
            isScrolling: document.body.scrollTop > 0,
        };
    },
    computed: {
        isStickyHeader() {
            return this.headerProps?.isStickyHeader;
        },
        stickyHeaderClasses() {
            return this.isStickyHeader
                ? 'c-layout-page__header--sticky nav-spacing--sticky'
                : '';
        },
        headerClasses() {
            return [
                this.stickyHeaderClasses,
                this.navigationClass,
            ];
        },
        navigationClass() {
            return this.ui?.navExtensionClass;
        },
        topClass() {
            return this.isScrolling
                ? 'c-layout-page__top--scroll'
                : 'c-layout-page__top--up';
        },
        backgroundClass() {
            return styleInfo[this.backgroundStyle].class;
        },
        backgroundComponent() {
            return styleInfo[this.backgroundStyle].component;
        },
        vIfVal() {
            // If CD is 'show', this should always be true as you want to depend on v-show rather than v-if.
            // If CD is 'if', then this property should be conditional on isLoading.
            return this.conditionalDirective === 'show' || !this.isLoading;
        },
        vShowVal() {
            // If CD is 'if', this should always be true as you want to depend on v-if rather than v-show.
            // If CD is 'show', then this property should be conditional on isLoading.
            return this.conditionalDirective === 'if' || !this.isLoading;
        },
    },
    methods: {
        onScroll() {
            this.isScrolling = document.body.scrollTop > 0;
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-layout-page {
    @apply
        flex
        flex-col
        relative
    ;

    &__top {
        transition: 0.1s ease-in-out;

        @apply
            border-primary-200
            pl-4
            pr-6
            py-2
        ;

        &--scroll {
            @apply
                bg-primary-100
                border-b
                border-solid
            ;
        }
    }

    &__main {
        @apply
            flex
            flex-1
            flex-col
            min-h-0
            relative
            z-over
        ;
    }

    &__background {
        @apply
            bottom-0
            fixed
            h-full
            left-0
            top-0
            w-full
        ;

        &--feature {
            @apply
                bg-gradient-to-b
                from-primary-50
                opacity-80
                to-primary-100
            ;
        }

        &--subfeature {
            @apply
                bg-gradient-to-r
                from-primary-50
                opacity-50
                to-primary-200
            ;
        }

        &--entities {
            @apply
                bg-gradient-to-bl
                from-primary-200
                opacity-50
                to-secondary-100
            ;
        }

        &--general {
            @apply
                bg-gradient-to-br
                from-secondary-100
                opacity-50
                to-primary-200
            ;
        }

    }

    /*&__quarter {
        height: 200px;
        width: 200px;

        @apply
            absolute
            bg-secondary-200
            bottom-0
            opacity-75
        ;
    }*/

    &__header--sticky {
        @apply
            bg-secondary-100
            border-b
            border-secondary-400
            border-solid
            sticky
            z-over
        ;
    }

    &__content {
        @apply
            flex-1
            mb-8
            min-h-0
            ml-8
            mr-8
        ;

        @media (min-width: 768px) {
            @apply
                ml-4
            ;
        }
    }
}

</style>
