<template>
    <div class="c-tab1">
        <BasicTabs
            :tabs="tabs"
            direction="column"
            :selectedTab="selectedTab"
            tabClass="mt-6 first:mt-0"
        >
            <template
                #item="{ tab }"
            >
                <div
                    v-if="tab.label"
                >
                    <h4
                        class="header-uppercase-light mb-1 px-3"
                    >
                        {{ tab.name }}
                    </h4>

                    <div class="flex flex-col items-start">
                        <component
                            v-for="subTab in tab.sub"
                            :key="subTab.value"
                            :is="linkComponent"
                            :to="{ name: subTab.link, params: { [paramKey]: subTab.paramName } }"
                            class="c-tab1__link"
                            :class="activeClass(subTab)"
                            type="button"
                            @click="emitTab(subTab)"
                        >
                            <div class="flex items-baseline min-w-0">
                                <div class="c-tab1__circle shrink-0">
                                    <i
                                        class="c-tab1__icon far fa-fw"
                                        :class="subTab.icon"
                                    >
                                    </i>
                                </div>

                                <p class="min-w-0">
                                    {{ subTab.name }}
                                </p>
                            </div>

                            <span
                                v-if="subTab.count"
                                class="c-tab1__count"
                            >
                                {{ subTab.count }}
                            </span>

                            <span
                                v-if="subTab.dot"
                                class="c-tab1__dot"
                            >
                            </span>
                        </component>
                    </div>
                </div>

                <div
                    v-else
                >
                    <component
                        :is="linkComponent"
                        :to="{ name: tab.link, params: { [paramKey]: tab.paramName } }"
                        class="c-tab1__link"
                        :class="activeClass(tab)"
                        @click="emitTab(tab)"
                    >
                        <div class="flex items-baseline min-w-0">
                            <div class="c-tab1__circle shrink-0">
                                <i
                                    class="c-tab1__icon far fa-fw"
                                    :class="tab.icon"
                                >
                                </i>
                            </div>

                            <p class="min-w-0">
                                {{ tab.name }}
                            </p>
                        </div>

                        <span
                            v-if="tab.count"
                            class="c-tab1__count"
                        >
                            {{ tab.count }}
                        </span>

                        <span
                            v-if="tab.dot"
                            class="c-tab1__dot"
                        >
                        </span>
                    </component>
                </div>
            </template>
        </BasicTabs>
    </div>
</template>

<script>

import BasicTabs from '@/components/tabs/BasicTabs.vue';

import providesTabBasics from '@/vue-mixins/providesTabBasics.js';

export default {
    name: 'Tab1',
    components: {
        BasicTabs,
    },
    mixins: [
        providesTabBasics,
    ],
    props: {
        forceResponsiveDisplay: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        linkComponent() {
            return this.router ? 'RouterLink' : 'button';
        },
    },
    methods: {
        activeClass(tab) {
            return this.isActive(tab) && !this.forceResponsiveDisplay
                ? 'c-tab1__link--active'
                : 'c-tab1__link--not-active';
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-tab1 {
    @apply
        text-sm
    ;

    &__link {
        @apply
            flex
            items-center
            justify-between
            my-0.5
            pl-4
            pr-8
            py-0.5
            rounded-md
            w-full
        ;

        &--not-active {
            &:hover {
                @apply
                    hover:bg-cm-100
                ;

                .c-tab1__circle {
                    @apply
                        bg-cm-200
                    ;
                }
            }
        }

        &--active {
            @apply
                bg-primary-100
                cursor-auto
                font-semibold
                text-primary-600
            ;

            .c-tab1__circle {
                @apply bg-primary-600;
            }

            .c-tab1__icon {
                @apply text-cm-00;
            }
        }
    }

    &__circle {
        transition: 0.2s ease-in-out;

        @apply
            bg-cm-100
            flex
            h-7
            items-center
            justify-center
            mr-1.5
            rounded-full
            w-7
    }

    &__icon {
        @apply
            text-cm-600
            text-xssm
        ;
    }

    &__count {
        @apply
            text-xs
        ;
    }

    &__dot {
        @apply
            bg-primary-400
            block
            h-1.5
            rounded-full
            w-1.5
        ;
    }
}

</style>
