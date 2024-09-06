<template>
    <NoNavBase class="o-otp-page">
        <div class="o-otp-page__container">
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
                title="access.otp"
                subtitle="access.otpSubtitle"
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
                            One-time code
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
    name: 'OtpPage',
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
            try {
                this.processing = true;
                await twoFa(this.form, true);
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

.o-otp-page {
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
