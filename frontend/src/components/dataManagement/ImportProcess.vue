<template>
    <div class="o-import-process">
        <template
            v-if="!isImporting && !showStatus"
        >
            <div
                class="bg-sky-100 p-4 rounded-xl mb-6 flex items-start"
            >
                <span
                    class="text-sky-600 bg-sky-200 rounded px-3 py-1 text-lg font-bold"
                >
                    Beta
                </span>

                <div class="ml-4">
                    <p class="mb-1">
                        This is a new feature!
                    </p>
                    <p class="mb-1">
                        We're working hard behind to scenes to make it better and add more options.
                    </p>
                    <p>
                        If you encounter any issues,
                        have any feedback,
                        or there's something you'd like to see added to the import tool,
                        do not hesitate to reach out at
                        <a
                            class="underline text-sky-700"
                            href="mailTo:hello@hylark.com"
                        >
                            hello@hylark.com
                        </a>.
                    </p>
                </div>
            </div>

            <ol class="list-decimal">
                <li class="mb-10 ml-4">
                    <h4 class="font-semibold mb-2">
                        {{ $t('imports.prompts.whichBlueprint') }}
                    </h4>
                    <div
                        v-if="blueprintsLength"
                        class="w-64"
                    >
                        <BlueprintPicker
                            v-model="parseForm.mappingId"
                            class="w-full"
                            :spaceId="null"
                            property="id"
                            :error="mappingIdWarning"
                            alertColor="gold"
                        >
                        </BlueprintPicker>
                    </div>
                    <div
                        v-else-if="!isLoadingBlueprints"
                        class="bg-gold-100 p-4 rounded-xl inline-flex flex-col"
                    >
                        <p class="mb-2">
                            You do not have any blueprints on this base.
                        </p>

                        <p>
                            Add a records page to get started with imports.
                        </p>
                    </div>
                </li>

                <li class="mb-10 ml-4">
                    <h4 class="font-semibold mb-2">
                        {{ $t('imports.prompts.name') }}
                    </h4>
                    <div class="w-64">
                        <InputBox
                            v-model="form.name"
                            placeholder="Import name"
                        >
                        </InputBox>
                    </div>
                </li>

                <li class="mb-10 ml-4">
                    <h4 class="font-semibold mb-2">
                        {{ $t('imports.prompts.upload') }}
                    </h4>
                    <div>
                        <DocumentUpload
                            v-model="parseForm.file"
                            acceptedFileTypes=".csv,.xlsx,.xls"
                            isNew
                        >
                        </DocumentUpload>
                    </div>
                </li>

                <li
                    class="mb-10 ml-4"
                    :class="{
                        'opacity-40': !fileData,
                    }"
                >
                    <h4 class="font-semibold mb-2">
                        {{ $t('imports.prompts.mapColumns') }}
                    </h4>

                    <LoaderFetch
                        v-if="loadingParsed"
                        class="my-10"
                        :isFull="true"
                        :sphereSize="50"
                    >
                    </LoaderFetch>

                    <div
                        v-if="fileData"
                    >
                        <div
                            class="p-4 bg-secondary-100 rounded-xl mb-2"
                        >
                            <p>
                                {{ $t('imports.warnings.wrongHeaders') }}
                            </p>

                            <button
                                class="button button-secondary mt-2"
                                type="button"
                                @click="openModal"
                            >
                                {{ $t('common.learnMore') }}
                            </button>
                        </div>

                        <FieldsMap
                            v-if="fileData"
                            v-model="form.columnMap"
                            :mapping="mapping"
                            :columns="fileData"
                        >
                        </FieldsMap>
                    </div>
                </li>

                <li
                    class="mb-10 ml-4 relative"
                    :class="{
                        'opacity-40': !isAtPreviewStep,
                    }"
                >
                    <h4
                        v-t="'imports.prompts.check'"
                        class="font-semibold mb-2"
                    >
                    </h4>

                    <div
                        v-if="isAtPreviewStep && !canCheckData"
                        class="p-4 bg-secondary-100 rounded-xl"
                    >
                        <p
                            v-t="'imports.warnings.mapName'"
                            class="mb-2"
                        >
                        </p>

                        <p
                            v-md-text="$t('imports.warnings.nameField', {
                                mappingName,
                                systemNameField,
                            })"
                        >
                        </p>
                    </div>

                    <ImportCheck
                        v-if="canCheckData"
                        :mapping="mapping"
                        :form="form"
                        :fileData="fileData"
                        :previewErrors="previewErrors"
                        :preview="preview"
                    >
                    </ImportCheck>
                </li>
            </ol>

            <div class="mt-6 flex flex-col items-start">
                <p class="mb-2 font-bold text-lg">
                    {{ $t('imports.prompts.ready') }}
                </p>

                <div
                    v-if="showNameWarning"
                    class="bg-peach-100 rounded-xl px-4 py-2 mb-2 inline-block"
                >
                    {{ $t('imports.warnings.giveName') }}
                </div>
                <SaveButton
                    textPath="common.import"
                    :disabled="loadingImport || !canImportData"
                    @save="importFile"
                >
                </SaveButton>
            </div>
        </template>

        <ImportProgress
            v-if="isImporting && importProgress"
            class="bg-cm-00 rounded-xl p-4"
            :importProgress="importProgress"
            :importName="form.name"
            :mappingName="mappingName"
            :fileName="parseForm.file?.name"
        >
        </ImportProgress>

        <ImportStatus
            v-if="showStatus"
            class="bg-cm-00 p-4 rounded-xl"
            :importName="form.name"
            :mappingName="mappingName"
            :status="status"
            @goBack="startNewImport"
        >
        </ImportStatus>

        <SupportModal
            v-if="isModalOpen"
            sectionTitle="Imports"
            sectionName="Imports"
            :relevantTopics="['imports']"
            viewedArticleFriendlyUrlProp="how-to-prepare-a-file-for-import-on-hylark"
            @closeModal="closeModal"
        >
        </SupportModal>
    </div>
</template>

<script>

import BlueprintPicker from '@/components/pickers/BlueprintPicker.vue';
import DocumentUpload from '@/components/assets/DocumentUpload.vue';
import FieldsMap from '@/components/dataManagement/FieldsMap.vue';
import ImportCheck from '@/components/dataManagement/ImportCheck.vue';
import ImportProgress from '@/components/dataManagement/ImportProgress.vue';
import ImportStatus from '@/components/dataManagement/ImportStatus.vue';
import SupportModal from '@/components/support/SupportModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import MAPPING from '@/graphql/mappings/queries/Mapping.gql';
import IMPORT from '@/graphql/imports/Import.gql';
import MAPPINGS from '@/graphql/mappings/queries/Mappings.gql';
import PROGRESS_TRACKER_UPDATED from '@/graphql/progress-tracker/ProgressTrackerUpdated.gql';
import { parseImportFile, importFile, previewFile } from '@/core/repositories/importsRepository.js';
import { removeTypename } from '@/core/helpers/apolloHelpers.js';
import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';

export default {
    name: 'ImportProcess',
    components: {
        BlueprintPicker,
        DocumentUpload,
        FieldsMap,
        ImportCheck,
        ImportProgress,
        ImportStatus,
        SupportModal,
    },
    mixins: [
        interactsWithModal,
        interactsWithApolloQueries,
    ],
    props: {

    },
    apollo: {
        mapping: {
            query: MAPPING,
            variables() {
                return {
                    id: this.parseForm.mappingId,
                };
            },
            skip() {
                return !this.parseForm.mappingId;
            },
            update: (data) => data.mapping,
            fetchPolicy: 'cache-first',
        },
        import: {
            query: IMPORT,
            variables() {
                return { id: this.importId };
            },
            skip() {
                return !this.importId || this.import?.progress.status === 'COMPLETED';
            },
            fetchPolicy: 'cache-first',
            subscribeToMore: {
                document: PROGRESS_TRACKER_UPDATED,
                variables() {
                    return {
                        taskId: this.importId,
                    };
                },
            },
            pollInterval: 10_000,
        },
        mappings: {
            query: MAPPINGS,
            update: (data) => initializeConnections(data).mappings,
        },
    },
    data() {
        return {
            // parseForm returns the spreadsheet and returns a response that guesses the fields
            parseForm: this.$apolloForm({
                mappingId: null,
                file: null,
                fileId: null,
            }),
            // form is the actual form that is submitted for the import
            form: this.$apolloForm({
                mappingId: null,
                file: null,
                fileId: null,
                name: '',
                columnMap: [],
                dateFormat: null,
            }),
            previewForm: this.$apolloForm({
                file: null,
                fileId: null,
                columnMap: [],
                dateFormat: null,
            }),
            fileData: null,
            loadingParsed: false, // Loading to do with parseForm
            mappingIdWarning: null,
            preview: null,
            previewErrors: null,
            loadingPreview: false, // Loading to do with getting the preview
            page: 1,
            previewHasMore: false,
            loadingImport: false, // Loading to do with starting the import
            importId: null,
        };
    },
    computed: {
        blueprintsLength() {
            return this.mappings?.length;
        },
        isLoadingBlueprints() {
            return this.$isLoadingQueries(['mappings']);
        },
        showStatus() {
            return this.showSuccess || this.showFailure;
        },
        status() {
            return this.importProgress?.status;
        },
        showFailure() {
            return this.isFailed;
        },
        isCompleted() {
            return this.status === 'COMPLETED';
        },
        isFailed() {
            return this.status === 'FAILED';
        },
        showSuccess() {
            return this.isCompleted;
        },
        importProgress() {
            return this.import?.progress;
        },
        progressAndNotCompleted() {
            return !!this.importProgress && !this.isCompleted && !this.isFailed;
        },
        isImporting() {
            return this.loadingImport || this.progressAndNotCompleted;
        },
        mappingFields() {
            return this.mapping?.fields;
        },
        mappingName() {
            return this.mapping?.name;
        },
        systemNameField() {
            return this.mappingFields?.find((field) => field.type === 'SYSTEM_NAME')?.name;
        },
        isAtPreviewStep() {
            return this.parseForm.mappingId
                && !this.loadingPreview
                && !this.loadingParsed
                && (this.parseForm.file || this.parseForm.fileId)
                && this.form.columnMap;
        },
        canImportData() {
            return this.isAtPreviewStep
                && this.canCheckData
                && this.form.name;
        },
        canCheckData() {
            return this.fileData
                && this.preview;
        },
        readyForPreview() {
            if (!this.parseForm.file && !this.parseForm.fileId) {
                return false;
            }
            if (!this.mapping) {
                return false;
            }
            const nameField = this.mappingFields.find((field) => field.type === 'SYSTEM_NAME');
            return _.find(this.form.columnMap, { fieldId: nameField.id });
        },
        showNameWarning() {
            return this.canCheckData && !this.form.name;
        },
    },
    methods: {
        async parseFile() {
            this.loadingParsed = true;
            try {
                const response = await parseImportFile(this.parseForm);
                this.fileData = response.data;
                this.form.dateFormat = response.dateFormatGuess;
                this.form.columnMap = removeTypename(response.columnMapGuess);
                this.parseForm.fileId = response.fileId;
                this.form.fileId = response.fileId;
            } finally {
                this.loadingParsed = false;
            }
        },
        async importFile() {
            this.loadingImport = true;
            this.form.mappingId = this.parseForm.mappingId;
            this.form.file = this.parseForm.file;
            this.form.fileId = this.parseForm.fileId;
            try {
                const imp = await importFile(this.form);
                this.importId = imp.id;
            } finally {
                this.loadingImport = false;
            }
        },
        resetEverything() {
            this.form.reset();
            this.parseForm.reset();
            this.previewForm.reset();
        },
        setMappingIdWarning() {
            this.mappingIdWarning = 'Select a blueprint to proceed';
            setTimeout(() => {
                this.mappingIdWarning = null;
            }, 5000);
        },
        async fetchPreview() {
            if (!this.readyForPreview) {
                return;
            }
            this.loadingPreview = true;
            this.previewForm.file = this.parseForm.file;
            this.previewForm.fileId = this.parseForm.fileId;
            this.previewForm.columnMap = this.form.columnMap;
            this.previewForm.dateFormat = this.form.dateFormat;
            try {
                const response = await previewFile(this.previewForm, this.mapping, this.page);
                this.preview = response.data;
                this.previewErrors = response.errors.map((error) => {
                    return {
                        ...error,
                        index: _.last(error.path),
                    };
                });
                this.previewHasMore = response.pageInfo.hasMorePages;
            } finally {
                this.loadingPreview = false;
            }
        },
        nextPage() {
            if (this.previewHasMore) {
                this.page += 1;
                this.fetchPreview();
            }
        },
        previousPage() {
            if (this.page !== 1) {
                this.page -= 1;
                this.fetchPreview();
            }
        },
        startNewImport() {
            this.importId = null;
            this.import = null;
            this.resetEverything();
        },
    },
    watch: {
        'parseForm.file': function onFileChange(file) {
            // When changing the file, a host of things need to be reset.
            this.parseForm.fileId = null;
            this.form.fileId = null;
            this.previewForm.fileId = null;
            this.form.columnMap = [];
            this.previewForm.columnMap = [];
            this.preview = null;

            if (file && this.parseForm.mappingId) {
                this.parseFile();
            }
            if (!file) {
                this.fileData = null;
            }
            if (!this.parseForm.mappingId) {
                this.setMappingIdWarning();
            }
        },
        'parseForm.mappingId': function onMappingIdChange() {
            if (this.mappingIdWarning) {
                this.mappingIdWarning = null;
            }
            if (this.parseForm.file || this.parseForm.fileId) {
                this.parseFile();
            }
        },
        // Just temporary to test the request
        'form.columnMap': function onColumnMapChange() {
            this.fetchPreview();
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-import-process {

} */

</style>
