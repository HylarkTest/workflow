<template>
    <div class="o-account-security">
        <SettingsHeaderLine class="o-account-security__line">
            <template #header>
                {{ $t('settings.security.password.header') }}
            </template>

            <template #description>
                {{ $t('settings.security.password.description') }}
            </template>

            <FormWrapper
                :form="form"
                @submit="updatePassword"
            >
                <div
                    class="flex items-start"
                    :class="{ unclickable: processingPassword }"
                >
                    <div class="o-account-security__passwords">

                        <div class="mb-8">
                            <InputPassword
                                formField="currentPassword"
                                name="currentPassword"
                                autocomplete="password"
                                labelPath="labels.currentPassword"
                            >
                            </InputPassword>
                        </div>

                        <div>
                            <InputPassword
                                formField="password"
                                name="password"
                                autocomplete="new-password"
                                labelPath="labels.newPassword"
                                :newPasswordInput="true"
                            >
                            </InputPassword>

                            <PasswordGuide
                                class="mt-3"
                                :text="form.password"
                                @passwordCriteria="updateCriteria"
                            >
                            </PasswordGuide>
                        </div>
                    </div>

                    <button
                        v-if="hasPasswordFields"
                        v-t="'common.update'"
                        class="bg-primary-600 hover:bg-primary-500 text-cm-00 button--sm ml-4"
                        type="submit"
                    >
                    </button>
                </div>
            </FormWrapper>
        </SettingsHeaderLine>

        <SettingsHeaderLine class="o-account-security__line">
            <template #header>
                {{ $t('settings.security.2fa.header') }}
            </template>

            <template #description>
                {{ $t('settings.security.2fa.description') }}
            </template>

            <div class="p-4 rounded-xl relative bg-cm-100">
                <div class="absolute -top-2 -right-2 ">
                    <CloseButton
                        v-if="currentStage && !has2fa"
                        class="bg-cm-200"
                        @click="cancel2fa"
                    >
                    </CloseButton>
                </div>

                <div
                    v-if="currentStage === 0 && !has2fa"
                    class="flex items-start"
                >
                    <button
                        v-t="'common.activate'"
                        class="bg-primary-600 hover:bg-primary-500 text-cm-00 button"
                        type="button"
                        @click="enable2fa"
                    >
                    </button>

                    <div class="ml-6">
                        <p
                            v-t="'settings.security.2fa.activate.header'"
                            class="o-account-security__header"
                        >
                        </p>
                        <p
                            v-t="'settings.security.2fa.activate.description'"
                            class="o-account-security__description"
                        >
                        </p>
                    </div>
                </div>

                <div
                    v-if="has2fa"
                    class="flex items-start"
                >
                    <button
                        v-t="'common.deactivate'"
                        class="bg-primary-600 hover:bg-primary-500 text-cm-00 button"
                        type="button"
                        @click="confirmDisable"
                    >
                    </button>

                    <div class="ml-6">
                        <p
                            v-t="'settings.security.2fa.isActivated.header'"
                            class="o-account-security__header"
                        >
                        </p>
                        <p
                            v-t="'settings.security.2fa.isActivated.description'"
                            class="o-account-security__description"
                        >
                        </p>
                    </div>
                </div>

                <AuthProcess
                    v-if="currentStage && !has2fa"
                    :currentStage="currentStage"
                    :svg="svg"
                    :code="code"
                    @confirmCode="confirmCode"
                    @set2faStep="set2faStep"
                >
                </AuthProcess>

                <div
                    v-if="currentStage === 4"
                    class="mt-4 bg-emerald-100 p-3 rounded-xl text-sm"
                >
                    <p
                        v-t="'settings.security.2fa.success.header'"
                        class="o-account-security__header"
                    >
                    </p>
                    <p
                        v-t="'settings.security.2fa.success.description'"
                        class="o-account-security__description"
                    >
                    </p>
                </div>

            </div>
        </SettingsHeaderLine>

        <SettingsHeaderLine class="o-account-security__line">
            <template #header>
                {{ $t('settings.security.login.header') }}
            </template>

            <template #description>
                {{ $t('settings.security.login.description') }}
            </template>

            <LoginHistory
                v-if="loginHistory"
                :loginHistory="loginHistory"
            >
            </LoginHistory>
        </SettingsHeaderLine>

        <VerifyPassword
            v-if="isModalOpen"
            @closeModal="closeModal"
            @cancelAction="closeModal"
            @verifyPassword="disable2fa"
        >
        </VerifyPassword>
    </div>
</template>

<script>

import AuthProcess from './AuthProcess.vue';
import LoginHistory from './LoginHistory.vue';
import PasswordGuide from '@/components/access/PasswordGuide.vue';
import CloseButton from '@/components/buttons/CloseButton.vue';
import VerifyPassword from '@/components/assets/VerifyPassword.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import {
    confirm2fa, disable2fa, get2faSvg, updatePassword,
} from '@/core/auth.js';
import getLoginHistory, {
    hasMoreLoginHistory,
    loadNextPageOfLoginHistory,
} from '@/core/repositories/loginHistoryRepository.js';

export default {
    name: 'AccountSecurity',
    components: {
        PasswordGuide,
        AuthProcess,
        LoginHistory,
        CloseButton,
        VerifyPassword,
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
            form: this.$form({
                currentPassword: '',
                password: '',
                email: this.user.email,
            }),
            isCriteriaMet: false,
            currentStage: 0,
            svg: null,
            code: null,
            loginHistory: null,
            processingPassword: false,
        };
    },
    computed: {
        hasPasswordFields() {
            return this.form.currentPassword && this.isCriteriaMet;
        },
        has2fa() {
            return this.user.hasEnabledTwoFactorAuthentication;
        },
    },
    methods: {
        async updatePassword() {
            this.processingPassword = true;
            try {
                await updatePassword(this.form);
                this.form.reset();
                this.$saveFeedback({
                    customHeaderPath: 'feedback.responses.saved.updated',
                    customMessagePath: 'feedback.responses.password.updated',
                });
            } finally {
                this.processingPassword = false;
            }
        },
        updateCriteria(val) {
            this.isCriteriaMet = val;
        },
        async enable2fa() {
            const response = await get2faSvg();
            this.svg = response.data.svg;
            this.code = response.data.code;
            this.currentStage = 2;
        },
        async confirmCode(form) {
            await confirm2fa(form);
            this.currentStage = 4;
        },
        async disable2fa(form) {
            await disable2fa(form);
            this.closeModal();
            this.cancel2fa();
        },
        confirmDisable() {
            this.openModal();
        },
        getNextPageOfLoginHistory() {
            if (hasMoreLoginHistory()) {
                loadNextPageOfLoginHistory();
            }
        },
        set2faStep(step) {
            this.currentStage = step;
        },
        cancel2fa() {
            this.currentStage = 0;
        },
    },
    watch: {
        has2fa(val) {
            if (val) {
                this.currentStage = 4;
            }
        },
    },
    async created() {
        this.loginHistory = await getLoginHistory();
    },
};
</script>

<style scoped>

.o-account-security {
    &__line {
        @apply
            mb-10
        ;
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

    &__passwords {
        @apply
            bg-cm-100
            p-4
            rounded-xl
            w-full
        ;

        @media (min-width: 1024px) {
            width: 400px;
        }
    }
}

</style>
