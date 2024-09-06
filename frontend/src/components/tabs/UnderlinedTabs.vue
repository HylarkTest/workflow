<template>
    <div class="o-underlined-tabs">
        <BasicTabs
            :tabs="tabs"
        >
            <template #item="{ tab }">
                <div
                    class="o-underlined-tabs__tab"
                    :class="buttonClass(tab)"
                >
                    <button
                        class="button"
                        type="button"
                        @click="emitTab(tab)"
                    >
                        <i
                            v-if="tab.icon"
                            class="mr-1"
                            :class="tab.icon"
                        >
                        </i>
                        {{ getTabTitle(tab) }}
                    </button>
                </div>
            </template>
        </BasicTabs>
    </div>
</template>

<script>

import BasicTabs from './BasicTabs.vue';

import providesTabBasics from '@/vue-mixins/providesTabBasics.js';

export default {
    name: 'UnderlinedTabs',
    components: {
        BasicTabs,
    },
    mixins: [
        providesTabBasics,
    ],
    props: {
    },
    emits: [
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        buttonClass(tab) {
            return this.isActive(tab)
                ? 'text-primary-600 border-primary-500'
                : 'text-gray-500 border-gray-500';
        },
    },
    created() {
    },
};
</script>

<style scoped>
.o-underlined-tabs {
    &__tab {
        @apply
            border-b-2
            border-solid
        ;
    }
}
</style>
