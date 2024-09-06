<template>
    <div class="o-account-general">
        <SettingsHeaderLine class="o-account-general__line">
            <template #header>
                {{ $t('settings.general.avatar') }}
            </template>

            <FormWrapper :form="imageForm">
                <ImageContainer
                    v-model:editMode="editingImage"
                    displaySize="h-56 w-56"
                    formField="avatar"
                    :image="user.avatar"
                    hasSaveButton
                    onlyCroppedImage
                    @saveImage="saveImage"
                >
                </ImageContainer>
            </FormWrapper>

        </SettingsHeaderLine>

        <SettingsHeaderLine class="o-account-general__line">
            <template #header>
                {{ $t('labels.name') }}
            </template>

            <FormWrapper
                :form="nameForm"
                @submit="saveName"
            >
                <div class="o-settings-page__box flex items-center">
                    <InputLine
                        class="w-full"
                        formField="name"
                        placeholder="Your name"
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

        </SettingsHeaderLine>

        <SettingsHeaderLine class="o-account-general__line">
            <template #header>
                {{ $t('labels.email') }}
            </template>

            <template #description>
                <p
                    v-md-text="currentEmailText"
                    class="mb-2"
                >
                </p>

                <div class="bg-gold-100 rounded-lg p-4">
                    <p
                        v-t="'settings.general.email.header'"
                        class="o-account-general__header"
                    >
                    </p>
                    <p
                        v-t="'settings.general.email.description'"
                        class="o-account-general__description"
                    >
                    </p>
                </div>
            </template>

            <ButtonEl
                v-t="'settings.general.email.changeEmail'"
                class="bg-primary-600 hover:bg-primary-500 text-cm-00 button w-fit"
                @click="openFullDialog"
            >
            </ButtonEl>

        </SettingsHeaderLine>

        <div class="text-sm text-cm-500 mt-12 bg-primary-100 rounded-xl p-4">
            <p class="mb-2">
                <span class="font-semibold">
                    {{ $t('settings.general.accountCreated') }}:
                </span>
                {{ createdAtFormatted }}
            </p>
            <div class="flex items-start">
                <BirdImage
                    class="h-12"
                    whichBird="ThumbsUpBird_72dpi.png"
                >
                </BirdImage>
                <p class="ml-3">
                    {{ $t('settings.general.hylarkDuration') }} {{ accountDuration }}!
                </p>

            </div>
        </div>

        <ChangeEmailDialog
            v-if="isDialogOpen"
            @closeFullDialog="closeFullDialog"
        >
        </ChangeEmailDialog>
    </div>
</template>

<script>

import ImageContainer from '@/components/images/ImageContainer.vue';
import ChangeEmailDialog from '@/components/settings/ChangeEmailDialog.vue';

import interactsWithFullDialog from '@/vue-mixins/interactsWithFullDialog.js';

import {
    updateAvatar,
    updateFullName,
} from '@/core/repositories/userRepository.js';

export default {
    name: 'AccountGeneral',
    components: {
        ImageContainer,
        ChangeEmailDialog,
    },
    mixins: [
        interactsWithFullDialog,
    ],
    props: {
        user: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            nameForm: this.$apolloForm({
                name: this.user.name,
            }, { client: 'defaultClient' }),
            imageForm: this.$apolloForm({
                avatar: this.user.avatar,
            }, { client: 'defaultClient' }),
            editingImage: false,
        };
    },
    computed: {
        // user() {
        //     return this.$root.authenticatedUser;
        // },
        newName() {
            return this.user.name !== this.nameForm.name.trim();
        },
        accountDuration() {
            return this.$dayjs().to(this.user.createdAt, true);
        },
        createdAtFormatted() {
            return this.$dayjs(this.user.createdAt).format('lll');
        },
        currentEmailText() {
            return this.$t('settings.general.email.currentEmail', { currentEmail: this.user.email });
        },
    },
    methods: {
        async saveImage() {
            await updateAvatar(this.imageForm);
            this.imageForm.avatar = this.user.avatar;
            this.editingImage = false;
            this.$saveFeedback();
        },
        async saveName() {
            await updateFullName(this.nameForm);
            this.$saveFeedback();
        },
    },
    watch: {
        'user.name': function onNewName(newVal) {
            this.nameForm.name = newVal;
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-account-general {
    &__line {
        @apply
            mb-10
    }

    &__header {
        @apply
            font-semibold
            text-cm-600
            text-sm
        ;
    }

    &__description {
        @apply
            text-cm-500
            text-sm
        ;
    }
}

</style>
