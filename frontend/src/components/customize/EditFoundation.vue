<template>
    <div class="c-edit-foundation h-full flex flex-col relative min-h-0">
        <LabelHeader
            v-if="!hideHeader"
            :labelPath="headerLabelPath"
        >
            <slot name="headerName">
            </slot>

            <template #extra>
                <slot name="headerExtra">
                </slot>
            </template>

        </LabelHeader>

        <div class="flex-1 flex h-full min-h-0">
            <CollapsableMenu
                :contentOnly="contentOnly"
                :isSideVisible="isSideVisible"
                :forceResponsiveDisplay="forceResponsiveDisplay"
                @showSide="showSide"
            >
                <template #menu>
                    <component
                        :is="tabComponent"
                        class="overflow-y-auto h-full"
                        :class="tabClasses"
                        :selectedTab="selectedTab"
                        :tabs="tabs"
                        :forceResponsiveDisplay="forceResponsiveDisplay"
                        @selectTab="selectTab"
                    >
                    </component>
                </template>

                <template #content>
                    <div
                        class="c-edit-foundation__content min-h-full"
                        :class="forceResponsiveDisplay ? 'pt-8' : 'pt-4'"
                    >
                        <div
                            v-if="!contentOnly"
                            class="mb-4"
                        >
                            <h2
                                v-t="selectedTabHeader"
                                class="text-xl font-bold text-cm-700"
                            >
                            </h2>
                            <p
                                v-if="selectedTabDescription"
                                v-t="selectedTabDescription"
                                class="text-gray-500 text-sm"
                            >
                            </p>
                        </div>
                        <slot>
                        </slot>
                    </div>
                </template>
            </CollapsableMenu>
        </div>
    </div>
</template>

<script>

import RoundedVertical from '@/components/tabs/RoundedVertical.vue';
import IconVertical from '@/components/tabs/IconVertical.vue';
import LabelHeader from '@/components/assets/LabelHeader.vue';

import interactsWithCollapsableMenu from '@/vue-mixins/interactsWithCollapsableMenu.js';

export default {
    name: 'EditFoundation',
    components: {
        RoundedVertical,
        LabelHeader,
        IconVertical,
    },
    mixins: [
        interactsWithCollapsableMenu,
    ],
    props: {
        defaultTab: {
            type: String,
            default: '',
        },
        headerLabelPath: {
            type: String,
            default: '',
        },
        tabs: {
            type: Array,
            required: true,
        },
        selectedTab: {
            type: String,
            required: true,
        },
        selectedTabHeader: {
            type: String,
            required: true,
        },
        selectedTabDescription: {
            type: [String, Object],
            default: '',
        },
        hideHeader: Boolean,
        tabComponent: {
            type: String,
            default: 'RoundedVertical',
            validator(val) {
                return ['RoundedVertical', 'IconVertical'].includes(val);
            },
        },
        tabClasses: {
            type: String,
            default: 'pl-4 py-4 w-32',
        },
        hideTabs: Boolean,
    },
    emits: [
        'selectTab',
    ],
    data() {
        return {
        };
    },
    computed: {
        contentOnly() {
            return this.hideTabs;
        },
    },
    methods: {
        selectTab(tab) {
            this.$emit('selectTab', tab);
            this.hideSide();
        },

    },
    created() {
    },
    mounted() {
        this.onResize();
    },
};
</script>

<style scoped>

.c-edit-foundation {
    &__content {
        @apply
            p-4
            pt-8
        ;
    }
}

</style>
