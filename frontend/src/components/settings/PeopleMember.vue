<template>
    <div
        class="o-people-member flex items-center"
        :class="{ unclickable: processingRemove }"
    >
        <ProfileNameImage
            class="flex-1"
            :profile="member"
            :showEmail="true"
            icon="fa-user"
        >
        </ProfileNameImage>

        <div class="flex flex-col items-end">
            <div class="flex items-center">
                <div
                    class="center h-8 w-8 rounded-full"
                    :class="statusColor"
                    :title="statusTooltip"
                >
                    <i
                        class="fa-light"
                        :class="statusIcon"
                    >
                    </i>
                </div>

                <div class="ml-4">
                    <FormWrapper
                        v-if="canChangeRole"
                        :form="roleForm"
                    >
                        <RoleSelect
                            :modelValue="roleForm.role"
                            :disabled="processingRole"
                            :roleArrName="roleArrName"
                            bgColor="gray"
                            size="lg"
                            @update:modelValue="setRole"
                        >
                        </RoleSelect>
                    </FormWrapper>
                    <div
                        v-else
                        class="font-medium text-smbase"
                        :title="roleTitle"
                    >
                        {{ roleDisplay }}
                    </div>
                </div>

                <button
                    v-if="showRemove"
                    class="button--sm button-peach ml-4"
                    type="button"
                    @click="openModal"
                >
                    <i
                        class="fa-regular fa-circle-minus mr-1"
                    >
                    </i>
                    Remove
                </button>
            </div>

            <div class="flex flex-col items-end">
                <div
                    class="text-xs mt-1 text-cm-500"
                >
                    <label
                        class="mr-1 font-semibold"
                    >
                        {{ dateLabel }}:
                    </label>
                    <span>
                        {{ date }}
                    </span>
                </div>
                <button
                    v-if="showReinvite"
                    class="button--xs button-primary--light mt-1"
                    :class="{ unlcikable: processingInvite }"
                    type="button"
                    @click="reinvite"
                >
                    Re-invite
                </button>
            </div>
        </div>

        <ConfirmModal
            v-if="isModalOpen"
            @closeModal="closeModal"
            @cancelAction="closeModal"
            @proceedWithAction="removeMember"
        >
            {{ confirmText }}
        </ConfirmModal>
    </div>
</template>

<script>

import ConfirmModal from '@/components/assets/ConfirmModal.vue';
import ProfileNameImage from '@/components/profile/ProfileNameImage.vue';
import RoleSelect from '@/components/settings/RoleSelect.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import {
    removeMember,
    updateRole,
    removeInvite,
    inviteMember, updateInvite,
} from '@/core/repositories/baseRepository.js';

export default {
    name: 'PeopleMember',
    components: {
        ProfileNameImage,
        ConfirmModal,
        RoleSelect,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        member: {
            type: Object,
            required: true,
        },
        base: {
            type: Object,
            required: true,
        },
        isBaseOwnerOrAdmin: Boolean,
        isBaseOwner: Boolean,
        hasOnlyOneOwner: Boolean,
    },
    data() {
        const roleForm = this.member.__typename === 'MemberInvite'
            ? this.$form({
                role: this.member.role,
                email: this.member.email,
            })
            : this.$apolloForm({
                role: this.member.role,
                id: this.member.id,
            }, { client: 'defaultClient' });
        return {
            roleForm,
            processingRole: false,
            processingRemove: false,
            processingInvite: false,
            reinviteForm: this.$form({
                emails: [this.member.email],
                role: this.member.role,
            }),
        };
    },
    computed: {
        isInvite() {
            return this.member.__typename === 'MemberInvite';
        },
        isAuthenticatedUser() {
            return this.member.isAuthenticatedUser;
        },
        isMemberOwner() {
            return this.role === 'OWNER';
        },
        isSingleOwner() {
            return this.isMemberOwner && this.hasOnlyOneOwner;
        },
        role() {
            return this.member.role;
        },
        roleDisplay() {
            return this.$t(`common.roles.${_.toLower(this.role)}`);
        },
        baseName() {
            return this.base.name;
        },
        showRemove() {
            if (this.isAuthenticatedUser) {
                return false;
            }
            if (this.isMemberOwner) {
                return this.isBaseOwner && !this.hasOnlyOneOwner;
            }
            return true;
        },
        canResend() {
            return this.isInvite && this.$dayjs().diff(this.member.invitedAt, 'days') > 1;
        },
        confirmText() {
            return this.isInvite
                ? `Are you sure you want to revoke the invite to "${this.member.email}" from "${ this.baseName }"`
                : `Are you sure you want to remove "${this.member.name}" from "${ this.baseName }"`;
        },
        canChangeRole() {
            if (this.isBaseOwner) {
                return !this.isSingleOwner;
            }
            return this.isBaseOwnerOrAdmin && !this.isMemberOwner;
        },
        roleTitle() {
            return this.isSingleOwner && this.isBaseOwnerOrAdmin
                ? 'Each base must have at least one owner. Add a second owner to change this role.'
                : '';
        },
        statusIcon() {
            return this.isInvite
                ? 'fa-paper-plane'
                : 'fa-circle-check';
        },
        statusColor() {
            return this.isInvite
                ? 'text-cm-400 bg-cm-100'
                : 'text-emerald-600 bg-emerald-100';
        },
        statusTooltip() {
            return this.isInvite
                ? 'Invite sent'
                : 'Member';
        },
        dateLabel() {
            return this.isInvite
                ? 'Last invited'
                : 'Member since';
        },
        date() {
            const data = this.isInvite ? this.member.invitedAt : this.member.addedAt;
            return this.$dayjs(data).format('lll');
        },
        roleArrName() {
            return this.isBaseOwner ? 'ALL' : 'BASIC';
        },
        showReinvite() {
            return this.isBaseOwnerOrAdmin && this.isInvite && this.inviteOlderThan24h;
        },
        inviteOlderThan24h() {
            return this.$dayjs().diff(this.member.invitedAt, 'hours') > 24;
        },
    },
    methods: {
        async removeMember() {
            this.processingDelete = true;
            try {
                if (this.isInvite) {
                    await removeInvite(this.member.email);
                } else {
                    await removeMember(this.member.id);
                }
            } finally {
                this.processingDelete = false;
                this.closeModal();
            }
        },
        async setRole(role) {
            this.processingRole = true;
            try {
                this.roleForm.role = role;
                if (this.isInvite) {
                    await updateInvite(this.roleForm);
                } else {
                    await updateRole(this.roleForm);
                    if (this.isAuthenticatedUser && this.roleForm.role === 'MEMBER') {
                        this.$router.push({ name: 'settings.profile', params: { baseId: this.$route.params.baseId } });
                    }
                }
                this.$saveFeedback();
            } finally {
                this.processingRole = false;
            }
        },
        async reinvite() {
            this.processingInvite = true;
            try {
                await inviteMember(this.reinviteForm);
                this.$successFeedback();
            } catch (error) {
                const response = error.response;
                if (response.status === 422) {
                    this.$warningFeedback({
                        customMessageString: response.data.message,
                    });
                } else {
                    throw error;
                }
            } finally {
                this.processingInvite = false;
            }
        },
    },
    watch: {
        member(member) {
            this.roleForm.role = member.role;
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-people-member {

} */

</style>
