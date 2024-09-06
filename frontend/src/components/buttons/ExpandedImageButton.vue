<template>
    <div
        class="c-expanded-image-button centered relative"
        :class="sizeClass"
    >
        <button
            type="button"
            class="circle-center"
            :title="$t('common.fullsize')"
            @click.stop="openModal"
        >
            <i class="fal fa-arrows-maximize z-over text-cm-500 pointer-events-none">
            </i>
            <span
                class="h-full w-full rounded-full opacity-70 bg-cm-00 absolute hover:opacity-100 transition-2eio"
            >
            </span>
        </button>
        <ImageModal
            v-if="isModalOpen"
            :header="header"
            :image="image"
            @closeModal="closeModal"
        >
        </ImageModal>
    </div>
</template>

<script>
import ImageModal from '@/components/images/ImageModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    name: 'ExpandedImageButton',
    components: {
        ImageModal,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        header: {
            type: [String, null],
            default: null,
        },
        image: {
            type: String,
            required: true,
        },
        size: {
            type: String,
            default: 'base',
            validator(val) {
                return ['base', 'sm'].includes(val);
            },
        },
    },
    computed: {
        sizeClass() {
            return `c-expanded-image-button__size--${this.size}`;
        },
    },
};
</script>

<style>
.c-expanded-image-button {
    &__size {
        &--base {
            height: 20px;
            width: 20px;

            @apply
                text-xs
            ;
        }

        &--sm {
            height: 15px;
            width: 15px;

            @apply
                text-xxs
            ;
        }
    }
}
</style>
