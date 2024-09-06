<template>
    <div
        class="c-icon-vertical"
        :class="{ 'c-icon-vertical--responsive': forceResponsiveDisplay }"
    >
        <BasicTabs
            direction="column"
            :tabs="tabs"
        >
            <template
                #item="{ tab, index }"
            >
                <div
                    class="c-icon-vertical__tab relative"
                    :class="tabString(tab)"
                >

                    <component
                        :is="router ? 'RouterLink' : 'button'"
                        class="flex p-4 w-full transition-2eio"
                        :to="{ name: tab.link, params: tab.params || {} }"
                        type="button"
                        @click="emitTab(tab)"
                    >
                        <div
                            class="c-icon-vertical__circle circle-center h-7 w-7 mr-2 bg-primary-100"
                            :class="{ 'bg-primary-600': activeClass(tab) }"
                        >
                            <i
                                v-if="tab.icon"
                                class="fa-fw"
                                :class="[tab.icon, iconString(tab)]"
                            >
                            </i>
                        </div>
                        <div class="flex-1">
                            <h4
                                class="text-smbase mb-0.5"
                                :class="{ 'font-semibold': activeClass(tab) }"
                            >
                                {{ getTabTitle(tab) }}
                            </h4>
                            <p
                                class="c-icon-vertical__subtitle"
                            >
                                {{ getTabSubtitle(tab) }}
                            </p>
                        </div>

                    </component>

                    <div
                        v-show="!isLast(index)"
                        class="c-icon-vertical__bar"
                        :class="{ 'c-icon-vertical__bar--responsive': forceResponsiveDisplay }"
                    >

                    </div>

                </div>
            </template>
        </BasicTabs>
    </div>
</template>

<script>

import BasicTabs from './BasicTabs.vue';

import providesTabBasics from '../../vue-mixins/providesTabBasics.js';

export default {

    name: 'IconVertical',
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
        tabsLength() {
            return this.tabs.length;
        },
    },
    methods: {
        activeClass(tab) {
            return this.isActive(tab) && !this.forceResponsiveDisplay;
        },
        tabString(tab) {
            return this.activeClass(tab)
                ? 'c-icon-vertical--selected'
                : 'c-icon-vertical--inactive';
        },
        iconString(tab) {
            return this.activeClass(tab)
                ? 'c-icon-vertical__icon--selected'
                : 'c-icon-vertical__icon--inactive';
        },
        isLast(index) {
            return index === (this.tabsLength - 1);
        },
    },
    created() {

    },
};
</script>

<style scoped>
.c-icon-vertical {

    @apply
        block
        mr-1
        shadow-xl
        w-48
    ;

    &--responsive {
        @apply
            mr-0
            shadow-none
            w-full
        ;
    }

    &__subtitle {
        @apply
            leading-tight
            text-cm-500
            text-xs
        ;
    }

    &--selected {
        &:hover {
            .c-icon-vertical__circle {
                @apply
                    bg-primary-500
                ;
            }
        }
    }

    &--inactive {
        &:hover {
            .c-icon-vertical__circle {
                @apply
                    bg-primary-200
                ;
            }
        }
    }

    &__circle {
        transition: 0.2s ease-in-out;
    }

    &__icon {
        &--inactive {
            @apply
                text-primary-600
            ;
        }
        &--selected {
            @apply
                text-cm-00
            ;
        }
    }

    &__bar {
        left: calc(50% - 20px);
        width: 40px;

        @apply
            absolute
            bg-cm-200
            bottom-0
            h-px
        ;

        &--responsive {
            left: 5%;
            width: 90%;
        }
    }
}
</style>
