<template>
    <div class="o-general-attributes">
        <SettingsHeaderLine
            class="mb-10"
        >
            <template
                #header
            >
                Base image
            </template>

            <FormWrapper
                v-if="avatarSource || isModifiable"
                :form="imageForm"
            >
                <ImageContainer
                    v-model:editMode="editingImage"
                    displaySize="h-56 w-56"
                    formField="image"
                    :image="avatarSource"
                    hasSaveButton
                    onlyCroppedImage
                    :isDisplayOnly="!isModifiable"
                    @saveImage="saveImage"
                >
                </ImageContainer>
            </FormWrapper>

            <div
                v-else
            >
                <ProfileNameImage
                    :profile="currentBaseDisplay"
                    hideFullName
                    size="lg"
                >
                </ProfileNameImage>

                <p
                    class="text-sm mt-2 text-cm-500"
                >
                    Edit your avatar in your account settings to change your personal base's image
                </p>
            </div>

        </SettingsHeaderLine>

        <SettingsHeaderLine
            class="mb-10"
        >
            <template
                #header
            >
                Base name
            </template>

            <FormWrapper
                v-if="isModifiable"
                :form="nameForm"
                @submit="saveName"
            >
                <div
                    class="o-settings-page__box flex items-center"
                >
                    <InputLine
                        class="w-full"
                        formField="name"
                        :maxLength="maxBaseNameLength"
                        placeholder="Give it a name!"
                    >
                    </InputLine>
                    <button
                        v-if="newName"
                        v-t="'common.save'"
                        class="bg-primary-600 hover:bg-primary-500 text-cm-00 button--sm ml-4"
                        type="submit"
                    >
                    </button>
                </div>
            </FormWrapper>

            <div
                v-else
                class="text-cm-500"
            >
                {{ base.name }}
            </div>

        </SettingsHeaderLine>

        <SettingsHeaderLine
            v-if="!isPersonalBase"
            class="mb-10"
        >
            <template
                #header
            >
                Base description
            </template>

            <FormWrapper
                :form="descriptionForm"
                @submit="saveDescription"
            >
                <div
                    class="o-settings-page__box flex items-start"
                >
                    <TextareaField
                        class="w-full"
                        formField="description"
                        placeholder="Add a description for your base"
                        maxlength="1000"
                    >
                    </TextareaField>
                    <button
                        v-if="newDescription"
                        v-t="'common.save'"
                        class="bg-primary-600 hover:bg-primary-500 text-cm-00 button--sm ml-4"
                        type="submit"
                    >
                    </button>
                </div>
            </FormWrapper>

        </SettingsHeaderLine>

        <div
            class="text-sm text-cm-500"
        >
            <p
                class="mb-2"
            >
                <span
                    class="font-semibold"
                >
                    Base created on:
                </span>
                {{ createdAtFormatted }}
            </p>
            <div class="flex items-start">
                <BirdImage
                    class="h-12"
                    whichBird="FlyingUpBird_72dpi.png"
                >
                </BirdImage>
                <p
                    class="ml-3"
                >
                    "{{ baseName }}" has used Hylark for {{ baseDuration }}!
                </p>

            </div>
        </div>
    </div>
</template>

<script>

import ImageContainer from '@/components/images/ImageContainer.vue';
import ProfileNameImage from '@/components/profile/ProfileNameImage.vue';

import {
    updateName,
    updateImage,
    updateDescription,
} from '@/core/repositories/baseRepository.js';

import { maxBaseNameLength } from '@/core/data/bases.js';

export default {
    name: 'GeneralAttributes',
    components: {
        ImageContainer,
        ProfileNameImage,
    },
    mixins: [
    ],
    props: {
        base: {
            type: Object,
            required: true,
        },
        isModifiable: Boolean,
        user: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            nameForm: this.$apolloForm({
                name: this.base.name,
            }, { client: 'defaultClient' }),
            descriptionForm: this.$apolloForm({
                description: this.base.description || '',
            }, { client: 'defaultClient' }),
            imageForm: this.$apolloForm({
                image: this.base.image,
            }, { client: 'defaultClient' }),
            editingImage: false,
        };
    },
    computed: {
        avatarSource() {
            return this.base.image;
        },
        newName() {
            return this.base.name !== this.nameForm.name.trim();
        },
        newDescription() {
            return (this.base.description || '') !== this.descriptionForm.description.trim();
        },
        isPersonalBase() {
            return this.base.baseType === 'PERSONAL';
        },
        currentBaseDisplay() {
            if (this.isPersonalBase) {
                return this.user;
            }
            return this.base;
        },
        baseName() {
            return this.base.name;
        },
        createdAt() {
            return this.base.createdAt;
        },
        baseDuration() {
            return this.$dayjs().to(this.createdAt, true);
        },
        createdAtFormatted() {
            return this.$dayjs(this.createdAt).format('lll');
        },
    },
    methods: {
        async saveName() {
            await updateName(this.nameForm);
            this.$saveFeedback();
        },
        async saveDescription() {
            await updateDescription(this.descriptionForm);
            this.$saveFeedback();
        },
        async saveImage() {
            await updateImage(this.imageForm);
            this.imageForm.image = this.base.image;
            this.editingImage = false;
            this.$saveFeedback();
        },
    },
    created() {
        this.maxBaseNameLength = maxBaseNameLength;
    },
};
</script>

<style scoped>

/*.o-general-attributes {

} */

</style>
