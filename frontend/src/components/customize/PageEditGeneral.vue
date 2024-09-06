<template>
    <div class="o-page-edit-general relative">

        <div class="flex items-center">
            <label class="mr-2 text-sm font-bold">
                Page type:
            </label>
            <TemplateTags
                :dataValue="{ name: pageTypeName }"
                :container="{ style: pageTypeSmall }"
            >
            </TemplateTags>
        </div>

        <FormWrapper
            class="rounded-xl p-6 bg-cm-100 mt-3 relative"
            :form="pageForm"
            @submit="savePage"
        >
            <SettingsHeaderLine
                class="mb-8"
            >
                <template
                    #header
                >
                    {{ $t('labels.name') }}
                </template>

                <div
                    class="max-w-sm"
                >
                    <InputBox
                        formField="name"
                        placeholder="Page name"
                    >
                    </InputBox>
                </div>
            </SettingsHeaderLine>

            <SettingsHeaderLine
                class="mb-8"
            >
                <template
                    #header
                >
                    {{ $t('labels.description') }}
                </template>

                <TextareaField
                    formField="description"
                    placeholder="Add a description"
                    bgColor="white"
                    boxStyle="plain"
                >
                </TextareaField>

            </SettingsHeaderLine>

            <SettingsHeaderLine
                class="mb-8"
            >
                <template
                    #header
                >
                    {{ $t('labels.image') }}
                </template>

                <ImageUploadTemplate
                    boxStyle="stripes"
                    formField="image"
                    onlyCroppedImage
                >
                </ImageUploadTemplate>

            </SettingsHeaderLine>

            <SettingsHeaderLine
                class="mb-8"
            >
                <template
                    #header
                >
                    Folder
                </template>

                <template
                    #description
                >
                    Categorize this page in a folder, either by creating a new folder, or selecting an existing folder.
                </template>

                <div class="max-w-sm mb-4 mt-5">
                    <InputBox
                        v-model="folderName"
                        :showClear="true"
                        placeholder="Folder name"
                    >
                    </InputBox>
                </div>

                <div
                    v-if="foldersLength"
                    class="flex"
                >
                    <span class="font-medium mr-4">Your folders:</span>

                    <div class="flex flex-wrap">
                        <button
                            v-for="folder in folders"
                            :key="folder.val"
                            class="m-1 button-rounded--sm"
                            type="button"
                            :class="folderClass(folder.val)"
                            @click="setFolder(folder.val)"
                        >
                            {{ folder.name }}
                        </button>
                    </div>
                </div>

            </SettingsHeaderLine>

            <SettingsHeaderLine
                class="mb-8"
            >
                <template
                    #header
                >
                    Icon
                </template>

                <IconEdit
                    v-model:symbol="pageForm.symbol"
                >
                </IconEdit>

            </SettingsHeaderLine>

            <SettingsHeaderLine
                v-if="showFiltersOption"
                class="mb-8"
            >
                <template
                    #header
                >
                    Filter
                </template>

                <template
                    v-if="hasFilterOptions && !cannotSetFilter"
                    #description
                >
                    View all "{{ mapping.name }}" data on this page, or add a filter to only load in a subset.
                </template>

                <button
                    v-if="!cannotSetFilter && !pageFormFilter && hasFilterOptions"
                    class="button bg-cm-200 hover:shadow-lg"
                    type="button"
                    @click="addFilter"
                >
                    Add a filter
                </button>

                <div
                    v-if="!hasFilterOptions || cannotSetFilter"
                    class="bg-cm-00 p-4 rounded-xl text-xssm"
                >
                    <p
                        v-t="'customizations.pageWizard.blueprint.subset.cannotCreate.header'"
                        class="font-semibold mb-2"
                    >
                    </p>

                    <template
                        v-if="!hasFilterOptions"
                    >
                        <p
                            v-t="'customizations.pageWizard.blueprint.subset.cannotCreate.description'"
                        >
                        </p>
                    </template>

                    <div
                        v-if="!hasFilterOptions && cannotSetFilter"
                        class="h-divider my-4"
                    >
                    </div>

                    <div
                        v-if="cannotSetFilter"
                    >
                        <p class="mb-2">
                            "{{ mapping.name }}" data is only displayed on this page,
                            and a filter could make some of your data harder to reach.
                        </p>
                        <p>
                            To set a filter, you can create a new page displaying
                            "{{ mapping.name }}" data, and add a filter to that page!
                        </p>
                    </div>
                </div>

                <PageSubsetFilters
                    v-if="pageFormFilter"
                    class="bg-cm-00 p-4 rounded-xl"
                    :pageForm="pageForm"
                    :mapping="mapping"
                    :showClose="pageFormFilter && !pageFilters"
                    @updateForm="updateFilter"
                >
                </PageSubsetFilters>

                <button
                    v-if="pageFilters && pageFormFilter"
                    class="button bg-cm-200 hover:shadow-lg mt-2"
                    type="button"
                    @click="removeFilter"
                >
                    Remove subset filter
                </button>

            </SettingsHeaderLine>

            <SaveButtonSticky
                class="mt-2"
                :disabled="saveTurnedOff"
                :pulse="true"
            >
            </SaveButtonSticky>
        </FormWrapper>

        <div
            class="rounded-xl p-6 bg-cm-100 mt-3"
        >
            <SettingsHeaderLine>
                <template
                    #header
                >
                    <template v-if="cannotDeletePage">
                        This page cannot be deleted
                    </template>
                    <template v-else>
                        Delete this page
                    </template>
                </template>

                <template
                    v-if="isRecordPage"
                    #description
                >
                    <template
                        v-if="isMainSubsetPage"
                    >
                        Other pages - <span class="font-medium">{{ sharedList }}</span> -
                        filter the full data found on this page. If you wish to delete
                        this page, please delete the subset pages first.
                    </template>

                    <template
                        v-else-if="sharedWith"
                    >
                        This page will be deleted, but the associated data is in use on other pages,
                        and will still be available after this page has been deleted.
                    </template>

                    <template v-else>
                        If you delete this page, you will also delete all associated data.
                    </template>
                </template>

                <button
                    v-if="!isMainSubsetPage"
                    v-t="'common.delete'"
                    class="button button-peach"
                    :class="{ unclickable: processingDelete }"
                    type="button"
                    :disabled="processingDelete"
                    @click="openConfirm"
                >
                </button>

            </SettingsHeaderLine>
        </div>

        <div
            v-if="mapping"
            class="rounded-xl p-6 bg-cm-100 mt-3"
        >
            <div class="flex items-center">
                <div class="flex items-center text-sm">
                    <div class="h-8 w-8 circle-center text-primary-600 bg-primary-200 mr-2">
                        <i
                            class="far fa-compass-drafting"
                        >
                        </i>
                    </div>
                    <span
                        class="uppercase font-semibold text-cm-400 mr-2"
                    >
                        {{ $t('common.blueprint') }}:
                    </span>
                </div>

                <span class="font-semibold">
                    {{ mapping.name }}
                </span>
            </div>

            <div
                v-if="isShared"
                class="mt-2"
            >
                <span
                    class="font-medium"
                >
                    Shared with pages:
                </span>

                {{ sharedList }}
            </div>
        </div>
        <ConfirmModal
            v-if="isConfirmOpen"
            @closeModal="closeConfirm"
            @cancelAction="closeConfirm"
            @proceedWithAction="deletePage"
        >
            <template
                v-if="sharedWith || !isRecordPage"
            >
                Are you sure you want to delete this page?
            </template>

            <template
                v-else
            >
                Deleting this page will also delete the blueprint it uses and associated data.

                Please make sure you wish to continue.
            </template>
        </ConfirmModal>
    </div>
</template>

<script>

import PageSubsetFilters from './PageSubsetFilters.vue';
import IconEdit from '@/components/assets/IconEdit.vue';
import ConfirmModal from '@/components/assets/ConfirmModal.vue';
import ImageUploadTemplate from '@/components/images/ImageUploadTemplate.vue';

import interactsWithPageItem from '@/vue-mixins/customizations/interactsWithPageItem.js';
import providesPageGeneralForm from '@/vue-mixins/customizations/providesPageGeneralForm.js';
import interactsWithFormCanSave from '@/vue-mixins/common/interactsWithFormCanSave.js';
import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import interactsWithPageSubsets from '@/vue-mixins/customizations/interactsWithPageSubsets.js';

import { isValueFilled } from '@/core/utils.js';
import { pageTypeSmall } from '@/core/display/systemTagDesigns.js';
import { deletePage, updateMappingPage, updateListPage } from '@/core/repositories/pageRepository.js';

const filter = {
    by: '', // MARKER, FIELD
    fieldId: null,
    match: 'IS', // IS, IS_NOT
    matchValue: null, // id of marker or option of field
};

export default {
    name: 'PageEditGeneral',
    components: {
        IconEdit,
        PageSubsetFilters,
        ConfirmModal,
        ImageUploadTemplate,
    },
    mixins: [
        interactsWithPageItem,
        providesPageGeneralForm,
        interactsWithModal,
        interactsWithFormCanSave,
        interactsWithPageSubsets,
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
        mapping: {
            type: [Object, null],
            default: null,
        },
        space: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'closeModal',
    ],
    data() {
        return {
            isConfirmOpen: false,
            processing: false,
            processingDelete: false,
            requiredFields: [
                'name',
                'symbol',
                {
                    fieldKey: 'singularName',
                    onlyRequiredIfPresent: true,
                },
            ],
            imageUrlStore: 'INITIAL',
        };
    },
    computed: {
        saveTurnedOff() {
            return !this.canSaveInfo
                || this.processing
                || this.processingDelete;
        },
        canSaveInfo() {
            return (this.canSave || this.wasImageChanged)
                && (!this.pageFormFilter
                    || isValueFilled(this.pageFormFilter.matchValue));
        },
        keysToOmit() {
            // Image handled in separate logic due to url/File difference
            return ['image'];
        },
        isImageTheSame() {
            return this.page.image === this.imageUrlStore
                || this.imageUrlStore === 'INITIAL';
        },
        wasImageChanged() {
            return !this.isImageTheSame;
        },
        isEntitiesPage() {
            return this.page.type === 'ENTITIES';
        },
        pageFilters() {
            return this.page.markerFilters || this.page.fieldFilters;
        },
        pageFormFilter() {
            return this.pageForm.filter;
        },
        checkerForm() {
            return this.pageForm;
        },
        checkerOriginal() {
            return this.pageForm._dataCb();
        },
        // hasSingularName() {
        //     return _.has(this.pageForm, 'singularName');
        // },
        folderName: {
            get() {
                return this.pageForm.folder?.replace(/\/$/, '') || '';
            },
            set(val) {
                const newVal = val ? `${val}/` : null;
                this.pageForm.folder = newVal;
            },
        },
        mappingPages() {
            return this.mapping?.pages;
        },
        sharedWith() {
            if (this.mappingPages && this.mappingPages.length > 1) {
                // Anything other than the current one
                return this.mappingPages.filter((page) => {
                    return page.id !== this.page.id;
                });
            }
            return null;
        },
        isShared() {
            return !!this.sharedWith;
        },
        sharedNames() {
            if (this.isShared) {
                return _.map(this.sharedWith, 'name');
            }
            return [];
        },
        isMainSubsetPage() {
            return this.isShared && !this.pageFilters && this.otherPagesHaveFilters;
        },
        cannotSetFilter() {
            return !this.isShared || this.isMainSubsetPage;
        },
        otherPagesHaveFilters() {
            if (!this.isShared) {
                return false;
            }
            return _(this.sharedWith).every((page) => {
                return page.fieldFilters || page.markerFilters;
            });
        },
        cannotDeletePage() {
            return this.isMainSubsetPage;
        },
        sharedList() {
            return _.join(this.sharedNames, ', ');
        },
        pages() {
            return this.space.pages;
        },
        spaceFolders() {
            const folders = _.groupBy(this.pages, 'folder');
            return _(folders).keys().sortBy((key) => key.length !== 0).value();
        },
        withName() {
            return this.spaceFolders?.filter((folder) => {
                return folder;
            });
        },
        foldersLength() {
            return this.folders.length;
        },
        folders() {
            return this.withName?.map((folder) => {
                const name = folder.slice(0, -1);
                return {
                    name,
                    val: folder,
                };
            });
        },
        isListPage() {
            return [
                'TODOS',
                'CALENDAR',
                'DOCUMENTS',
                'PINBOARD',
                'NOTES',
                'LINKS',
            ].includes(this.page.type);
        },
        isRecordPage() {
            return [
                'ENTITY',
                'ENTITIES',
            ].includes(this.page.type);
        },
        imageObj: {
            get() {
                return this.page.image;
            },
            set(val) {
                this.pageForm.image = val;
            },
        },
        showFiltersOption() {
            return this.mapping
                && !this.isLoadingMarkers
                && this.isEntitiesPage;
        },
    },
    methods: {
        folderClass(val) {
            if (this.isFolderSelected(val)) {
                return 'button-primary';
            }
            return 'bg-cm-200 hover:shadow-lg';
        },
        isFolderSelected(val) {
            return val === this.pageForm.folder;
        },
        async savePage() {
            this.processing = true;
            try {
                if (this.isListPage) {
                    await updateListPage(this.pageForm, this.page);
                } else {
                    await updateMappingPage(this.pageForm, this.page);
                }
                // On crop, the crop does not fill the space on save (image not replaced
                // with cropped image).
                // However, reset, which fixes the above, causes other inconsistencies. So
                // commented out for now as it is working fine.
                // this.pageForm.reset();
                this.$saveFeedback();
            } finally {
                this.processing = false;
            }
        },
        updateIcon(icon) {
            this.pageForm.symbol = icon;
            this.closeModal();
        },
        setFolder(val) {
            if (this.isFolderSelected(val)) {
                this.pageForm.folder = null;
            } else {
                this.pageForm.folder = val;
            }
        },
        addFilter() {
            const clone = _.clone(filter);
            this.pageForm.filter = clone;
        },
        updateFilter({ valKey, val }) {
            // When the filter is removed, the filter object is set to null but
            // the child components still emit the update event so we can ignore
            // it here.
            if (!this.pageForm.filter) {
                return;
            }
            if (!valKey) {
                this.pageForm.filter = val;
            } else {
                this.pageForm.filter[valKey] = val;
            }
        },
        removeFilter() {
            this.pageForm.filter = null;
            this.savePage();
        },
        openConfirm() {
            this.isConfirmOpen = true;
        },
        closeConfirm() {
            this.isConfirmOpen = false;
        },
        async deletePage() {
            this.closeConfirm();
            this.processingDelete = true;
            try {
                await deletePage(this.page);
                if (this.$route.params.pageId === this.page.id) {
                    this.$router.push({ name: 'home' });
                } else {
                    this.$emit('closeModal');
                }
            } finally {
                this.processingDelete = false;
            }
        },
    },
    watch: {
        'page.image': {
            handler(val) {
                this.imageUrlStore = val;
            },
        },
        'pageForm.image': {
            handler(val) {
                // Using initial because of the way the image is set with cropper
                if (this.imageUrlStore === 'INITIAL' && this.page.image) {
                    this.imageUrlStore = this.page.image;
                } else {
                    this.imageUrlStore = val;
                }
            },
        },
    },
    created() {
        this.pageTypeSmall = pageTypeSmall;
    },
};
</script>

<style scoped>

/*.o-page-edit-general {
}*/

</style>
