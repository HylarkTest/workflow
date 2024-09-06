<template>
    <div class="c-document-upload relative flex">
        <FileUploadTemplate
            :error="errorMessage"
            :url="url"
            :isProcessingFile="isProcessingFile"
            :acceptedFileTypes="acceptedFileTypes"
            @addFile="addFile"
        >
            <template #withUrl>
                <div
                    class="border border-primary-200 border-solid rounded-lg bg-primary-50 px-4 py-2 text-sm"
                >
                    {{ filename }}
                </div>
            </template>

            <template #withoutUrl="{ browseFilesInput }">
                <div class="flex items-center">
                    <span
                        class="text-lg text-primary-600 fal fa-paperclip mr-2"
                    >
                    </span>

                    <p
                        class="c-file-upload-template__prompt"
                    >
                        {{ $t('features.upload.documentPrompt') }}
                    </p>

                    <p
                        class="uppercase text-sm text-cm-400 mx-4"
                    >
                        {{ $t('features.upload.connector') }}
                    </p>

                    <label for="browseFiles">
                        <button
                            class="button--sm bg-primary-600 text-cm-00 hover:bg-primary-500"
                            type="button"
                            @click.stop="browseFilesInput.click(null)"
                        >
                            {{ $t('features.upload.browseFiles') }}
                        </button>
                    </label>
                </div>
            </template>

            <template #extras="{ browseFilesInput }">
                <div
                    v-if="isNew"
                    class="c-document-upload__extras"
                >
                    <button
                        class="c-file-upload-template__button circle-center bg-cm-100 mr-1"
                        type="button"
                        :title="$t('common.clear')"
                        @click="browseFilesInput.click(null)"
                    >
                        <i class="fal fa-pencil-alt"></i>
                    </button>
                    <button
                        class="c-file-upload-template__button circle-center bg-cm-100"
                        type="button"
                        :title="$t('common.clear')"
                        @click="removeFile"
                    >
                        <i class="fal fa-times"></i>
                    </button>
                </div>

                <!-- A button like this is unusual for a form,
                    but there are currently no options for editing an existing document,
                    so the form may look confusing to users. This may be reviewed once the form is fleshed out. -->
                <div
                    v-else
                    class="c-document-upload__extras"
                >
                    <DownloadButton
                        :url="downloadUrl"
                    >
                        <IconHover
                            class="c-icon-hover"
                            icon="far fa-download"
                            iconColor="text-primary-600"
                        >
                        </IconHover>
                    </DownloadButton>
                </div>
            </template>
        </FileUploadTemplate>
    </div>
</template>

<script>
import FileUploadTemplate from '@/components/buttons/FileUploadTemplate.vue';
import DownloadButton from '@/components/buttons/DownloadButton.vue';
import IconHover from '@/components/buttons/IconHover.vue';

import formWrapperChild from '@/vue-mixins/formWrapperChild.js';
import interactsWithFileUploadWrapper from '@/vue-mixins/interactsWithFileUploadWrapper.js';

export default {
    name: 'DocumentUpload',
    components: {
        FileUploadTemplate,
        DownloadButton,
        IconHover,
    },
    mixins: [
        formWrapperChild,
        interactsWithFileUploadWrapper,
    ],
    props: {
        icon: {
            type: String,
            default: 'fal fa-paperclip',
        },
        isNew: Boolean,
        acceptedFileTypes: {
            type: String,
            default: '',
        },
        downloadUrl: {
            type: [String, null],
            default: null,
        },
    },
    emits: [
        'update:modelValue',
    ],
    data() {
        return {
            isProcessingFile: false,
        };
    },
    computed: {
    },
    methods: {
        async addFile(file) {
            this.isProcessingFile = true;

            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onloadend = async () => {
                if (!reader.result) {
                    this.$errorFeedback({
                        customHeaderPath: 'feedback.responses.invalidAttachment.header',
                        customMessagePath: 'feedback.responses.invalidAttachment.message',
                    });
                    this.isProcessingFile = false;
                    return;
                }
                this.file = file;
                this.url = reader.result;
                this.filename = this.file.name;
                this.mimeType = this.file.type;
                this.emitInput(this.file);
                this.isProcessingFile = false;
            };
        },
        removeFile() {
            this.file = null;
            this.emitInput(null);
        },
    },
    watch: {
        formValue: {
            immediate: true,
            async handler(value) {
                if (!this.file && value) {
                    this.addFile(value);
                }
            },
        },
    },
};
</script>

<style scoped>
.c-document-upload {
    &__extras {
        @apply
            flex
            h-full
            items-center
            ml-2
        ;
    }
}
</style>
