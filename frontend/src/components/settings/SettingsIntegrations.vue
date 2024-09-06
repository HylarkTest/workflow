<template>
    <div class="o-settings-integrations">
        <div class="mb-16">
            <p
                class="font-semibold text-sm mb-2"
            >
                {{ $t('integrations.selectOption', { baseName: baseName }) }}
            </p>

            <div class="flex flex-wrap gap-2 max-w-full">
                <ButtonEl
                    v-for="option in integrationOptions"
                    :key="option.id"
                    class="o-settings-integrations__option centered relative"
                    :class="optionClasses(option)"
                    @click="toggleSelectedOption(option)"
                    @keyup.enter="toggleSelectedOption(option)"
                    @keyup.space="toggleSelectedOption(option)"
                >
                    <div
                        v-if="option.comingSoon"
                        class="o-settings-integrations__soon"
                    >
                        Coming soon
                    </div>
                    <img
                        :src="'/images/integrations/' + option.id + '.png'"
                        class="o-settings-integrations__img"
                    />
                </ButtonEl>
            </div>

            <div
                class="bg-secondary-100 rounded-xl p-4 text-sm inline-flex mt-2 font-medium"
            >
                {{ dataDisclaimer }}
            </div>

            <SettingsIntegrationsInfo
                v-if="selectedOption"
                class="mt-10"
                :integration="selectedOption"
            >
            </SettingsIntegrationsInfo>
        </div>

        <div
            v-if="integrations && integrations.length"
        >
            <h2
                v-t="'integrations.currentIntegrations'"
                class="header-2 mb-2"
            >
            </h2>

            <SettingsIntegrationsCurrent
                :integrations="integrations"
                @deleteIntegration="deleteIntegration"
            >
            </SettingsIntegrationsCurrent>
        </div>
    </div>
</template>

<script>

import SettingsIntegrationsInfo from './SettingsIntegrationsInfo.vue';
import SettingsIntegrationsCurrent from './SettingsIntegrationsCurrent.vue';

import INTEGRATIONS from '@/graphql/account-integrations/AccountIntegrations.gql';
import DELETE_INTEGRATION from '@/graphql/account-integrations/DeleteAccountIntegrations.gql';
import ACCOUNT_INTEGRATED from '@/graphql/account-integrations/AccountIntegratedSubscription.gql';
import { updateQueryWithResponse } from '@/core/helpers/apolloHelpers.js';
import { arrRemoveId } from '@/core/utils.js';

const integrationOptions = [
    // {
    //     id: 'apple',
    //     name: 'Apple',
    // },
    {
        id: 'google',
        name: 'Google',
    },
    {
        id: 'microsoft',
        name: 'Microsoft',
    },
];

export default {
    name: 'SettingsIntegrations',
    components: {
        SettingsIntegrationsInfo,
        SettingsIntegrationsCurrent,
    },
    mixins: [
    ],
    props: {
        user: {
            type: Object,
            required: true,
        },
    },
    apollo: {
        integrations: {
            query: INTEGRATIONS,
            subscribeToMore: {
                document: ACCOUNT_INTEGRATED,
                result() {
                    this.$apollo.queries.integrations.refetch();
                },
            },
        },
    },
    data() {
        return {
            selectedOption: null,
        };
    },
    computed: {
        activeBase() {
            return this.user.activeBase();
        },
        baseName() {
            return this.activeBase.name;
        },
        baseType() {
            return this.activeBase.baseType;
        },
        baseTypeFormatted() {
            return _.camelCase(this.baseType);
        },
        dataDisclaimer() {
            return this.$t(`integrations.disclaimers.bases.${this.baseTypeFormatted}`);
        },
    },
    methods: {
        optionClasses(option) {
            return [
                {
                    'o-settings-integrations__option--selected': this.isSelectedOption(option),
                },
                {
                    unclickable: option.comingSoon,
                },
            ];
        },
        toggleSelectedOption(option) {
            if (this.isSelectedOption(option)) {
                this.unselectOption();
            } else {
                this.selectedOption = option;
            }
        },
        unselectOption() {
            this.selectedOption = null;
        },
        isSelectedOption(option) {
            return this.selectedOption?.id === option.id;
        },
        deleteIntegration(integration) {
            this.$apollo.mutate({
                mutation: DELETE_INTEGRATION,
                variables: {
                    input: { id: integration.id },
                },
                update: updateQueryWithResponse({ query: INTEGRATIONS }, null, 'integrations', (originalData) => {
                    return arrRemoveId(originalData, integration.id);
                }),
            });
        },
    },
    watch: {
        integrations(newVal, oldVal) {
            const now = newVal?.length || 0;
            const before = oldVal?.length || 0;
            if ((now - before) === 1) {
                this.selectedOption = null;
            }
        },
    },
    created() {
        this.integrationOptions = integrationOptions;
        // Temporarily allowing Google test account access for testing
        if (this.$root.authenticatedUser.email === 'oauthtest222@gmail.com') {
            this.integrationOptions[0].comingSoon = false;
        }
    },
};
</script>

<style scoped>

.o-settings-integrations {
    &__option {
        filter: grayscale(60%);
        opacity: 0.7;
        width: 210px;

        @apply
            border
            border-cm-300
            border-solid
            h-20
            px-10
            rounded-lg
        ;

        &:hover {
            filter: grayscale(0%);

            @apply
                opacity-100
            ;
        }

        &--selected {
            filter: grayscale(0%);

            @apply
                border-primary-600
                opacity-100
            ;
        }
    }

    &__soon {
        @apply
            absolute
            bg-cm-00
            font-semibold
            -left-3
            px-3
            py-0.5
            rounded-md
            shadow-lg
            text-xssm
            -top-3
        ;
    }

    &__img {
        max-height: 50px;

        @apply
            w-auto
        ;
    }
}

</style>
