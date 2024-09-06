<template>
    <Modal
        class="o-view-edit-modal"
        :containerClass="modalWidthClass"
        :containerStyle="modalContainerStyle"
        :header="true"
        v-bind="$attrs"
        @closeModal="closeModal"
    >
        <template
            #header
        >
            <h1>
                {{ whichHeader }}
            </h1>

        </template>

        <EditFoundation
            :key="view?.id"
            class="min-h-0"
            :hideHeader="true"
            :hideTabs="isNew"
            :tabs="tabs"
            :selectedTab="selectedTab"
            :selectedTabHeader="selectedTabHeader"
            :selectedTabDescription="selectedTabDescription"
            @selectTab="selectTab"
        >
            <component
                :is="selectedComponent"
                class="min-h-0"
                :allAvailableData="allAvailableData"
                :page="page"
                :view="view"
                :viewName="viewName"
                @viewCreated="viewCreated"
                @closeModal="closeModal"
            >
            </component>

        </EditFoundation>
    </Modal>
</template>

<script>

// import ViewEditBirdseye from './ViewEditBirdseye.vue';
// import ViewEditDashboard from './ViewEditDashboard.vue';
import EditFoundation from './EditFoundation.vue';
import ViewEditNew from './ViewEditNew.vue';
import ViewEditGeneral from './ViewEditGeneral.vue';
import ViewEditAppearance from './ViewEditAppearance.vue';
import ViewEditDesign from './ViewEditDesign.vue';
import ViewEditColumns from './ViewEditColumns.vue';
import ViewEditData from './ViewEditData.vue';

import setsTabSelection from '@/vue-mixins/setsTabSelection.js';

import { allData } from '@/core/display/getAllEntityData.js';

import { isActiveBasePersonal } from '@/core/repositories/baseRepository.js';

const tabOptions = {
    NEW: {
        value: 'NEW',
        name: 'New',
        hideDescription: true,
    },
    GENERAL: {
        value: 'GENERAL',
        name: 'General',
        hideDescription: true,
    },
    COLUMNS: {
        value: 'COLUMNS',
        name: 'Columns',
    },
    DESIGN: {
        value: 'DESIGN',
        name: 'Design',
    },
    DATA: {
        value: 'DATA',
        name: 'Data',
    },
    APPEARANCE: {
        value: 'APPEARANCE',
        name: 'Appearance',
    },
};

const relevantLists = {
    LINE: [
        'DESIGN',
        'DATA',
        'APPEARANCE',
    ],
    KANBAN: [
        'DESIGN',
        'DATA',
        'APPEARANCE',
    ],
    TILE: [
        'DESIGN',
        'DATA',
        'APPEARANCE',
    ],
    SPREADSHEET: [
        'COLUMNS',
        'APPEARANCE',
    ],
};

export default {
    name: 'ViewEditModal',
    components: {
        // ViewEditBirdseye,
        // ViewEditDashboard,
        EditFoundation,
        ViewEditNew,
        ViewEditGeneral,
        ViewEditDesign,
        ViewEditAppearance,
        ViewEditColumns,
        ViewEditData,
    },
    mixins: [
        setsTabSelection,
    ],
    props: {
        view: {
            type: [Object, null],
            required: true,
        },
        page: {
            type: Object,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'viewCreated',
        'closeModal',
    ],
    data() {
        return {
            selectedTab: null,
        };
    },
    computed: {
        // viewEditComponent() {
        //     return `ViewEdit${_.pascalCase(this.view.viewType)}`;
        // },
        viewName() {
            if (this.isNew) {
                return '';
            }
            return this.view.name || this.$t(`views.dashboard.${_.camelCase(this.view.viewType)}`);
        },
        whichHeader() {
            return this.isNew ? 'Add a new view' : `Edit view - ${this.viewName}`;
        },
        isNew() {
            return _.isObject(this.view) && !this.view.id;
        },
        modalWidthClass() {
            return this.isNew ? 'w-96' : 'w-4/5';
        },
        modalContainerStyle() {
            return this.isNew ? {} : { height: '80vh' };
        },
        selectedTabHeader() {
            return `labels.${_.camelCase(this.selectedTab)}`;
        },
        selectedTabFull() {
            return _.find(this.validTabs, { value: this.selectedTab }) || {};
        },
        selectedTabDescription() {
            if (this.selectedTabFull?.hideDescription) {
                return '';
            }
            return {
                path: `customizations.editView.tabs.${_.camelCase(this.selectedTab)}.description`,
                args: { viewName: this.viewName },
            };
        },
        firstTab() {
            return this.isNew ? 'NEW' : 'GENERAL';
        },
        selectedComponent() {
            return `ViewEdit${_.pascalCase(this.selectedTab)}`;
        },
        tabs() {
            if (this.isNew) {
                return [tabOptions.NEW];
            }
            // return [];
            return this.validTabs;
        },
        validTabs() {
            if (!this.isNew) {
                let list = ['GENERAL'];
                list = list.concat(relevantLists[this.view.viewType]);
                return list.map((item) => {
                    return tabOptions[item];
                });
            }
            return [];
        },
        allAvailableData() {
            const exclusions = [];
            if (isActiveBasePersonal()) {
                exclusions.push('COLLABORATION');
            }
            return allData(this.mapping, exclusions);
        },
    },
    methods: {
        viewCreated(event) {
            this.selectedTab = 'GENERAL';
            this.$emit('viewCreated', event);
        },
        closeModal() {
            this.$emit('closeModal');
        },
        setInitialTab() {
            if (this.isNew) {
                this.selectedTab = 'NEW';
            } else {
                this.selectedTab = this.firstTab;
            }
        },
    },
    created() {
        this.setInitialTab();
    },
};
</script>

<style scoped>

/*.o-view-edit-modal {

} */

</style>
