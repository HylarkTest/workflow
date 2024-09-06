<template>
    <div class="c-image-upload-template relative">
        <FileUploadTemplate
            :error="errorString"
            :url="url"
            :isProcessingFile="isProcessingFile"
            boxHeightClass="h-56"
            boxWidthClass="w-64"
            :acceptedFileTypes="acceptedFileTypes"
            fileTypeKey="IMAGE"
            :boxStyle="boxStyle"
            @addFile="addFile"
        >
            <template #withUrl>
                <div
                    tabindex="0"
                    class="w-full h-full"
                    @keydown="manipulateCropper"
                    @click.stop
                >
                    <img
                        ref="image"
                        class="w-full max-h-full"
                        crossorigin="anonymous"
                        :src="url"
                    >
                </div>
            </template>

            <template #withoutUrl="{ browseFilesInput }">
                <span class="text-2xl text-primary-600 fal fa-image mb-3">
                </span>

                <p class="c-file-upload-template__prompt mb-2">
                    {{ $t('features.upload.imagePrompt') }}
                </p>

                <div class="flex mb-2 items-center">
                    <div class="c-image-upload-template__divider"></div>
                    <p class="uppercase text-sm text-cm-400">
                        {{ $t('features.upload.connector') }}
                    </p>
                    <div class="c-image-upload-template__divider"></div>
                </div>

                <label for="browseFiles">
                    <button
                        class="button--sm button-primary"
                        type="button"
                        @click.stop="browseFilesInput.click(null)"
                    >
                        {{ $t('features.upload.browseImages') }}
                    </button>
                </label>

                <button
                    class="c-file-upload-template__button circle-center bg-cm-00
                        absolute
                        shadow-md
                        top-2
                        right-2"
                    type="button"
                    title="Google image search"
                    @click.stop="openSearch"
                >
                    <i class="fa-light fa-magnifying-glass"></i>
                </button>
            </template>

            <template #extras>
                <div class="flex ml-2 flex-col">
                    <button
                        v-for="extra in extras"
                        :key="extra.icon"
                        class="c-file-upload-template__button mb-1 circle-center bg-cm-100"
                        :class="extra.customClass"
                        :title="extra.title"
                        type="button"
                        @click="extra.action"
                    >
                        <i :class="extra.icon"></i>
                    </button>
                </div>
            </template>
        </FileUploadTemplate>

        <ImageSearchModal
            v-if="isModalOpen"
            :presetQuery="presetQuery"
            emitDownloadedImage
            @closeModal="closeModal"
            @selectedImage="setSelectedImage"
        >
        </ImageSearchModal>
    </div>
</template>

<script>
import 'cropperjs/dist/cropper.css';

import ImageSearchModal from '@/components/images/ImageSearchModal.vue';
import FileUploadTemplate from '@/components/buttons/FileUploadTemplate.vue';

import formWrapperChild from '@/vue-mixins/formWrapperChild.js';
import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import {
    checkIsFileTypeValid,
    validFileTypes,
} from '@/core/validation.js';
import {
    compressFile,
    convertHeicFile,
    fetchFileFromUrl, getDataUrlFromFile,
} from '@/core/fileHelpers.js';

const imageRatios = {
    circle: 1,
    square: 1,
    hRectangle: 8 / 5,
    vRectangle: 6 / 8,
};

const MAX_SIZE = 2 * 1024 * 1024;

/*
 * The `formValue` prop can be:
 * - A File object
 * - A URL
 * - An object with the following properties:
 *   - originalUrl: A URL
 *   - width: The width of the crop window
 *   - height: The height of the crop window
 *   - yOffset: The y offset of the crop window
 *   - xOffset: The x offset of the crop window
 * - A document object:
 *   - id: The ID of the document
 *   - filename: The name of the document
 *   - url: The URL of the document
 *   - mimeType: The MIME type of the document
 */
export default {
    name: 'ImageUploadTemplate',
    components: {
        ImageSearchModal,
        FileUploadTemplate,
    },
    mixins: [
        formWrapperChild,
        interactsWithModal,
    ],
    props: {
        // Constrain the aspect ratio of the image
        // Allowed options: 'circle', 'square', 'hRectangle', 'vRectangle'
        shape: {
            type: [String, null],
            default: null,
            validate(value) {
                return _.isNull(value) || _.keys(imageRatios).includes(value);
            },
        },
        // Include a save button which emits `saveImage` when clicked
        hasSaveButton: Boolean,
        // If true this component will emit the cropped image in the `update:modelValue` event.
        // The default behaviour is to emit the original image with the crop info.
        onlyCroppedImage: Boolean,
        // A string that will be added to the Google image search modal.
        presetQuery: {
            type: String,
            default: '',
        },
        // An error message to display over the cropper element.
        error: {
            type: String,
            default: '',
        },
        // Override the default accepted file types match which is `image/*`.
        acceptedFileTypes: {
            type: String,
            default: validFileTypes.IMAGE.acceptedTypes,
        },
        // The style of the drag box.
        boxStyle: {
            type: String,
            default: 'solid',
            validator(value) {
                return ['solid', 'stripes'].includes(value);
            },
        },
    },
    emits: [
        'update:modelValue',
        'saveImage',
    ],
    data() {
        return {
            cropper: null,
            isProcessingCropper: false,
            componentError: '',
            // The file is used to get the name and file type. This is either
            // taken directly from `formValue` or downloaded using the URL.
            file: null,
            // The URL is needed to add to the `img` tag, so it is either the
            // provided URL or the base64 data URL of the file.
            url: null,
            // We watch the formValue to see if a new uploaded image is passed.
            // So we need to store the emitted payload in order to check that
            // the new formValue is not the same as the emitted payload.
            emittedPayload: null,
        };
    },
    computed: {
        isProcessingFile() {
            return this.isProcessingCropper;
        },
        errorString() {
            return this.error || this.componentError || this.errorMessage;
        },
        extras() {
            return [
                {
                    action: this.removeFile,
                    icon: 'fal fa-times',
                    title: 'Clear',
                },
                {
                    action: this.rotate,
                    icon: 'fal fa-rotate-right',
                    title: 'Rotate 90 degrees clockwise',
                },
                {
                    action: this.openSearch,
                    icon: 'fa-light fa-magnifying-glass',
                    title: 'Google image search',
                },
                {
                    action: this.save,
                    condition: this.hasSaveButton,
                    customClass: 'c-file-upload-template__accent',
                    icon: 'fal fa-check',
                    title: this.$t('common.save'),
                },
            ].filter((extra) => !_.has(extra, 'condition') || extra.condition);
        },
        isDataUrl() {
            return this.url.match(/^data:image\/.*;base64/);
        },
    },
    methods: {
        async convertHeicFile(file) {
            try {
                const convertedFile = convertHeicFile(file);
                if (!convertedFile) {
                    throw new Error('Failed to convert HEIC file');
                }
                return convertedFile;
            } catch (error) {
                this.corruptedImageError();
                // For now throwing error always, to assess the frequency of the above
                // or if it is related to other problems.
                throw error;
            }
        },
        async addFile(file) {
            if (!checkIsFileTypeValid(file, 'IMAGE')) {
                this.invalidImageError();
                return;
            }

            this.isProcessingCropper = true;

            const extractedFile = await this.convertHeicFile(file);

            this.file = this.onlyCroppedImage ? extractedFile : await compressFile(extractedFile, MAX_SIZE);
            this.url = await getDataUrlFromFile(this.file);

            await this.initializeCropper();

            this.isProcessingCropper = false;
        },
        async initializeCropper() {
            if (this.cropper) {
                this.destroyCropper();
            }
            const { default: Cropper } = await import('cropperjs');

            const imageEl = this.$refs.image;
            if (!imageEl) {
                // The formValue watcher could get called after the component is
                // unmounted, so we need to check if the image element is still
                // available.
                return;
            }

            let data = null;
            if (_.isPlainObject(this.formValue)) {
                data = {
                    width: this.formValue.width,
                    height: this.formValue.height,
                    x: this.formValue.xOffset,
                    y: this.formValue.yOffset,
                };
            }

            this.cropper = new Cropper(imageEl, {
                data,
                viewMode: 1,
                dragMode: 'move',
                aspectRatio: this.shape && imageRatios[this.shape],
                responsive: false,
                autoCropArea: 0.9,
                checkCrossOrigin: true,
                toggleDragModeOnDblclick: false,
                ready: () => {
                    if (this.shape === 'circle') {
                        this.cropper.cropBox.classList.add('cropper-circle');
                    }
                    const originalCanvasData = this.cropper.getCanvasData();
                    const newWidth = originalCanvasData.width * 0.9;
                    const newHeight = originalCanvasData.height * 0.9;
                    const newTop = originalCanvasData.top + ((originalCanvasData.height - newHeight) / 2);
                    const newLeft = originalCanvasData.left + ((originalCanvasData.width - newWidth) / 2);
                    this.cropper.setCanvasData({
                        width: newWidth,
                        height: newHeight,
                    });
                    this.cropper.setCanvasData({
                        top: newTop,
                        left: newLeft,
                    });
                    this.emitCropChange();
                },
                cropend: this.emitCropChange,
                zoom: this.emitCropChange,
            });
        },
        getCroppedImage() {
            return new Promise((resolve) => {
                this.cropper.getCroppedCanvas().toBlob((croppedImage) => {
                    const file = new File([croppedImage], this.file.name || 'tmp', { type: this.file.type });
                    const compressedFile = compressFile(file, MAX_SIZE);
                    compressedFile.cropped = true;
                    resolve(compressedFile);
                }, this.file.type);
            });
        },
        async emitCropChange() {
            let payload;
            if (this.onlyCroppedImage) {
                payload = await this.getCroppedImage();
            } else {
                const data = this.cropper.getData(true);
                payload = {
                    image: this.isDataUrl ? this.file : null,
                    url: !this.isDataUrl ? this.url : null,
                    width: data.width,
                    height: data.height,
                    yOffset: data.y,
                    xOffset: data.x,
                    rotate: data.rotate,
                };
            }

            this.emittedPayload = payload;
            this.emitInput(payload);
        },
        removeFile() {
            this.file = null;
            this.url = null;
            this.emitInput(null);
            this.destroyCropper();
        },
        rotate() {
            if (this.cropper) {
                this.cropper.rotate(90);
                this.emitCropChange();
            }
        },
        save() {
            this.$emit('saveImage');
        },
        manipulateCropper(event) {
            const { key, altKey, shiftKey } = event;
            if (!this.cropper || !['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(key)) {
                return;
            }
            event.stopPropagation();
            event.preventDefault();
            // Manipulations allowed:
            // - Arrow keys: Move the crop box
            // - Alt + Up/Down zooms in/out
            // - Shift + Arrow keys: Resize the crop box
            // - Shift + Alt + Up/Down: Resize the crop box proportionally
            if (!shiftKey && !altKey) {
                this.cropper.move(...{
                    ArrowUp: [0, -10],
                    ArrowDown: [0, 10],
                    ArrowLeft: [-10, 0],
                    ArrowRight: [10, 0],
                }[key]);
            } else if (shiftKey && !altKey) {
                const data = this.cropper.getCropBoxData();
                data.top += {
                    ArrowUp: -10,
                    ArrowDown: 10,
                }[key];
                data.left += {
                    ArrowLeft: -10,
                    ArrowRight: 10,
                }[key];
                this.cropper.setCropBoxData(data);
            } else if (shiftKey && altKey) {
                const data = this.cropper.getCropBoxData();
                const scale = {
                    ArrowUp: 1.1,
                    ArrowDown: 0.9,
                }[key];
                data.width *= scale;
                data.height *= scale;
                this.cropper.setCropBoxData(data);
            } else if (!shiftKey && altKey) {
                this.cropper.zoom({
                    ArrowUp: 0.1,
                    ArrowDown: -0.1,
                }[key]);
            }

            this.emitCropChange();
        },
        openSearch() {
            this.openModal();
        },
        setSelectedImage(image) {
            this.addFile(image);
        },
        corruptedImageError() {
            this.$errorFeedback({
                customHeaderPath: 'feedback.responses.invalidImageFormat.header',
                customMessagePath: 'feedback.responses.invalidImageFormat.message',
            }, false);
        },
        invalidImageError() {
            setTimeout(() => {
                this.componentError = 'Please add a valid image file';
            }, 3000);
        },
        destroyCropper() {
            this.cropper.destroy();
            this.cropper = null;
        },
    },
    watch: {
        formValue: {
            immediate: true,
            async handler(value) {
                if (!value || _.isEqual(value, this.emittedPayload)) {
                    return;
                }
                if (value instanceof File) {
                    await this.addFile(value);
                    return;
                }
                if (_.isString(value) || value?.originalUrl || value?.url) {
                    this.url = value.originalUrl || value?.url || value;
                    this.file = await fetchFileFromUrl(this.url, value?.filename);
                    await this.initializeCropper();
                }
            },
        },
    },
    created() {

    },
    mounted() {

    },
    beforeUnmount() {
        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }
    },
};
</script>

<style scoped>
.c-image-upload-template {
    &__divider {
        height:  1px;

        @apply
            bg-cm-300
            flex-1
            mx-3
        ;
    }
}
</style>
