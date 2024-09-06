<template>
    <div
        class="c-file-upload-template relative"
    >
        <div
            v-if="isProcessingFile"
            class="absolute h-full w-full bg-cm-00 opacity-50 z-over"
        >

        </div>
        <div
            v-if="isProcessingFile"
            class="absolute centered h-full w-full z-over"
        >
            <LoaderFetch
                :sphereSize="20"
            >
            </LoaderFetch>
        </div>

        <ButtonEl
            class="c-file-upload-template__box relative center"
            :class="[boxClass, { unclickable: isProcessingFile }]"
            @dragenter="onDragEnter"
            @dragover="onDragEnter"
            @dragleave="onDragLeave"
            @drop="onDrop"
            @click.stop="handleButton"
        >
            <transition name="t-fade">
                <AlertTooltip
                    v-if="errorMessage"
                    :alertPosition="{ top: '-20px', right: '-20px' }"
                >
                    {{ errorMessage }}
                </AlertTooltip>
            </transition>

            <input
                ref="input"
                id="browseFiles"
                class="hidden"
                type="file"
                :accept="acceptedFileTypes"
                @change="onChange"
            >

            <slot
                v-if="url"
                name="withUrl"
            >
            </slot>

            <!-- isMounted is required to allow the slot to access $refs.input -->
            <div
                v-if="!url && isMounted"
                class="p-4"
            >
                <slot
                    name="withoutUrl"
                    :browseFilesInput="$refs.input"
                >
                </slot>
            </div>
        </ButtonEl>

        <div
            :class="{ unclickable: isProcessingFile }"
        >
            <slot
                v-if="url && isMounted"
                name="extras"
                :browseFilesInput="$refs.input"
            >
            </slot>
        </div>
    </div>
</template>

<script>
import AlertTooltip from '@/components/popups/AlertTooltip.vue';

import formWrapperChild from '@/vue-mixins/formWrapperChild.js';
import interactsWithFileDrop from '@/vue-mixins/interactsWithFileDrop.js';

export default {
    name: 'FileUploadTemplate',
    components: {
        AlertTooltip,
    },
    mixins: [
        formWrapperChild,
        interactsWithFileDrop,
    ],
    props: {
        boxHeightClass: {
            type: String,
            default: 'h-auto',
        },
        boxStyle: {
            type: String,
            default: 'solid',
            validator(value) {
                return ['solid', 'stripes'].includes(value);
            },
        },
        boxWidthClass: {
            type: String,
            default: 'w-auto',
        },
        url: {
            type: [String, Object, null],
            default: null,
        },
        isProcessingFile: Boolean,
        acceptedFileTypes: {
            type: String,
            default: '',
        },
        fileTypeKey: {
            type: String,
            default: '',
            validator(value) {
                return ['IMAGE', ''].includes(value);
            },
        },
    },
    emits: [
        'addFile',
    ],
    data() {
        return {
            isMounted: false,
        };
    },
    computed: {
        boxClass() {
            return [
                this.boxWidthClass,
                this.boxHeightClass,
                { 'c-file-upload-template__empty': !this.url },
                { 'c-file-upload-template__empty--stripes': !this.url && this.boxStyle === 'stripes' },
                this.hovering ? 'border-primary-500' : 'border-cm-300',
                this.url ? 'cursor-default' : 'cursor-pointer',
            ];
        },
    },
    methods: {
        async onChange(event) {
            const file = event.target.files[0] ?? null;
            if (file) {
                this.addFile(file);
            }
        },
        addFile(file) {
            this.$emit('addFile', file);
        },
        handleButton() {
            if (!this.url) {
                this.$refs.input.click(null);
            }
        },
    },
    watch: {
        url(value) {
            if (!value) {
                this.$refs.input.value = null;
            }
        },
    },
    mounted() {
        this.isMounted = true;
    },
};
</script>

<style scoped>
.c-file-upload-template {
    @apply
        flex
    ;

    &__box {
        @apply
            rounded-xl
        ;
    }

    &__empty {
        @apply
            bg-cm-100
            border-2
            border-dashed
            flex-col
            text-center
        ;

        &--stripes {
            background: repeating-linear-gradient(
                45deg,
                #fafafa,
                #fafafa 4%,
                #fff 4%,
                #fff 8%
            );
        }
    }

    /* Because the html is passed through a slot, deep selector is required */
    /* Perhaps these classes could be in each wrapper file separately, but for now
       leaving as is */

    &:deep(.c-file-upload-template__prompt) {
        @apply
            font-semibold
            leading-snug
            text-cm-700
            text-xssm
        ;
    }

    &:deep(.c-file-upload-template__button) {
        transition: 0.2s ease-in-out;

        @apply
            h-8
            hover:bg-cm-200
            text-primary-600
            w-8
        ;
    }

    &:deep(.c-file-upload-template__accent) {
        @apply
            bg-primary-600
            hover:bg-primary-500
            text-cm-00
        ;
    }
}

.darkmode .c-file-upload-template__empty--stripes {
    background: repeating-linear-gradient(
        45deg,
        #363636,
        #363636 4%,
        #000 4%,
        #000 8%
    );
}
</style>
