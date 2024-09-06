<template>
    <div
        class="o-settings-general"
        :class="{ unclickable: processingDelete }"
    >
        <IconContainer
            class="mb-10"
            header="Attributes"
            icon="fal fa-face-viewfinder"
        >
            <GeneralAttributes
                :base="activeBase"
                :isModifiable="isModifiable"
                :user="user"
            >
            </GeneralAttributes>
        </IconContainer>

        <IconContainer
            class="mb-10"
            header="Appearance"
            icon="fal fa-circle-nodes"
        >
            <GeneralAppearance
                :accentColor="accentColor"
                @update:accentColor="updateAccentColor"
            >
            </GeneralAppearance>
        </IconContainer>

        <IconContainer
            v-if="isDeletable"
            :header="deleteText"
            icon="fal fa-trash-alt"
        >
            <div class="mb-4 text-cm-800">
                <p
                    class="mb-2"
                >
                    This base and all of its data will be permanently deleted and non-recoverable.
                </p>

                <p>
                    Access will no longer be available to any of its members.
                </p>
            </div>

            <button
                class="bg-peach-600 hover:bg-peach-500 text-cm-00 button"
                type="button"
                @click="deleteBase"
            >
                Delete base
            </button>
        </IconContainer>

        <ConfirmModal
            v-if="isModalOpen"
            @closeModal="closeModal"
            @cancelAction="closeModal"
            @proceedWithAction="deleteAndRedirect"
        >
            <p class="mb-3">
                If you continue, you will not be able to access any of your records on "{{ baseName }}",
                recover any data, or access the base in any way.
            </p>

            <p>
                Are you sure you want to delete this base?
            </p>
        </ConfirmModal>
    </div>
</template>

<script>

import GeneralAppearance from '@/components/settings/GeneralAppearance.vue';
import GeneralAttributes from '@/components/settings/GeneralAttributes.vue';
import IconContainer from '@/components/display/IconContainer.vue';
import ConfirmModal from '@/components/assets/ConfirmModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import {
    deleteBase,
    updateAccentColor,
    isActiveBasePersonal,
} from '@/core/repositories/baseRepository.js';

export default {
    name: 'SettingsGeneral',
    components: {
        GeneralAppearance,
        IconContainer,
        GeneralAttributes,
        ConfirmModal,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        user: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            processingDelete: false,
        };
    },
    computed: {
        activeBase() {
            return this.user.activeBase();
        },
        isPersonalActive() {
            return isActiveBasePersonal();
        },
        accentColor() {
            return this.activeBase.preferences.accentColor;
        },
        isModifiable() {
            return !this.isPersonalActive;
        },
        isDeletable() {
            return !this.isPersonalActive;
        },
        deleteText() {
            return `Delete "${this.baseName}"`;
        },
        baseName() {
            return this.activeBase.name;
        },
    },
    methods: {
        async updateAccentColor(color) {
            await updateAccentColor(color);
            this.$debouncedSaveFeedback();
        },
        deleteBase() {
            this.openModal();
        },
        async deleteAndRedirect() {
            const baseName = this.baseName;
            this.closeModal();
            this.processingDelete = true;
            await deleteBase();
            this.$router.push({ name: 'settings.account' });
            this.$successFeedback({
                customMessageString: `"${baseName}" was successfully deleted.`,
            });
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-settings-general {

} */

</style>
