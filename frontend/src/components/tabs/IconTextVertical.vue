<template>
    <div class="c-icon-text-vertical">
        <BasicTabs
            :tabs="tabs"
            direction="column"
        >
            <template
                #item="{ tab }"
            >
                <Component
                    :is="router ? 'RouterLink' : 'a'"
                    :to="{ name: tab.value, params: tab.params || {} }"
                    class="c-icon-text-vertical__tab text-primary-600"
                    :class="{ 'bg-cm-200': isSelectedTab(tab.value) }"
                    @click="emitTab(tab)"
                >
                    <i
                        class="fa-fw mb-1 text-2xl text-center"
                        :class="tab.icon"
                    >
                    </i>
                    <h6
                        class="font-semibold text-xxs"
                    >
                        {{ tab.name }}
                    </h6>
                </Component>
            </template>

        </BasicTabs>
    </div>
</template>

<script>

import providesTabBasics from '../../vue-mixins/providesTabBasics.js';
import BasicTabs from '@/components/tabs/BasicTabs.vue';

export default {
    name: 'IconTextVertical',
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
        isSelectedTab(value) {
            return value === this.selectedTab;
        },
    },
    created() {

    },
};
</script>

<style scoped>
.c-icon-text-vertical {
    @apply
        flex
        flex-col
        items-center
        px-2
        py-10
    ;

    &__tab {
        min-width: 68px;

        @apply
            border
            border-solid
            border-transparent
            cursor-pointer
            flex
            flex-col
            items-center
            px-2
            py-3
            rounded
            w-full
        ;

        &:hover {
            @apply
                border-cm-200
            ;
        }
    }
}
</style>
