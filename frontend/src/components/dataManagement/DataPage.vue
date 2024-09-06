<template>
    <div class="o-data-page">
        <LayoutPage
            :headerProps="headerProps"
        >
            <div
                class="flex flex-1 h-full min-h-0 pb-8 items-start mt-4"
            >
                <div class="o-data-page__tabs">
                    <RoundedVertical
                        :tabs="tabs"
                        :router="router"
                        :paramKey="paramKey"
                        @selectTab="selectTab"
                    >
                    </RoundedVertical>
                </div>
                <component
                    :is="dataComponent"
                    class="flex-1"
                >
                </component>
            </div>
        </LayoutPage>
    </div>
</template>

<script>

import LayoutPage from '@/components/layout/LayoutPage.vue';
import RoundedVertical from '@/components/tabs/RoundedVertical.vue';
import DataImport from '@/components/dataManagement/DataImport.vue';
import DataPreviousImports from '@/components/dataManagement/DataPreviousImports.vue';

import setsTabSelection from '@/vue-mixins/setsTabSelection.js';

export default {
    name: 'DataPage',
    components: {
        LayoutPage,
        RoundedVertical,
        DataImport,
        DataPreviousImports,
    },
    mixins: [
        setsTabSelection,
    ],
    props: {

    },
    data() {
        return {
            headerProps: {
                name: this.$t('links.data'),
                iconProp: 'far fa-database',
                isStickyHeader: true,
            },
            paramKey: 'tab',
            router: true,
        };
    },
    computed: {
        routeTab() {
            return this.$route.params.tab;
        },
        dataComponent() {
            const name = _.pascalCase(this.selectedValue);
            return `Data${name}`;
        },
        selectedValue() {
            return this.selectedTabObject?.value;
        },
        selectedTabObject() {
            return this.tabs.find((tab) => {
                return tab.paramName === this.routeTab;
            });
        },
        tabs() {
            return [
                {
                    value: 'IMPORT',
                    name: 'Import tool',
                    paramName: 'import',
                    link: 'dataManagement',
                },
                {
                    value: 'PREVIOUS_IMPORTS',
                    name: 'Previous imports',
                    paramName: 'previous',
                    link: 'dataManagement',
                },
            ];
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

.o-data-page {
    &__tabs {
        top: calc(144px + var(--g-top-resp));

        @apply
            mr-8
            pl-8
            sticky
            w-48
        ;

        @media (min-width: 768px) {
            top: 144px;
        }
    }
}

</style>
