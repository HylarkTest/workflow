<template>
    <div
        class="c-group-edit-general"
        :class="{ unclickable: processingDelete }"
    >

        <FormWrapper
            class="mb-10"
            :form="form"
            @submit="saveGroup"
        >
            <SettingsHeaderLine
                class="mb-6"
            >
                <template
                    #header
                >
                    {{ $t(getString('name')) }}
                </template>

                <div class="max-w-sm">
                    <InputLine
                        formField="name"
                        :placeholder="$t('labels.name')"
                    >
                    </InputLine>
                </div>

            </SettingsHeaderLine>

            <SettingsHeaderLine
                v-if="!hideDescription"
                class="mb-4"
            >
                <template
                    #header
                >
                    {{ $t('labels.description') }}
                </template>

                <TextareaField
                    formField="description"
                    :placeholder="$t('labels.description')"
                >
                </TextareaField>
            </SettingsHeaderLine>

            <button
                v-t="'common.save'"
                class="button-primary button"
                :class="{ unclickable: disabled && !processingDelete }"
                :disabled="disabled"
                type="submit"
            >
            </button>
        </FormWrapper>

        <SettingsHeaderLine>
            <template
                #header
            >
                Delete "{{ group.name }}"
            </template>

            <template
                #description
            >
                {{ $t(getString('deleteDescription')) }}
            </template>

            <button
                class="bg-peach-600 hover:bg-peach-500 text-cm-00 button"
                type="button"
                :disabled="processingDelete"
                @click="deleteGroup"
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
                {{ $t(getString('warning')) }}
            </p>

            <p>
                {{ $t(getString('sure')) }}
            </p>
        </ConfirmModal>
    </div>
</template>

<script>

import ConfirmModal from '@/components/assets/ConfirmModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import interactsWithFormCanSave from '@/vue-mixins/common/interactsWithFormCanSave.js';

export default {
    name: 'GroupEditGeneral',
    components: {
        ConfirmModal,
    },
    mixins: [
        interactsWithModal,
        interactsWithFormCanSave,
    ],
    props: {
        group: {
            type: Object,
            required: true,
        },
        repository: {
            type: Object,
            required: true,
        },
        groupType: {
            type: String,
            required: true,
        },
        hideDescription: Boolean,
    },
    data() {
        return {
            form: this.$apolloForm(() => {
                const data = {
                    id: this.group.id,
                    name: this.group.name,
                };

                if (!this.hideDescription) {
                    data.description = this.group.description || '';
                }
                return data;
            }),
            processingDelete: false,
            processingSave: false,
            requiredFields: ['name'],
        };
    },
    computed: {
        formName() {
            return this.form.name;
        },
        groupTypeString() {
            return _.camelCase(this.groupType);
        },
        disabled() {
            return !this.canSave || this.processingSave;
        },
        checkerForm() {
            return this.form;
        },
        checkerOriginal() {
            return this.group;
        },
    },
    methods: {
        deleteGroup() {
            this.openModal();
        },
        async confirmDelete() {
            this.closeModal();
            this.processingDelete = true;
            await this.repository.deleteGroup(this.group);
        },
        async saveGroup() {
            this.processingSave = true;
            await this.repository.updateGroup(this.form);
            this.$saveFeedback();
            this.processingSave = false;
        },
        getString(textKey) {
            return `customizations.${this.groupTypeString}.edit.${textKey}`;
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-group-edit-general {

} */

</style>
