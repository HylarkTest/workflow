<template>
    <NoNavBase class="o-authentication-page">
        <div class="o-authentication-page__container">
            <div
                v-if="successMessage"
                v-t="successMessage"
            >
            </div>
            <AccessFormBase
                v-else
                widthClass="w-full"
                :form="form"
                title="access.2fa"
                subtitle="access.2faSubtitle"
                buttonText="common.submit"
                :footerLink="footerLink"
                :buttonDisabled="processing"
                @goNext="confirm"
            >
                <div class="mb-6">
                    <InputLine
                        formField="code"
                        name="code"
                        :motion="true"
                    >
                        <template #label>
                            2fa passcode
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
import { twoFa } from '@/core/auth.js';

export default {
    name: 'AuthenticationPage',
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
                code: '',
            }),
            footerLink: {
                text: 'login.noAccount',
                clickable: 'common.signUp',
                link: 'register.initial',
            },
            successMessage: null,
            processing: false,
        };
    },
    computed: {

    },
    methods: {
        async confirm() {
            this.processing = true;
            try {
                await twoFa(this.form);
                if (this.$route.query.redirect) {
                    await this.$router.push(this.$route.query.redirect);
                } else {
                    await this.$router.push({ name: 'home' });
                }
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

.o-authentication-page {
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
