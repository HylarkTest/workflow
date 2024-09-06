<template>
    <FeatureFormBase
        v-model:form="form"
        v-model:formAssociations="form.associations"
        v-model:formAssigneeGroups="form.assigneeGroups"
        v-model:formMarkers="form.markers"
        v-model:formListId="form.pinboardId"
        class="o-pin-form"
        v-bind="baseProps"
        :changeListFunction="changePinboard"
        @saveItem="saveItem(true)"
        @deleteItem="deleteItem"
    >
        <div class="mb-4">
            <label class="header-form">
                {{ $t('features.upload.image') }}*
            </label>

            <ImageUploadTemplate
                formField="image"
            >
            </ImageUploadTemplate>
        </div>
    </FeatureFormBase>
</template>

<script>

import ImageUploadTemplate from '@/components/images/ImageUploadTemplate.vue';

import interactsWithFeatureForms from '@/vue-mixins/features/interactsWithFeatureForms.js';

import {
    changePinboard,
    createPinFromObject,
    updatePin,
    createPin,
    deletePin,
} from '@/core/repositories/pinRepository.js';

import PIN from '@/graphql/pinboard/queries/Pin.gql';

export default {
    name: 'PinForm',
    components: {
        ImageUploadTemplate,
    },
    mixins: [
        interactsWithFeatureForms,
    ],
    props: {
        file: {
            type: [Object, null],
            default: null,
        },
        pinboard: {
            type: [Object, null],
            default: null,
        },
        pin: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'closeModal',
    ],
    apollo: {
        fullPin: {
            query: PIN,
            variables() {
                return { id: this.pin.id };
            },
            skip() {
                return !this.pin?.id;
            },
            update: ({ pin }) => createPinFromObject(pin),
        },
    },
    data() {
        return {
            listKey: 'pinboardId',
            listObjKey: 'pinboard',
            featureType: 'PINBOARD',
            form: this.$apolloForm(() => {
                const data = {
                    description: this.pin?.description || '',
                    name: this.pin?.name || '',
                };
                if (this.isNew) {
                    data.image = this.file || null;
                    data.assigneeGroups = [];
                    data.associations = this.defaultAssociations || [];
                    data.markers = [];
                    data.pinboardId = this.pinboard?.id;
                } else {
                    data.id = this.pin.id;
                    data.image = this.pin.image || null;
                }

                return data;
            }),
        };
    },
    computed: {
        savedItem() {
            return this.fullPin;
        },
        url() {
            return this.form.image?.url || this.pin?.url;
        },
        filename() {
            return this.form.image?.filename || this.pin?.filename;
        },
    },
    methods: {
    },
    created() {
        this.changePinboard = changePinboard;
        this.createFunction = createPin;
        this.updateFunction = updatePin;
        this.deleteFunction = deletePin;
    },
};
</script>

<style scoped>

/*.o-pin-form {

} */

</style>
