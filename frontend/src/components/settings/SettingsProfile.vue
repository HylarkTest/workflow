<template>
    <div
        class="o-settings-profile"
    >
        <IconContainer
            class="mb-10"
            :header="meHeader"
            icon="fal fa-id-card-clip"
        >
            <template
                #subheader
            >
                Select how you want to appear to other users on the "{{ baseName }}" base
            </template>
            <ProfileMe
                :user="user"
            >
            </ProfileMe>

        </IconContainer>

        <IconContainer
            class="mb-10"
            header="Leave base"
            icon="fal fa-person-walking-dashed-line-arrow-right"
        >
            <SettingsHeaderLine>
                <template
                    v-if="showLeave"
                    #description
                >
                    If you leave this base, you will no longer be able to access it or any
                    of the data it contains unless you are re-invited.
                </template>

                <template
                    v-else
                    #description
                >
                    You are the sole owner on this base. Please designate another owner to leave this base.
                </template>

                <p
                    v-if="isOwner && !baseHasOneOwner"
                    class="text-sm bg-gold-100 p-4 rounded-lg mb-2"
                >
                    You are an owner on this base.
                </p>

                <button
                    v-if="showLeave"
                    class="bg-rose-600 hover:bg-rose-500 text-cm-00 button"
                    type="button"
                    @click="openModal"
                >
                    Leave this base
                </button>
            </SettingsHeaderLine>
        </IconContainer>

        <ConfirmModal
            v-if="isModalOpen"
            @closeModal="closeModal"
            @cancelAction="closeModal"
            @proceedWithAction="leaveBase"
        >
            Are you sure you want to leave "{{ baseName }}"?
        </ConfirmModal>
    </div>
</template>

<script>

import ProfileMe from '@/components/settings/ProfileMe.vue';
import IconContainer from '@/components/display/IconContainer.vue';
import ConfirmModal from '@/components/assets/ConfirmModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import { leaveBase } from '@/core/repositories/baseRepository.js';

export default {
    name: 'SettingsProfile',
    components: {
        ProfileMe,
        IconContainer,
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

        };
    },
    computed: {
        activeBase() {
            return this.user.activeBase();
        },
        baseName() {
            return this.activeBase.name;
        },
        meHeader() {
            return `Me on "${this.baseName}"`;
        },
        isOwner() {
            return this.activeBase.pivot.role === 'OWNER';
        },
        showLeave() {
            return !this.isSingleOwner;
        },
        isSingleOwner() {
            return this.isOwner && this.baseHasOneOwner;
        },
        baseOwners() {
            return this.activeBase.members.filter((member) => {
                return member.role === 'OWNER';
            });
        },
        baseHasOneOwner() {
            return this.baseOwners.length === 1;
        },
    },
    methods: {
        async leaveBase() {
            await leaveBase();
            this.$router.push({ name: 'settings' });
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-settings-profile {

} */

</style>
