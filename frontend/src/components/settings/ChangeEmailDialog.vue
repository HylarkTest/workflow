<template>
    <FullDialog
        class="o-change-email-dialog relative"
        paddingClass="py-8"
        :confirmClose="!formIsComplete"
        headerColorName="secondary"
        @closeFullDialog="closeFullDialog"
    >
        <template #title>
            <i class="far fa-envelopes mr-2">
            </i>
            {{ $t('settings.changeEmail.header') }}
        </template>

        <template #leaveConfirm>
            {{ $t('settings.changeEmail.leave.header') }}
            {{ $t('settings.changeEmail.leave.description') }}
        </template>

        <h2 class="o-change-email-dialog__header">
            {{ stepHeader }}
        </h2>

        <div class="o-change-email-dialog__container">
            <FormWrapper
                v-if="!formIsComplete"
                class="o-change-email-dialog__form"
                :form="currentStep.form"
                @submit="submitForm(currentStep.submitFunction, step + 1)"
            >
                <div class="centered flex-col">
                    <div class="mb-8 w-full">
                        <InputPassword
                            v-if="isPasswordStep"
                            formField="password"
                            name="password"
                            :motion="true"
                        >
                        </InputPassword>

                        <InputLine
                            v-else-if="isEmailStep"
                            formField="email"
                            name="email"
                            :motion="true"
                            type="email"
                            autocomplete="off"
                        >
                            <template #label>
                                {{ $t('labels.email') }}
                            </template>
                        </InputLine>

                        <InputLine
                            v-else-if="isCodeStep"
                            formField="code"
                            name="code"
                            :motion="true"
                        >
                            <template #label>
                                {{ $t('settings.changeEmail.oneTimeCode') }}
                            </template>
                        </InputLine>
                    </div>

                    <button
                        v-t="'common.next'"
                        class="button--lg button-primary flex items-center"
                        :class="{ unclickable: !currentStep.nextCondition }"
                        :disabled="!currentStep.nextCondition"
                        type="submit"
                    >
                    </button>
                </div>
            </FormWrapper>

            <div
                v-if="isCodeStep"
                class="mt-8"
            >
                <button
                    v-for="(button, index) in codeStepButtons"
                    :key="index"
                    class="o-change-email-dialog__button button--lg"
                    :class="{ unclickable: processing }"
                    :disabled="processing"
                    type="button"
                    @click="button.action"
                >
                    {{ button.text }}
                </button>
            </div>

            <div
                v-if="formIsComplete"
                class="flex flex-col items-center"
            >
                <div class="w-fit">
                    <h3 class="o-change-email-dialog__subheader mb-4">
                        {{ $t('settings.changeEmail.whereNext') }}
                    </h3>
                    <ButtonEl
                        v-for="(button, index) in formIsCompleteButtons"
                        :key="index"
                        class="o-change-email-dialog__link button--lg"
                        type="button"
                        @click="button.action"
                    >
                        <i
                            class="far fa-fw mr-2 text-primary-500"
                            :class="button.icon"
                        >
                        </i>
                        {{ button.text }}
                    </ButtonEl>
                </div>

                <div class="bg-gold-100 rounded-lg p-4 mt-8">
                    <p
                        v-t="'settings.changeEmail.oldInvitesInvalid'"
                        class="text-lg text-cm-600 font-semibold text-center mb-2"
                    >
                    </p>
                    <p
                        v-t="'settings.changeEmail.notifyAdmins'"
                        class="text-cm-500 text-center mb-2"
                    >
                    </p>
                    <p
                        v-t="'settings.changeEmail.goodToGo'"
                        class="text-cm-500 text-center"
                    >
                    </p>
                </div>
            </div>
        </div>
    </FullDialog>
</template>

<script>
import FullDialog from '@/components/dialogs/FullDialog.vue';

import { checkPassword, updateEmail, verifyUpdateEmail } from '@/core/auth.js';

export default {
    name: 'ChangeEmailDialog',
    components: {
        FullDialog,
    },
    mixins: [
    ],
    props: {
    },
    emits: [
        'closeFullDialog',
    ],
    data() {
        return {
            passwordForm: this.$form({
                password: '',
            }),
            emailForm: {},
            codeForm: this.$form({
                code: '',
            }),
            processing: false,
            step: 0,
            codeStepButtons: [
                {
                    text: this.$t('settings.changeEmail.resendCode'),
                    action: () => this.resendCode(),
                },
                {
                    text: this.$t('settings.changeEmail.differentEmail'),
                    action: () => { this.step -= 1; },
                },
            ],
            formIsCompleteButtons: [
                {
                    text: this.$t('settings.changeEmail.homePageLink'),
                    icon: 'fa-home',
                    action: () => this.$router.push({ name: 'home' }),
                },
                {
                    text: this.$t('settings.changeEmail.accountSettingsLink'),
                    icon: 'fa-angle-left',
                    action: () => this.closeFullDialog(),
                },
            ],
        };
    },
    computed: {
        stepData() {
            return {
                password: {
                    form: this.passwordForm,
                    nextCondition: !this.processing && !!this.passwordForm.password.trim(),
                    submitFunction: () => checkPassword(this.passwordForm),
                },
                email: {
                    form: this.emailForm,
                    nextCondition: !this.processing && !!this.emailForm.email.trim(),
                    submitFunction: () => updateEmail(this.emailForm),
                },
                code: {
                    form: this.codeForm,
                    nextCondition: !this.processing && !!this.codeForm.code.trim(),
                    submitFunction: () => verifyUpdateEmail(this.codeForm),
                },
                end: {},
            };
        },
        currentStep() {
            return Object.values(this.stepData)[this.step];
        },
        currentKey() {
            return Object.keys(this.stepData)[this.step];
        },
        isPasswordStep() {
            return this.currentKey === 'password';
        },
        isEmailStep() {
            return this.currentKey === 'email';
        },
        isCodeStep() {
            return this.currentKey === 'code';
        },
        formIsComplete() {
            return this.currentKey === 'end';
        },
        newEmail() {
            return this.emailForm.email;
        },
        stepHeader() {
            const path = `settings.changeEmail.steps.${this.currentKey}`;
            if (this.isCodeStep || this.formIsComplete) {
                return this.$t(path, { newEmail: this.emailForm.email });
            }
            return this.$t(path);
        },
    },
    methods: {
        async submitForm(formFunction, nextStep = this.step) {
            this.processing = true;

            try {
                await formFunction();

                this.step = nextStep;
                if (this.isEmailStep) {
                    this.newEmailForm();
                }
            } catch (error) {
                if (error.response.status === 429) {
                    this.$errorFeedback({
                        customHeaderPath: 'feedback.responses.429.header',
                        customMessagePath: 'feedback.responses.429.message',
                    });
                } else if (error.response.status === 403) {
                    this.$errorFeedback({
                        customHeaderPath: 'feedback.responses.otpExpired.header',
                        customMessagePath: 'feedback.responses.otpExpired.message',
                    });
                } else {
                    throw error;
                }
            } finally {
                this.processing = false;
            }
        },
        newEmailForm() {
            this.emailForm = this.$form({
                email: '',
                password: this.passwordForm.password,
            });
        },
        resendCode() {
            this.submitForm(this.stepData.email.submitFunction);
        },
        closeFullDialog() {
            this.$emit('closeFullDialog');
        },
    },
    created() {
        this.newEmailForm();
    },
};
</script>

<style scoped>

.o-change-email-dialog {

    &__container {
        @apply
            flex
            flex-col
            items-center
        ;
    }

    &__header, &__subheader {
        @apply
            font-semibold
            text-center
        ;
    }

    &__header {
        @apply
            mb-10
            text-2xl
            text-cm-600
        ;
    }

    &__subheader {
        @apply
            mb-4
            text-cm-400
            text-xl
        ;
    }

    &__form {
        @apply
            bg-cm-100
            px-8
            py-4
            rounded-lg
            w-fit
        ;
    }

    &__button, &__link {
        @apply
            hover:shadow-lg
            my-4
            text-center
            w-full
        ;
    }

    &__button {
        @apply
            bg-primary-600
            text-cm-00
        ;
    }

    &__link {
        @apply
            bg-cm-100
            text-primary-500
        ;
    }

}

</style>
