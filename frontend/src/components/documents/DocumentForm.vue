<template>
    <FeatureFormBase
        v-model:form="form"
        v-model:formAssociations="form.associations"
        v-model:formAssigneeGroups="form.assigneeGroups"
        v-model:formMarkers="form.markers"
        v-model:formListId="form.driveId"
        class="o-document-form"
        v-bind="baseProps"
        :changeListFunction="changeDrive"
        @saveItem="saveItem(true)"
        @deleteItem="deleteItem"
    >
        <div class="mb-4">
            <label class="header-form">
                {{ $t('features.upload.document') }}*
            </label>

            <DocumentUpload
                class="mt-1"
                :formUrl="url"
                :downloadUrl="downloadUrl"
                :formFilename="filename"
                :isNew="isNew"
                formField="file"
            >
            </DocumentUpload>
        </div>
    </FeatureFormBase>
</template>

<script>
import DocumentUpload from '@/components/assets/DocumentUpload.vue';

import interactsWithFeatureForms from '@/vue-mixins/features/interactsWithFeatureForms.js';

import {
    changeDrive,
    createDocumentFromObject,
    updateDocument,
    createDocument,
    deleteDocument,
} from '@/core/repositories/documentRepository.js';

import DOCUMENT from '@/graphql/documents/queries/Document.gql';

export default {
    name: 'DocumentForm',
    components: {
        DocumentUpload,
    },
    mixins: [
        interactsWithFeatureForms,
    ],
    props: {
        file: {
            type: [Object, null],
            default: null,
        },
        document: {
            type: [Object, null],
            default: null,
        },
        drive: {
            type: [Object, null],
            default: null,
        },
    },
    apollo: {
        fullDocument: {
            query: DOCUMENT,
            variables() {
                return { id: this.document.id };
            },
            skip() {
                return !this.document?.id;
            },
            update: ({ document }) => createDocumentFromObject(document),
        },
    },
    data() {
        return {
            listKey: 'driveId',
            listObjKey: 'drive',
            featureType: 'DOCUMENTS',
            form: this.$apolloForm(() => {
                const data = {
                    filename: this.document?.filename || '',
                };
                if (this.isNew) {
                    data.file = this.file || null;
                    data.assigneeGroups = [];
                    data.associations = this.defaultAssociations || [];
                    data.markers = [];
                    data.driveId = this.drive?.id || this.firstList?.id;
                } else {
                    data.id = this.document.id;
                    data.file = null;
                }

                return data;
            }),
        };
    },
    computed: {
        savedItem() {
            return this.fullDocument;
        },
        hiddenSections() {
            return ['NAME', 'DESCRIPTION'];
        },
        downloadUrl() {
            // only accessible if the document exists i.e. is not new
            return this.document?.downloadUrl;
        },
        url() {
            return this.form.file?.url || this.document?.url;
        },
        filename() {
            return this.form.file?.filename || this.document?.filename;
        },
    },
    methods: {

    },
    created() {
        this.changeDrive = changeDrive;
        this.createFunction = createDocument;
        this.updateFunction = updateDocument;
        this.deleteFunction = deleteDocument;
    },
};
</script>

<style scoped>

/*.o-document-form {

} */

</style>
