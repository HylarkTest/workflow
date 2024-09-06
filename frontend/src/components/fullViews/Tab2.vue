<template>
    <div class="c-tab2">
        <BasicTabs
            :tabs="tabs"
            direction="column"
            :selectedTab="selectedTab"
        >
            <template
                #item="{ tab }"
            >
                <component
                    :is="linkComponent"
                    :to="{ name: tab.link, params: { [paramKey]: tab.paramName } }"
                    class=""
                    :class="isActive(tab)
                        ? ''
                        : ''"
                    type="button"
                    @click="emitTab(tab)"
                >
                    <i
                        class="fal"
                        :class="tab.icon"
                    >
                    </i>
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
    name: 'Tab2',
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

/*.c-tab2 {

} */

</style>
