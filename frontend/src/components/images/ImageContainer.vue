<!-- For Hylark system image uploads -->

<template>
    <div class="c-image-container">
        <ImageUploadTemplate
            v-if="!image || editMode"
            :class="displaySize"
            boxStyle="stripes"
            :shape="shape"
            v-bind="$attrs"
            @update:modelValue="$emit('update:modelValue', $event)"
            @saveImage="$emit('saveImage', $event)"
        >
        </ImageUploadTemplate>

        <div
            v-else
            class="flex"
        >
            <div
                :class="displaySize"
            >
                <ImageHolder
                    class="rounded-xl"
                    :image="image"
                >
                </ImageHolder>
            </div>

            <div
                v-if="!isDisplayOnly"
                class="ml-3"
            >
                <button
                    class="c-image-container__button
                        circle-center
                        bg-cm-100
                        mb-1
                        hover:bg-cm-200
                        text-primary-600"
                    type="button"
                    title="Remove image"
                    @click="removeFile"
                >
                    <i class="fal fa-times"></i>
                </button>
                <button
                    class="c-image-container__button
                        circle-center
                        bg-cm-100
                        mb-1
                        hover:bg-cm-200
                        text-primary-600"
                    type="button"
                    title="Edit"
                    @click="edit"
                >
                    <i class="fal fa-pencil-alt"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>

import ImageHolder from './ImageHolder.vue';
import ImageUploadTemplate from '@/components/images/ImageUploadTemplate.vue';
import formWrapperChild from '@/vue-mixins/formWrapperChild.js';

export default {
    name: 'ImageContainer',
    components: {
        ImageUploadTemplate,
        ImageHolder,
    },
    mixins: [
        formWrapperChild,
    ],
    props: {
        image: {
            type: String,
            default: '',
        },
        displaySize: {
            type: String,
            required: true,
        },
        editMode: Boolean,
        isDisplayOnly: Boolean,
        shape: {
            type: [String, null],
            default: 'square',
        },
    },
    emits: [
        'update:modelValue',
        'update:editMode',
        'saveImage',
    ],
    data() {
        return {
        };
    },
    computed: {

    },
    methods: {
        removeFile() {
            this.emitInput(null);
            this.$emit('saveImage');
            this.$emit('update:editMode', false);
        },
        edit() {
            this.$emit('update:editMode', true);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-image-container {
    &__button {
        transition: 0.2s ease-in-out;

        @apply
            h-8
            w-8
        ;
    }
}

</style>
