<template>
    <div class="c-rounded-vertical">
        <BasicTabs
            :tabs="tabs"
            direction="column"
            :selectedTab="selectedTab"
        >
            <template
                #item="{ tab }"
            >

                <div
                    v-if="tab.label"
                >
                    <h4
                        class="c-rounded-vertical__label"
                    >
                        {{ tab.name }}
                    </h4>

                    <div class="flex flex-col items-start">
                        <component
                            v-for="subTab in tab.sub"
                            :key="subTab.value"
                            :is="linkComponent"
                            :to="{ name: tab.link, params: { [paramKey]: tab.paramName } }"
                            class="c-rounded-vertical__button"
                            :class="subTab.value === selectedTab
                                ? 'bg-primary-700 font-semibold text-cm-00'
                                : 'hover:bg-cm-200'"
                            type="button"
                            :name="subTab.name"
                            @click="emitTab(subTab)"
                        >
                            {{ subTab.name }}
                        </component>
                    </div>
                </div>

                <component
                    v-else
                    :is="linkComponent"
                    :to="{ name: tab.link, params: { [paramKey]: tab.paramName } }"
                    class="c-rounded-vertical__button inline-block"
                    :class="isActive(tab)
                        ? 'bg-primary-100 font-semibold text-primary-600'
                        : 'hover:bg-cm-100'"
                    :name="tab.name"
                    @click="emitTab(tab)"
                >
                    {{ tab.name }}
                </component>
            </template>
        </BasicTabs>
    </div>
</template>

<script>

import BasicTabs from '@/components/tabs/BasicTabs.vue';

import providesTabBasics from '@/vue-mixins/providesTabBasics.js';

export default {
    name: 'RoundedVertical',
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
        linkComponent() {
            return this.router ? 'RouterLink' : 'button';
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>
.c-rounded-vertical {
    &__label {
        @apply
            font-semibold
            mb-2
            mt-5
            px-2
            text-cm-600
            text-sm
            uppercase
        ;
    }

    &__button {
        @apply
            mb-0.5
            px-4
            py-1
            rounded-lg
            text-sm
        ;
    }
}
</style>
