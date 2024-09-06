<template>
    <div class="o-space-general">

        <SettingsHeaderLine
            class="mb-10"
        >
            <template
                #header
            >
                Space name
            </template>

            <FormWrapper
                :form="nameForm"
                @submit="saveName"
            >
                <div class="o-settings-page__box flex items-center">
                    <InputLine
                        formField="name"
                        :placeholder="$t('labels.name')"
                    >
                    </InputLine>
                    <button
                        v-t="'common.save'"
                        class="button-primary button--sm ml-4"
                        :class="{ unclickable: processing || !canSave }"
                        type="submit"
                        :disabled="processing || !canSave"
                    >
                    </button>
                </div>
            </FormWrapper>

        </SettingsHeaderLine>

        <SettingsHeaderLine>
            <template
                #header
            >
                Delete "{{ spaceName }}"
            </template>

            <template
                #description
            >
                This space will be permanently deleted and non-recoverable.
            </template>

            <button
                class="bg-peach-600 hover:bg-peach-500 text-cm-00 button"
                type="button"
                :class="{ unclickable: processingDelete }"
                :disabled="processingDelete"
                @click="deleteSpace"
            >
                Delete
            </button>
        </SettingsHeaderLine>

        <ConfirmModal
            v-if="isModalOpen"
            @closeModal="closeModal"
            @cancelAction="closeModal"
            @proceedWithAction="confirmDelete"
        >
            <p class="mb-3">
                If you continue, you will not be able to access this space or any of the pages it contains.
            </p>

            <p>
                Are you sure you want to delete this space?
            </p>
        </ConfirmModal>
    </div>
</template>

<script>

import ConfirmModal from '@/components/assets/ConfirmModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import { deleteSpace, updateSpace } from '@/core/repositories/spaceRepository.js';

export default {
    name: 'SpaceGeneral',
    components: {
        ConfirmModal,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        space: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'closeModal',
    ],
    data() {
        return {
            nameForm: this.$apolloForm({
                id: this.space.id,
                name: this.space.name,
            }),
            processing: false,
            processingDelete: false,
        };
    },
    computed: {
        newName() {
            return this.spaceName !== this.formName;
        },
        formName() {
            return this.nameForm.name;
        },
        canSave() {
            return this.newName && this.formName;
        },
        spaceName() {
            return this.space?.name;
        },
    },
    methods: {
        deleteSpace() {
            this.openModal();
        },
        async confirmDelete() {
            this.closeModal();
            this.processingDelete = true;
            try {
                // Close modal first, otherwise it will be closed as a byproduct of the space not existing
                // The warning will appear anyway
                this.$emit('closeModal');
                await deleteSpace(this.space);
                this.$successFeedback();
            } catch (error) {
                this.$warningFeedback({ customMessageString: error.message });
                throw error;
            }
        },
        async saveName() {
            this.processing = true;
            try {
                await updateSpace(this.nameForm);
                this.$saveFeedback();
            } finally {
                this.processing = false;
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-space-general {

} */

</style>
