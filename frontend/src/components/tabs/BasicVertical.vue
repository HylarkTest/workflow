<template>
    <div class="c-basic-vertical">
        <BasicTabs
            direction="column"
            :tabs="tabs"
        >
            <template
                #item="{ tab }"
            >
                <component
                    v-if="!tab.header"
                    :ref="setRef(tab)"
                    :is="router ? 'RouterLink' : 'a'"
                    class="c-basic-vertical__link"
                    activeClass="c-basic-vertical__link--selected"
                    :to="{ name: tab.link, params: tab.params || {} }"
                    @click="emitTab(tab)"
                >
                    {{ getTabName(tab) }}
                </component>

                <slot
                    v-if="tab.hasAfterTab"
                    name="afterTab"
                >
                </slot>

                <div
                    v-if="tab.subs && tab.subs.length"
                    :ref="setRef(tab)"
                >
                    <h5
                        class="c-basic-vertical__header flex items-start leading-snug u-hyphens"
                    >
                        <slot
                            name="headerImage"
                            :tab="tab"
                        >
                        </slot>

                        {{ getTabName(tab) }}
                    </h5>

                    <template
                        v-for="sub in tab.subs"
                        :key="sub.value"
                    >
                        <div
                            v-if="sub.hasDividerAbove"
                            class="h-0.5 w-full bg-cm-200"
                        >
                        </div>

                        <component
                            :is="router ? 'RouterLink' : 'a'"
                            class="c-basic-vertical__link"
                            activeClass="c-basic-vertical__link--selected"
                            :to="{ name: sub.link, params: sub.params || {} }"
                            @click="emitTab(sub)"
                        >
                            {{ getTabName(sub) }}
                        </component>
                    </template>

                    <!-- <div
                        v-if="getSeparateTabs(tab.subs).length"
                        class="bg-secondary-100 rounded-xl px-3 py-2 -ml-3"
                    >
                        <component
                            v-for="sub in getSeparateTabs(tab.subs)"
                            :key="sub.value"
                            :is="router ? 'RouterLink' : 'a'"
                            :to="{ name: sub.link, params: sub.params || {} }"
                            class="c-basic-vertical__separate"
                            activeClass="c-basic-vertical__separate--selected"
                        >
                            {{ getTabName(sub) }}
                        </component>
                    </div> -->
                </div>
            </template>
        </BasicTabs>
    </div>
</template>

<script>
import BasicTabs from './BasicTabs.vue';
import providesTabBasics from '../../vue-mixins/providesTabBasics.js';

export default {
    name: 'BasicVertical',
    components: {
        BasicTabs,
    },
    mixins: [
        providesTabBasics,
    ],
    props: {
        tabs: {
            type: Array,
            required: true,
            validator: (val) => {
                // each tab must have a value
                if (val.some((tab) => !tab.value)) {
                    return false;
                }

                // all values must be unique
                const uniqueValues = _.uniq(val.map((tab) => tab.value));
                return uniqueValues.length === val.length;
            },
        },
        paddingClass: {
            type: String,
            default: 'pl-10 pr-8',
        },
    },
    data() {
        return {
            tabRefs: {},
        };
    },
    methods: {
        setRef(tab) {
            return (el) => {
                this.tabRefs[tab.value] = el;
            };
        },
        getTabName(tab) {
            return tab.name || this.$t(tab.namePath);
        },
        getMainTabs(tabs) {
            return tabs.filter((tab) => !tab.separate);
        },
        getSeparateTabs(tabs) {
            return tabs.filter((tab) => tab.separate);
        },
        scrollToTab() {
            const activeTab = this.tabs.find((tab) => this.isActive(tab));
            const activeRef = this.tabRefs[activeTab.value].$el || this.tabRefs[activeTab.value];
            activeRef.scrollIntoView({ block: 'center' });
        },
    },
    mounted() {
        this.$nextTick(() => {
            this.scrollToTab();
        });
    },
};
</script>
<style scoped>
.c-basic-vertical {
    &__header {
        @apply
            font-semibold
            mb-1
            mt-8
            text-cm-400
            text-smbase
        ;
    }

    &__link {
        margin-bottom: 1px;
        margin-top: 1px;
        transition: 0.2s ease-in-out;

        @apply
            block
            cursor-pointer
            -ml-4
            px-4
            py-1.5
            rounded-lg
            text-cm-700
            text-sm
        ;

        &:hover:not(.c-basic-vertical__link--selected) {
            @apply
                bg-cm-100
            ;
        }

        &--selected {
            @apply
                bg-primary-100
                font-semibold
                text-primary-600
            ;
        }
    }

    /*&__separate {

        @apply
            block
            font-medium
            py-1
            text-secondary-700
            text-sm
        ;

        &:hover:not(.c-basic-vertical__link--selected) {
            @apply
                font-semibold
            ;
        }

        &--selected {
            @apply
                font-bold
                text-secondary-800
            ;
        }
    }*/
}
</style>
