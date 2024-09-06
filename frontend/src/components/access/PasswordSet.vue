<template>
    <NoNavBase class="o-password-set">
        <div
            v-if="successMessage"
            class="px-6"
        >
            {{ $t(successMessage) }}
            <div class="text-sm flex items-center mb-3 text-gray-500">
                <i
                    class="far fa-arrow-left mr-1"
                >
                </i>

                <router-link
                    :to="{ name: 'access.login' }"
                    class="font-semibold hover:underline"
                >
                    {{ 'Back to login' }}
                </router-link>
            </div>
        </div>
        <AccessFormBase
            v-else
            :form="form"
            title="access.setPassword"
            subtitle="access.setSubtitle"
            buttonText="common.continue"
            headerSizeClass="text-2xl"
            @goNext="setNew"
        >
            <div class="mb-6">
                <InputLine
                    name="email"
                    formField="email"
                    disabled
                >
                    <template #label>
                        {{ $t('labels.email') }}
                    </template>
                </InputLine>
            </div>

            <PasswordForm
                :form="form"
            >
            </PasswordForm>

        </AccessFormBase>
    </NoNavBase>
</template>

<script>

import AccessFormBase from '@/components/access/AccessFormBase.vue';
import NoNavBase from '@/components/layout/NoNavBase.vue';
import PasswordForm from '@/components/access/PasswordForm.vue';
import { resetPassword } from '@/core/auth.js';

export default {
    name: 'PasswordSet',
    components: {
        AccessFormBase,
        NoNavBase,
        PasswordForm,
    },
    mixins: [
    ],
    props: {

    },
    data() {
        return {
            form: this.$form({
                password: '',
                email: this.$route.query.email || '',
                token: this.$route.query.token || null,
            }),
            successMessage: null,
        };
    },
    computed: {
    },
    methods: {
        // onPasswordFocus(focusState) {
        //     this.passwordFocused = focusState;
        // },
        async setNew() {
            const response = await resetPassword(this.form);
            this.successMessage = response.data.status;
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-password-set {
    @apply
        flex
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
