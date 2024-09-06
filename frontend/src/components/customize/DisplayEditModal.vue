<template>
    <Modal
        class="o-display-edit-modal"
        containerClass="w-3/4"
        :containerStyle="{ height: '90vh' }"
        :header="true"
        v-bind="$attrs"
    >
        <template
            #header
        >
            <h1>
                Edit display
            </h1>

        </template>

        <EditFoundation
            class="min-h-0"
            :hideHeader="true"
            :hideTabs="true"
            :tabs="tabs"
            :selectedTab="selectedTab"
            :selectedTabHeader="selectedTabHeader"
            @selectTab="selectTab"
        >
            <component
                :is="selectedComponent"
                class="min-h-0"
                :page="page"
                :mapping="mapping"
            >
            </component>

        </EditFoundation>
    </Modal>
</template>

<script>

import EditFoundation from './EditFoundation.vue';
import DisplayEditOrder from './DisplayEditOrder.vue';

import setsTabSelection from '@/vue-mixins/setsTabSelection.js';

const tabOptions = [
    {
        value: 'ORDER',
        name: 'Order',
        hideDescription: true,
    },
];

export default {
    name: 'DisplayEditModal',
    components: {
        EditFoundation,
        DisplayEditOrder,
    },
    mixins: [
        setsTabSelection,
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            selectedTab: 'ORDER',
        };
    },
    computed: {
        tabs() {
            return tabOptions;
        },
        isNew() {
            return _.isEmpty(this.view)
                || (!this.view.val && !this.view.id);
        },
        selectedTabHeader() {
            return `labels.${_.camelCase(this.selectedTab)}`;
        },
        // selectedTabDescription() {
        //     if (this.selectedTabFull?.hideDescription) {
        //         return '';
        //     }
        //     return {
        //         path: `customizations.editView.tabs.${_.camelCase(this.selectedTab)}.description`,
        //         args: { viewName: this.viewName },
        //     };
        // },
        selectedComponent() {
            return `DisplayEdit${_.pascalCase(this.selectedTab)}`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.o-display-edit-modal {

} */

</style>
