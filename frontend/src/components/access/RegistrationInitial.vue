<template>
    <NoNavBase class="o-registration-initial">
        <img
            class="o-registration-initial__graphic"
            :src="'/banners/GraphicShapesLookingBackBird.png'"
        />

        <div class="o-registration-initial__container">

            <h2 class="o-registration-initial__banner">
                Your data, your goals, your way.
            </h2>

            <AccessFormBase
                :form="form"
                :title="titlePath"
                :subtitle="subtitlePath"
                :buttonDisabled="disabledJoin"
                :buttonTooltip="mainButtonTooltip"
                :buttonText="mainButtonTitle"
                :footerLink="footerLink"
                @goNext="continueWithRegistration"
            >
                <template
                    v-if="onPassword"
                >
                    <PasswordForm
                        :form="form"
                        @passwordCriteria="setPasswordCriteria"
                    >
                    </PasswordForm>
                </template>

                <template
                    v-else
                >
                    <div class="mb-6">
                        <InputLine
                            name="name"
                            formField="name"
                            :motion="true"
                        >
                            <template #label>
                                {{ $t('labels.name') }}
                            </template>
                        </InputLine>
                    </div>

                    <div>
                        <InputLine
                            name="email"
                            formField="email"
                            :motion="true"
                            :disabled="disableEmail"
                        >
                            <template #label>
                                {{ $t('labels.email') }}
                            </template>

                        </InputLine>
                    </div>

                    <div class="mt-3">
                        <CheckHolder
                            formField="permission"
                            size="sm"
                        >
                            I agree with the
                            <a
                                :href="terms"
                                rel="noreferrer noopener"
                                target="_blank"
                                class="underline"
                                @click.stop
                            >
                                terms and conditions
                            </a>
                            and
                            <a
                                :href="privacy"
                                rel="noreferrer noopener"
                                target="_blank"
                                class="underline"
                                @click.stop
                            >
                                privacy policy
                            </a>
                        </CheckHolder>
                    </div>
                </template>

                <!-- <template #lowerSpace>
                    <span class="text-xs italic text-center mt-2">
                        Password set after registration to activate account
                    </span>
                </template> -->
            </AccessFormBase>
        </div>
        <div class="o-registration-initial__space flex flex-col items-center">
            <div class="text-azure-600 font-bold text-3xl leading-normal mb-8">
                <h2>
                    Your data,
                </h2>

                <h2>
                    your goals,
                </h2>

                <!-- <h2>
                    your style,
                </h2> -->

                <h2>
                    your way.
                </h2>
            </div>
            <img
                class="o-registration-initial__bird"
                :src="'/banners/GraphicShapesLookingBackBird.png'"
            />
        </div>
    </NoNavBase>
</template>

<script>

import AccessFormBase from '@/components/access/AccessFormBase.vue';
import NoNavBase from '@/components/layout/NoNavBase.vue';
import PasswordForm from '@/components/access/PasswordForm.vue';

import config from '@/core/config.js';
import { checkRegistration, register } from '@/core/auth.js';
import { isValidationError } from '@/http/checkResponse.js';

export default {
    name: 'RegistrationInitial',
    components: {
        AccessFormBase,
        NoNavBase,
        PasswordForm,
    },
    mixins: [
    ],
    props: {

    },
    emits: [
        'join',
        'mounted',
    ],
    data() {
        const email = this.$route.query.email || '';
        return {
            form: this.$form({
                name: '',
                email,
                permission: false,
                password: '',
            }),
            footerLink: {
                text: 'registration.initial.alreadyHaveAccount',
                clickable: 'common.logIn',
                link: 'access.login',
                query: _.pick(this.$route.query, ['uses']),
            },
            processing: false,
            currentView: 1,
            isCriteriaMet: false,
        };
    },
    computed: {
        disableEmail() {
            return !!this.$route.query.email;
        },
        landingUrl() {
            return config('app.landing-url');
        },
        terms() {
            return `${this.landingUrl}/terms-and-conditions`;
        },
        privacy() {
            return `${this.landingUrl}/privacy-policy`;
        },
        disabledJoin() {
            if (this.processing) {
                return true;
            }
            if (this.onPassword) {
                return !this.form.password || !this.isCriteriaMet;
            }
            return !this.form.name
                || !this.form.email
                || !this.form.permission;
        },
        onPassword() {
            return this.currentView === 2;
        },
        titlePath() {
            return this.onPassword ? 'registration.initial.password.set' : 'common.hello';
        },
        subtitlePath() {
            return this.onPassword ? '' : 'registration.initial.subtitle';
        },
        mainButtonTooltip() {
            if (this.disabledJoin) {
                const textKey = this.onPassword ? 'completeToJoin' : 'completeToAdvance';
                return this.$t(`registration.initial.${textKey}`);
            }
            return '';
        },
        mainButtonTitle() {
            return this.onPassword ? 'registration.initial.join' : 'common.next';
        },
    },
    methods: {
        setPasswordCriteria(val) {
            this.isCriteriaMet = val;
        },
        async joinHylark() {
            if (!this.disabledJoin) {
                this.processing = true;
                try {
                    await register(this.form);
                    this.$emit('join');
                } catch (error) {
                    this.processing = false;
                    throw error;
                }
            }
        },
        async continueWithRegistration() {
            if (this.onPassword) {
                await this.joinHylark();
            } else {
                this.processing = true;
                try {
                    await checkRegistration(this.form);
                    this.currentView = 2;
                } catch (error) {
                    if (!isValidationError(error)) {
                        throw error;
                    }
                } finally {
                    this.processing = false;
                }
            }
        },
    },
    created() {
    },
    mounted() {
        this.$emit('mounted');
    },
};
</script>

<style>

.o-registration-initial {
    @apply
        bg-azure-100
        flex
        max-h-screen
        min-h-full
        overflow-hidden
        relative
    ;

    &__graphic {
        max-height: 50%;
        max-width: 40%;
        @apply
            absolute
            bottom-0
            left-0
        ;
    }

    &__banner {
        margin-top: 50px;

        @apply
            font-bold
            leading-none
            mb-6
            px-10
            text-3xl
            text-azure-600
            text-center
        ;
    }

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

    &__space {
        display: none;
    }
}

@media (min-width: 768px) {
    .o-registration-initial {
        @apply
            bg-cm-00
        ;

        &__graphic {
            display: none;
        }

        &__banner {
            display: none;
        }

        &__container {
            @apply
                flex-1
                items-center
                justify-center
                relative
            ;
        }

        &__space {
            max-width: 30%;
            padding: 40px 0 0;

            @apply
                bg-azure-50
                flex
                flex-col
                justify-end
            ;
        }

        &__bird {
            max-height: 450px;
        }
    }
}

</style>
