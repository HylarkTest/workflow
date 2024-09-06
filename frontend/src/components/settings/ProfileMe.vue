<template>
    <div class="o-profile-me">
        <SettingsHeaderLine
            class="mb-10"
        >
            <template
                #header
            >
                Avatar
            </template>

            <FormWrapper
                :form="imageForm"
            >
                <CheckHolder
                    :modelValue="basePivot.useAccountAvatar"
                    @update:modelValue="updateUseAccountAvatar"
                >
                    <span
                        class="text-smbase font-medium text-cm-600"
                    >
                        Use account avatar
                    </span>
                </CheckHolder>

                <div
                    class="mt-2.5 mb-2 uppercase font-semibold text-cm-400"
                >
                    Or
                </div>

                <div>
                    <label
                        class="mb-2 text-smbase font-medium text-cm-600 block"
                    >
                        Set an avatar specific to this base
                    </label>
                    <ImageContainer
                        v-model:editMode="editingImage"
                        :class="{ unclickable: basePivot.useAccountAvatar }"
                        displaySize="h-56 w-56"
                        formField="displayAvatar"
                        :image="basePivot.displayAvatar"
                        hasSaveButton
                        onlyCroppedImage
                        @saveImage="saveImage"
                    >
                    </ImageContainer>
                </div>
            </FormWrapper>

        </SettingsHeaderLine>

        <SettingsHeaderLine
            class="mb-10"
        >
            <template
                #header
            >
                Name
            </template>

            <FormWrapper
                :form="nameForm"
                @submit="saveName(nameForm)"
            >
                <CheckHolder
                    :modelValue="useAccountName"
                    @update:modelValue="updateUseAccountName"
                >
                    <span
                        class="text-smbase font-medium text-cm-600"
                    >
                        Use account name ({{ userName }})
                    </span>
                </CheckHolder>

                <div
                    class="mt-2.5 mb-2 uppercase font-semibold text-cm-400"
                >
                    Or
                </div>

                <div>
                    <label
                        class="mb-1 text-smbase font-medium text-cm-600 block"
                    >
                        Set a name specific to this base
                    </label>
                    <div
                        class="o-settings-page__box flex items-center"
                        :class="{ unclickable: useAccountName }"
                    >
                        <InputLine
                            ref="nameInput"
                            class="w-full"
                            formField="displayName"
                            placeholder="Name"
                        >
                        </InputLine>
                        <button
                            v-if="showSaveName"
                            v-t="'common.save'"
                            class="bg-primary-600 hover:bg-primary-500 text-cm-00 button--sm ml-4"
                            type="submit"
                        >
                        </button>
                    </div>
                </div>
            </FormWrapper>

        </SettingsHeaderLine>
    </div>
</template>

<script>

import ImageContainer from '@/components/images/ImageContainer.vue';

import { updateProfile } from '@/core/repositories/baseRepository.js';

export default {
    name: 'ProfileMe',
    components: {
        ImageContainer,
    },
    mixins: [
    ],
    props: {
        user: {
            type: Object,
            required: true,
        },
    },
    data() {
        const basePivot = this.user.activeBase().pivot;
        const name = basePivot.displayName || this.user.name;
        return {
            nameForm: this.$apolloForm({
                displayName: name,
            }, { client: 'defaultClient' }),
            imageForm: this.$apolloForm({
                displayAvatar: basePivot.displayAvatar || null,
            }, { client: 'defaultClient' }),
            useAccountName: !basePivot.displayName,
            editingImage: false,
        };
    },
    computed: {
        showSaveName() {
            return !this.useAccountName && this.originalName !== this.nameForm.displayName.trim();
        },
        userName() {
            return this.user.name;
        },
        basePivot() {
            return this.user.activeBase().pivot;
        },
        originalName() {
            return this.basePivot.displayName;
        },
    },
    methods: {
        updateUseAccountName(val) {
            if (val) {
                this.saveName(this.$apolloForm({ displayName: null }, { client: 'defaultClient' }));
                this.nameForm.displayName = this.user.name;
                this.useAccountName = true;
            } else {
                this.focusOnName();
                this.nameForm.displayName = this.user.name;
                this.useAccountName = false;
            }
        },
        focusOnName() {
            this.$refs.nameInput.select();
        },
        async updateUseAccountAvatar(val) {
            await updateProfile(this.$apolloForm({ useAccountAvatar: val }, { client: 'defaultClient' }));
            this.$saveFeedback();
        },
        async saveName(form) {
            await updateProfile(form);
            this.$saveFeedback();
            this.canSaveName = false;
        },
        async saveImage() {
            await updateProfile(this.imageForm);
            this.editingImage = false;
            this.$saveFeedback();
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-profile-me {

} */

</style>
