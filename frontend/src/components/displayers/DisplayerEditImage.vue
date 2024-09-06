<template>
    <div class="c-displayer-edit-image flex justify-center">
        <ImageUploadTemplate
            :modelValue="modifiableFieldValue"
            :formField="formField"
            :presetQuery="presetQuery"
            @update:modelValue="updateDataValue"
        >
        </ImageUploadTemplate>
    </div>
</template>

<script>

import ImageUploadTemplate from '@/components/images/ImageUploadTemplate.vue';

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';

export default {
    name: 'DisplayerEditImage',
    components: {
        ImageUploadTemplate,
    },
    mixins: [
        interactsWithDisplayersEdit,
    ],
    props: {
        formField: {
            type: String,
            default: '',
        },
        fullForm: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'update:dataValue',
    ],
    data() {
        return {

        };
    },
    computed: {
        presetQuery() {
            return this.item?.name || this.fullForm?.SYSTEM_NAME?.fieldValue || '';
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-edit-image {

} */

</style>
