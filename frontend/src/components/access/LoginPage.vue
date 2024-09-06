<template>
    <NoNavBase class="o-login-page">
        <div
            class="o-login-page__space"
        >
            <img
                :src="'/banners/GraphicShapesFlyingBird.png'"
                alt="Marty, the Hylark bird, flying"
            />
        </div>
        <img
            ref="image"
            class="o-login-page__graphic"
            :src="'/banners/GraphicShapesFlyingBird.png'"
            alt="Marty, the Hylark bird, flying"
        />
        <div class="o-login-page__container">
            <AccessFormBase
                :form="form"
                title="login.welcomeBack"
                buttonText="common.signIn"
                :buttonDisabled="disabledSignIn"
                :footerLink="footerLink"
                @goNext="login"
            >
                <template
                    #top
                >
                    <div
                        v-if="showSessionExpiredBox"
                        class="bg-azure-100 px-3 py-2 rounded-md leading-tight text-xssm mb-4"
                    >
                        <div class="font-semibold mb-1">
                            <i class="fa-regular fa-timer mr-0.5">
                            </i>
                            Your session expired due to inactivity.
                        </div>
                        <p>Log in again to pick up where you left off!</p>
                    </div>
                </template>

                <div class="mb-6">
                    <InputLine
                        formField="email"
                        name="email"
                        :motion="true"
                        type="email"
                        autocomplete="off"
                        :disabled="disableEmail"
                    >
                        <template #label>
                            {{ $t('labels.email') }}
                        </template>
                    </InputLine>
                </div>

                <div>
                    <InputPassword
                        formField="password"
                        name="password"
                        :motion="true"
                    >
                    </InputPassword>
                    <router-link
                        v-t="'login.noPassword'"
                        :to="{ name: 'access.passwordReset' }"
                        class="block italic mt-2 text-gray-500 text-right text-xs hover:underline"
                    >
                    </router-link>
                </div>

                <div class="mt-2">
                    <CheckHolder
                        ref="rememberMe"
                        class="inline-flex"
                        size="sm"
                        name="remember"
                        formField="remember"
                        :disabled="blocksFunctionalCookies"
                    >
                        {{ $t('login.rememberMe') }}
                    </CheckHolder>

                    <CookiesNotifier
                        v-if="refsSet && blocksFunctionalCookies"
                        :activator="$refs.rememberMe"
                        nudgeUpProp="0.125rem"
                        nudgeLeftProp="0.625rem"
                    >
                    </CookiesNotifier>
                </div>
            </AccessFormBase>
        </div>
    </NoNavBase>
</template>

<script>
import interactsWithEventBus from '@/vue-mixins/interactsWithEventBus.js';

import CookiesNotifier from '@/components/access/CookiesNotifier.vue';
import AccessFormBase from '@/components/access/AccessFormBase.vue';
import NoNavBase from '@/components/layout/NoNavBase.vue';
import { login } from '@/core/auth.js';
import { isTooManyRequestsError } from '@/http/checkResponse.js';
import { allowsFunctionalCookies, COOKIE_SET } from '@/core/helpers/cookieHelpers.js';

export default {
    name: 'LoginPage',
    components: {
        CookiesNotifier,
        AccessFormBase,
        NoNavBase,
    },
    mixins: [
        interactsWithEventBus,
    ],
    props: {

    },
    data() {
        const email = this.$route.query.email || '';
        return {
            form: this.$form({
                password: '',
                email,
                remember: false,
            }),
            footerLink: {
                text: 'login.noAccount',
                clickable: 'common.signUp',
                link: 'register.initial',
                query: _.pick(this.$route.query, ['uses']),
            },
            processing: false,
            allowsFunctionalCookies: allowsFunctionalCookies(),
            refsSet: false,
            listeners: {
                updateFunctionalCookies: [COOKIE_SET],
            },
        };
    },
    computed: {
        disabledSignIn() {
            return this.processing || !this.form.password || !this.form.email;
        },
        disableEmail() {
            return !!this.$route.query.email;
        },
        blocksFunctionalCookies() {
            return !this.allowsFunctionalCookies;
        },
        showSessionExpiredBox() {
            return this.$route.query.session_expired === 'true';
        },
    },
    methods: {
        async login() {
            try {
                this.processing = true;
                await login(this.form);
                if (this.$route.query.redirect) {
                    const redirect = this.$route.query.redirect;
                    if (_.startsWith(redirect, 'http')) {
                        window.location = redirect;
                    } else {
                        await this.$router.push(this.$route.query.redirect);
                    }
                } else {
                    await this.$router.push({ name: 'home' });
                }
            } catch (e) {
                if (e.one_time_password) {
                    await this.$router.push({ name: 'access.otp' });
                } else if (e.two_factor) {
                    await this.$router.push({ name: 'access.authentication' });
                } else if (isTooManyRequestsError(e)) {
                    this.$errorFeedback({
                        customHeaderPath: 'feedback.responses.login.header',
                        customMessagePath: 'feedback.responses.login.message',
                    });
                } else {
                    throw e;
                }
            } finally {
                this.processing = false;
            }
        },
        updateFunctionalCookies() {
            this.allowsFunctionalCookies = allowsFunctionalCookies();
        },
    },
    mounted() {
        // For popup to show elegantly and not jump
        setTimeout(() => {
            this.refsSet = true;
        }, 1500);
    },
};
</script>

<style scoped>

.o-login-page {
    @apply
        bg-primary-100
        flex
        min-h-full
        overflow-hidden
        relative
    ;

    &__container {
        @apply
            flex
            flex-col
            items-center
            justify-end
            mb-10
            relative
            w-full
            z-over
        ;
    }

    &__graphic {
        max-height: 80%;

        @apply
            absolute
            bottom-0
            right-0
        ;
    }

    &__space {
        display: none;
    }

    &__image {
        @apply
            absolute
            left-8
            top-4
        ;
    }
}

@media (min-width: 768px) {
    .o-login-page {
        @apply
            bg-cm-00
        ;

        &__graphic {
            display: none;
        }

        &__space {
            max-width: 30%;
            padding: 60px 0 0 0;

            @apply
                bg-azure-50
                flex
                flex-col
                justify-end
                min-h-screen
            ;
        }

        &__container {
            @apply
                flex
                flex-1
                items-center
                justify-center
                relative
            ;
        }
    }
}

</style>
