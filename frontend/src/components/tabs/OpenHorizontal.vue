<template>
    <div class="c-open-horizontal">
        <BasicTabs
            class="bg-cm-00"
            :tabs="tabs"
        >
            <template
                #item="{ tab }"
            >
                <div
                    class="c-open-horizontal__tab"
                    :class="tabString(tab)"
                >
                    <button
                        v-if="!tab.header"
                        type="button"
                        class="cursor-pointer px-4 py-3"
                        @click="emitTab(tab)"
                    >
                        <i
                            v-if="tab.icon"
                            class="mr-1"
                            :class="tab.icon"
                        >
                        </i>
                        {{ tab.name }}
                    </button>
                </div>
            </template>
        </BasicTabs>
        <div class="c-open-horizontal__border">

        </div>
    </div>
</template>

<script>

import BasicTabs from './BasicTabs.vue';

import providesTabBasics from '../../vue-mixins/providesTabBasics.js';

export default {

    name: 'OpenHorizontal',
    components: {
        BasicTabs,
    },
    mixins: [
        providesTabBasics,
    ],
    props: {

    },
    data() {
        return {

        };
    },
    computed: {
    },
    methods: {
        tabString(tab) {
            return this.selectedTab === tab.value
                ? 'c-open-horizontal--selected'
                : 'c-open-horizontal--inactive hover:bg-cm-100';
        },
    },
    created() {

    },
};
</script>

<style scoped>
.c-open-horizontal {
    @apply
        flex
        relative
        w-full
    ;

    &__tab {
        @apply
            border-cm-400
            border-solid
        ;
    }

    &--selected {
        @apply
            border-l
            border-r
            border-t
            rounded-t-lg
            text-cm-800
        ;
    }

    &--inactive {
        @apply
            border-b
            pl-1p
            pr-1p
            pt-1p
            text-cm-600
        ;
    }

    &__border {
        height: 1px;
        @apply
            absolute
            bg-cm-400
            bottom-0
            left-0
            w-full
            -z-1
        ;
    }
}
</style>
