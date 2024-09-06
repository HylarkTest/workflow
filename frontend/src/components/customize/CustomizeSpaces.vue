<template>
    <CustomizeFoundation
        class="o-customize-spaces"
    >

        <template #header>
            {{ spaceTitle }}
        </template>

        <template #besideHeader>
            <button
                class="button-primary button--sm"
                type="button"
                @click="openNewModal"
            >
                <i
                    class="fal fa-plus-circle mr-1"
                >
                </i>
                {{ $t('customizations.spaces.add') }}
            </button>
        </template>

        <ExpandCollapseAllButton
            class="mb-3"
            :allSectionsExpanded="allSpacesExpanded"
            @toggleAllOpenState="toggleAllSpacesOpenState"
        >
        </ExpandCollapseAllButton>

        <div
            v-for="space in groupedPages"
            :key="space.id"
            class="customize__container mb-6 last:mb-0"
        >
            <CustomizeSpace
                :space="space"
                :pageBeingDeleted="pageBeingDeleted"
                :isExpanded="isExpanded(space)"
                @openSpaceEdit="openSpaceEdit"
                @openPageCreation="openPageCreation"
                @openPageEdit="openDataModal"
                @toggleExpandCollapse="toggleSpaceExpandCollapse(space)"
            >
            </CustomizeSpace>
        </div>

        <Modal
            v-if="isModalOpen && selectedSpace"
            containerClass="w-3/4"
            :containerStyle="{ height: '80vh' }"
            @closeModal="closeEditModal"
        >
            <SpaceEdit
                :space="selectedSpace"
                :defaultTab="selectedTab"
                @closeModal="closeEditModal"
            >
            </SpaceEdit>
        </Modal>

        <Modal
            v-if="isNewOpen"
            containerClass="p-4 w-600p"
            :containerStyle="{ height: '320px' }"
            @closeModal="closeNewModal"
        >
            <SpaceNew
                @saveNewSpace="saveNewSpace"
            >
            </SpaceNew>
        </Modal>

        <PageWizardDialog
            v-if="isDialogOpen"
            :space="selectedSpace"
            :potentialPage="selectedPotentialPage"
            :initialPageWizardStep="initialPageWizardStep"
            @closeFullDialog="closePageCreation"
        >
        </PageWizardDialog>

        <Modal
            v-if="selectedData"
            containerClass="w-3/4"
            :containerStyle="{ height: '80vh' }"
            @closeModal="closeDataModal"
        >
            <DataEdit
                :page="selectedData.page"
                :blueprint="selectedData.blueprint"
                :defaultView="selectedData.selectedView"
                @closeModal="closeToDelete"
            >
            </DataEdit>
        </Modal>
    </CustomizeFoundation>
</template>

<script>

import SpaceEdit from './SpaceEdit.vue';
import CustomizeFoundation from './CustomizeFoundation.vue';
import CustomizeSpace from './CustomizeSpace.vue';
import SpaceNew from './SpaceNew.vue';
import DataEdit from './DataEdit.vue';
import ExpandCollapseAllButton from '@/components/buttons/ExpandCollapseAllButton.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import providesSpaceFolderHelpers from '@/vue-mixins/providesSpaceFolderHelpers.js';
// import interactsWithSupportWidget from '@/vue-mixins/support/interactsWithSupportWidget.js';

import { isActiveBasePersonal } from '@/core/repositories/baseRepository.js';

import interactsWithNewPageDialog from '@/vue-mixins/customizations/interactsWithNewPageDialog.js';
import { createSpace } from '@/core/repositories/spaceRepository.js';
// import { extraColorsList } from '@/core/display/accentColors.js';

import { arrRemove } from '@/core/utils.js';

export default {
    name: 'CustomizeSpaces',
    components: {
        SpaceEdit,
        CustomizeFoundation,
        CustomizeSpace,
        ExpandCollapseAllButton,
        SpaceNew,
        DataEdit,
    },
    mixins: [
        interactsWithModal,
        interactsWithNewPageDialog,
        providesSpaceFolderHelpers,
        // interactsWithSupportWidget,
    ],
    props: {
        spaces: {
            type: Array,
            required: true,
        },
    },
    emits: [
    ],
    data() {
        return {
            selectedSpace: null,
            selectedPotentialPage: null,
            selectedTab: '',
            isNewOpen: false,
            selectedData: null,
            pageBeingDeleted: null,
            initialPageWizardStep: '',
            collapsedSpaces: [],
        };
    },
    computed: {
        groupedPages() {
            return _(this.spaces).map((space) => {
                const folders = space.pages?.length ? this.groupedByFolder(space.pages) : [];
                return {
                    id: space.id,
                    name: space.name,
                    folders,
                };
            }).value();
        },
        isPersonalActive() {
            return isActiveBasePersonal();
        },
        spaceTitle() {
            return this.isPersonalActive ? 'My spaces' : 'Spaces';
        },
        spaceIds() {
            return this.groupedPages.map((space) => space.id);
        },
        allSpacesExpanded() {
            return this.collapsedSpaces.length === 0;
        },
        // supportPropsObj() {
        //     return {
        //         sectionName: 'Spaces',
        //         val: 'CUSTOMIZE_SPACES',
        //     };
        // },
    },
    methods: {
        openPageCreation({ space, page, initialStep }) {
            this.selectedSpace = space;
            this.selectedPotentialPage = page;
            this.initialPageWizardStep = initialStep;
            this.openFullDialog();
        },
        closePageCreation() {
            this.selectedSpace = null;
            this.selectedPotentialPage = null;
            this.initialPageWizardStep = '';
            this.closeFullDialog();
        },
        closeEditModal() {
            this.isModalOpen = false;
            this.selectedSpace = null;
            this.selectedTab = '';
        },
        openSpaceEdit(space, tab) {
            this.isModalOpen = true;
            this.selectedSpace = space;
            this.selectedTab = tab;
        },
        async saveNewSpace(form) {
            await createSpace(form);
            this.closeNewModal();
        },
        closeNewModal() {
            this.isNewOpen = false;
        },
        openNewModal() {
            this.isNewOpen = true;
        },
        getColors(row) {
            return _.split(row, ' ');
        },
        closeDataModal() {
            this.selectedData = null;
        },
        openDataModal(event) {
            this.selectedData = event;
        },
        closeToDelete() {
            this.pageBeingDeleted = this.selectedData.page;
            this.closeDataModal();
        },
        isExpanded(space) {
            return !this.collapsedSpaces.includes(space.id);
        },
        toggleAllSpacesOpenState() {
            if (this.allSpacesExpanded) {
                this.collapsedSpaces = this.spaceIds;
            } else {
                this.collapsedSpaces = [];
            }
        },
        toggleSpaceExpandCollapse(space) {
            if (this.isExpanded(space)) {
                this.collapsedSpaces.push(space.id);
            } else {
                this.collapsedSpaces = arrRemove(this.collapsedSpaces, space.id);
            }
        },
    },
    watch: {
        groupedPages(newSpaces) {
            if (this.selectedSpace?.id) {
                this.selectedSpace = _.find(newSpaces, ['id', this.selectedSpace.id]);
            }
        },
    },
    created() {
        // this.extraColorsList = extraColorsList;
    },
};
</script>

<style scoped>

/*.o-customize-spaces {
}*/

</style>
