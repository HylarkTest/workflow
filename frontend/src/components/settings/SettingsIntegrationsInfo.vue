<template>
    <div class="o-settings-integrations-info">
        <div
            class="o-settings-integrations-info__logo"
        >
            <img
                :src="'/images/integrations/' + integration.id + '.png'"
            >
        </div>
        <!--  <h3
            v-t="{ path: 'integrations.integrateWith', args: { integration: integration.name } }"
            class="font-semibold text-2xl text-cm-500 mb-10"
        >
        </h3> -->

        <ul
            class="mb-8 flex flex-wrap max-w-full justify-center"
        >
            <li
                v-for="data in dataOptions"
                :key="data"
                class="flex mx-8 items-center"
            >
                <div
                    class="o-settings-integrations-info__circle circle-center bg-primary-100 mr-2"
                >
                    <i
                        class="fal text-primary-600 text-xl"
                        :class="options[data]"
                    >
                    </i>
                </div>

                <span
                    v-t="'common.' + data"
                    class="text-cm-600 text-lg font-semibold"
                >
                </span>
            </li>
        </ul>

        <div class="mb-8">
            <h4
                v-t="'integrations.ableTo'"
                class="header-uppercase text-center mb-3"
            >
            </h4>
            <ul class="flex flex-wrap justify-center -mx-4">
                <li
                    v-for="ability in abilities"
                    :key="ability.id"
                    class="o-settings-integrations-info__ability"
                >
                    <div
                        class="o-settings-integrations-info__check circle-center bg-secondary-600"
                    >
                        <i
                            class="fas"
                            :class="ability.icon"
                        >
                        </i>
                    </div>

                    <span
                        v-t="'integrations.' + integration.id + '.abilities.' + ability.id"
                    >
                    </span>
                </li>
            </ul>
        </div>

        <div>
            <h4
                v-t="'common.instructions'"
                class="header-uppercase text-center mb-3"
            >
            </h4>
            <ol
                class="o-settings-integrations-info__steps"
            >
                <li
                    v-for="step in integrationSteps"
                    :key="step.id"
                    class="mb-4"
                >
                    <div class="flex">
                        <div
                            class="o-settings-integrations-info__count"
                        >
                            <span>
                                {{ step.id }}.
                            </span>
                        </div>

                        <div>
                            <span
                                v-t="'integrations.' + integration.id + '.steps.' + step.id"
                            >
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="step.button"
                        class="ml-8"
                    >
                        <component
                            v-if="step.button.component"
                            :is="step.button.component"
                            class="mt-2"
                            :disabled="isButtonDisabled(step.button)"
                            @click="instructionAction(step.button)"
                        ></component>
                        <button
                            v-else
                            v-t="step.button.textPath"
                            type="button"
                            class="button--sm button-primary"
                            :class="buttonClass(step.button)"
                            :disabled="isButtonDisabled(step.button)"
                            @click="instructionAction(step.button)"
                        >
                        </button>
                    </div>

                    <div
                        v-if="step.permissionOptions"
                        class="ml-10"
                    >
                        <div
                            v-for="data in dataOptions"
                            :key="data"
                            class="mb-1"
                        >
                            <CheckHolder
                                v-model="integrationData"
                                :val="data"
                                size="sm"
                            >
                                {{ $t('common.' + data) }}
                            </CheckHolder>
                        </div>
                    </div>
                </li>
            </ol>
        </div>
    </div>
</template>

<script>

import config from '@/core/config.js';
import GoogleButton from '@/components/settings/GoogleButton.vue';

const appleSteps = [
    {
        id: 1,
        button: {
            link: '',
            textPath: 'integrations.apple.getCode',
        },
    },
    {
        id: 2,
        button: {
            link: '',
            textPath: 'common.integrate',
        },
    },
];

const googleSteps = [
    {
        id: 1,
        permissionOptions: true,
    },
    {
        id: 2,
        button: {
            component: 'GoogleButton',
            action: 'integrateWithGoogle',
            condition: 'integrationData.length',
        },
    },
];

const microsoftSteps = [
    {
        id: 1,
        permissionOptions: true,
    },
    {
        id: 2,
        button: {
            action: 'integrateWithMicrosoft',
            textPath: 'common.integrate',
            condition: 'integrationData.length',
        },
    },
];

const appleAbilities = [
    {
        id: 1,
        icon: 'fa-arrows-rotate',
    },
    {
        id: 2,
        icon: 'fa-eye',
    },
    {
        id: 3,
        icon: 'fa-pencil-alt',
    },
];

const googleAbilities = [
    {
        id: 1,
        icon: 'fa-arrow-pointer',
    },
    {
        id: 2,
        icon: 'fa-eye',
    },
    {
        id: 3,
        icon: 'fa-arrows-rotate',
    },
];

const microsoftAbilities = [
    {
        id: 1,
        icon: 'fa-arrow-pointer',
    },
    {
        id: 2,
        icon: 'fa-eye',
    },
    {
        id: 3,
        icon: 'fa-arrows-rotate',
    },
];

const options = {
    calendar: 'fa-calendar-alt',
    todos: 'fa-square-check',
    emails: 'fa-envelope',
};

const appleOptions = [
    'calendar',
];

const googleOptions = [
    'calendar',
    'todos',
    'emails',
];

const microsoftOptions = [
    'calendar',
    'todos',
    'emails',
];

export default {
    name: 'SettingsIntegrationInfo',
    components: {
        GoogleButton,
    },
    mixins: [
    ],
    props: {
        integration: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            integrationData: [],
        };
    },
    computed: {
        integrationSteps() {
            return this[`${this.integration.id}Steps`];
        },
        dataOptions() {
            return this[`${this.integration.id}Options`];
        },
        abilities() {
            return this[`${this.integration.id}Abilities`];
        },
        scopes() {
            return this.integrationData.map((data) => data.toUpperCase());
        },
    },
    methods: {
        isButtonDisabled(button) {
            if (button.condition) {
                return !_.get(this, button.condition);
            }
            return false;
        },
        instructionAction(button) {
            if (!this.isButtonDisabled(button)) {
                this[button.action]();
            }
        },
        buttonClass(button) {
            return { unclickable: this.isButtonDisabled(button) };
        },
        integrateWithGoogle() {
            const scopesQuery = this.scopes.map((scope) => `scopes[]=${scope}`).join('&');
            window.open(`${config('app.api-url')}/integrate/google?${scopesQuery}`);
        },
        integrateWithMicrosoft() {
            const scopesQuery = this.scopes.map((scope) => `scopes[]=${scope}`).join('&');
            window.open(`${config('app.api-url')}/integrate/microsoft?${scopesQuery}`);
        },
    },
    watch: {
        'integration.id': function onChangeId() {
            this.integrationData = [];
        },
    },
    created() {
        this.appleSteps = appleSteps;
        this.microsoftSteps = microsoftSteps;
        this.googleSteps = googleSteps;

        this.appleOptions = appleOptions;
        this.microsoftOptions = microsoftOptions;
        this.googleOptions = googleOptions;

        this.appleAbilities = appleAbilities;
        this.microsoftAbilities = microsoftAbilities;
        this.googleAbilities = googleAbilities;

        this.options = options;
    },
};
</script>

<style scoped>

.o-settings-integrations-info {
    @apply
        flex
        flex-col
        items-center
    ;

    &__logo {
        height: 80px;
        width: 180px;

        @apply
            flex
            items-end
            justify-center
            mb-10
        ;
    }

    &__circle {
        height: 40px;
        width: 41px;
    }

    &__ability {

        @apply
            bg-cm-100
            flex
            m-2
            px-4
            py-2
            rounded-md
            text-sm
            w-full
        ;

        @media (min-width: 1024px) {
            max-width: 250px;
        }
    }

    &__check {
        height: 23px;
        min-width: 23px;
        width: 23px;

        @apply
            mr-2
            text-cm-00
            text-xs
        ;
    }

    /*&__steps {
    }*/

    &__count {
        min-width: 34px;
        width: 34px;
    }
}

</style>
