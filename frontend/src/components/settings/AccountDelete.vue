<template>
    <div class="o-account-delete">

        <SettingsHeaderLine class="o-account-delete__line">

            <template
                #description
            >
                <div
                    v-if="ownedCollabBasesLength"
                >
                    <p class="mb-2">
                        {{ $t('settings.deleteAccount.baseOwner') }}
                    </p>
                    <p class="mb-2">
                        {{ $t('settings.deleteAccount.leaveBases') }}
                    </p>
                    <p>
                        {{ $t('settings.deleteAccount.assignOwner') }}
                    </p>
                </div>

                <div
                    v-if="!ownedCollabBasesLength"
                >
                    <p>
                        {{ $t('settings.deleteAccount.permanent') }}
                    </p>
                </div>
            </template>

            <button
                v-if="!ownedCollabBasesLength"
                class="bg-peach-600 hover:bg-peach-500 text-cm-00 button"
                :class="{ unclickable: processingDelete }"
                type="button"
                :disabled="processingDelete"
                @click="deleteAccount"
            >
                {{ $t('settings.deleteAccount.deleteAccountPrompt') }}
            </button>

            <div
                v-if="ownedCollabBasesLength"
                class="bg-gold-100 rounded-lg p-4 text-cm-600 text-sm"
            >
                <div class="o-account-general__header">
                    {{ $t('settings.deleteAccount.ownedBases') }}:
                </div>
                <div
                    v-for="base in ownedCollabBases"
                    :key="base.id"
                    class="o-account-general__description"
                >
                    {{ base.name }}
                </div>
            </div>
        </SettingsHeaderLine>

        <ConfirmModal
            v-if="isModalOpen"
            @closeModal="closeModal"
            @cancelAction="closeModal"
            @proceedWithAction="deleteAndLogout"
        >
            <AccountDeleteConfirm>
            </AccountDeleteConfirm>
        </ConfirmModal>
    </div>
</template>

<script>

import AccountDeleteConfirm from '@/components/settings/AccountDeleteConfirm.vue';
import ConfirmModal from '@/components/assets/ConfirmModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import {
    deleteAccount,
} from '@/core/repositories/userRepository.js';

export default {
    name: 'AccountDelete',
    components: {
        AccountDeleteConfirm,
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
        ownedCollabBases() {
            return this.user.allBases().filter((base) => {
                return base.baseType === 'COLLABORATIVE' && base.pivot.role === 'OWNER';
            });
        },
        ownedCollabBasesLength() {
            return this.ownedCollabBases.length;
        },
    },
    methods: {
        async deleteAndLogout() {
            // TODO: require password authentication
            this.closeModal();
            this.processingDelete = true;
            await deleteAccount();
            window.location.href = '/login';
        },
        deleteAccount() {
            this.openModal();
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-account-delete {
    &__line {
        @apply
            mb-10
    }
}

</style>
