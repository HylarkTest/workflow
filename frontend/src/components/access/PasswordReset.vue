<template>
    <NoNavBase class="o-password-reset">
        <div class="o-password-reset__container">
            <div class="text-sm flex items-center mb-3 text-gray-500">
                <i
                    class="far fa-arrow-left mr-1"
                >
                </i>

                <router-link
                    :to="{ name: 'access.login' }"
                    class="font-semibold hover:underline"
                >

                    Back to login
                </router-link>
            </div>
            <div
                v-if="successMessage"
                v-t="successMessage"
                class="px-6"
            >
            </div>
            <AccessFormBase
                v-else
                widthClass="w-full"
                :form="form"
                title="access.resetPassword"
                subtitle="access.resetSubtitle"
                buttonText="common.submit"
                :footerLink="footerLink"
                @goNext="recover"
            >
                <div class="mb-6">
                    <InputLine
                        formField="email"
                        name="email"
                        :motion="true"
                    >
                        <template #label>
                            {{ $t('labels.email') }}
                        </template>
                    </InputLine>
                </div>

            </AccessFormBase>
        </div>
    </NoNavBase>
</template>

<script>

import AccessFormBase from '@/components/access/AccessFormBase.vue';
import NoNavBase from '@/components/layout/NoNavBase.vue';
import { forgotPassword } from '@/core/auth.js';

export default {
    name: 'PasswordReset',
    components: {
        AccessFormBase,
        NoNavBase,
    },
    mixins: [
    ],
    props: {

    },
    data() {
        return {
            form: this.$form({
                email: '',
            }),
            footerLink: {
                text: 'login.noAccount',
                clickable: 'common.signUp',
                link: 'register.initial',
            },
            successMessage: null,
        };
    },
    computed: {

    },
    methods: {
        async recover() {
            const response = await forgotPassword(this.form);
            this.successMessage = response.data.status;
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-password-reset {
    @apply
        flex
        flex-col
        items-center
        justify-center
        min-h-screen
        overflow-hidden
    ;

    &__image {
        @apply
            absolute
            left-8
            top-4
        ;
    }

    &__logo {
        height: 40px;
    }
}

</style>
