<template>
    <component
        v-if="!$apollo.loading"
        :is="dropdownComponent"
        :modelValue="modelValue"
        :groups="combinedLists"
        :property="property"
        class="c-list-picker"
        :popupProps="{ maxHeightProp: '7.5rem' }"
        :displayRule="nameWithSpace"
        :placeholder="placeholder"
        v-bind="$attrs"
        @update:modelValue="$emit('update:modelValue', $event)"
    >
        <template
            #group="{ group }"
        >
            <div
                class="flex text-xs mb-1 px-2 mt-4 first:mt-2 items-baseline"
            >
                <i
                    v-if="group.provider"
                    class="mr-1 text-cm-300"
                    :class="integrationIcon(group.provider)"
                >
                </i>
                <span
                    class="uppercase font-semibold text-cm-400"
                >
                    {{ group.name }}
                </span>
            </div>
        </template>

        <template
            #option="{ original }"
        >
            <div class="flex justify-between w-full items-baseline">
                {{ original.name }}

                <i
                    v-if="pageSymbol && original.isOnPage"
                    class="fa-light text-cm-500"
                    :class="pageSymbol"
                    :title="pageSymbolTooltip"
                >
                </i>
            </div>
        </template>
    </component>
</template>

<script>

import interactsWithFeatureListLoading from '@/vue-mixins/features/interactsWithFeatureListLoading.js';
import INTEGRATIONS from '@/graphql/account-integrations/AccountIntegrations.gql';

import { getIntegrationIcon } from '@/core/display/integrationIcons.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import { getFirstKey } from '@/core/utils.js';
import { initializeCalendars } from '@/core/repositories/calendarRepository.js';
import { initializeTodoLists } from '@/core/repositories/todoListRepository.js';
import { initializeMailboxes } from '@/core/repositories/mailboxRepository.js';

const initializeMap = {
    EVENTS: initializeCalendars,
    CALENDAR: initializeCalendars,
    TODOS: initializeTodoLists,
    EMAILS: initializeMailboxes,
};

export default {
    name: 'ListPicker',
    components: {

    },
    mixins: [
        interactsWithFeatureListLoading,
    ],
    props: {
        modelValue: {
            type: [Object, String, null],
            default: null,
        },
        dropdownComponent: {
            type: String,
            default: 'DropdownBox',
        },
        placeholder: {
            type: String,
            default: 'Select a list',
        },
        spaceIds: {
            type: [Array, null],
            default: null,
        },
        integrationAccountId: {
            type: String,
            default: null,
        },
        type: {
            type: String,
            required: true,
        },
        property: {
            type: [Function, String],
            default: null,
        },
        onlyInternal: Boolean,
        defaultFirstSystem: Boolean,
        page: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'update:modelValue',
    ],
    apollo: {
        lists: {
            query() {
                return this.getListQuery(this.type);
            },
            skip() {
                return !!this.integrationAccountId;
            },
            variables() {
                return this.spaceIds ? { spaceIds: this.spaceIds } : {};
            },
            update(data) {
                return getFirstKey(initializeConnections(data));
            },
        },
        integrations: {
            query: INTEGRATIONS,
            skip() {
                return this.onlyInternal || this.spaceIds?.length || !this.hasExternalListQuery(this.type);
            },
        },
    },
    data() {
        return {
            integrationLists: {},
        };
    },
    computed: {
        combinedLists() {
            const integrations = this.validIntegrationsLength && this.integratedFormatted?.length
                ? this.integratedFormatted
                : [];
            return _.concat(this.nativeFormatted, integrations);
        },
        integratedFormatted() {
            return _(this.integrationLists).map((lists) => {
                const account = lists[0].account;
                const accountId = account.id;
                const integrationObj = _.find(this.integrationsForType, { id: accountId });
                return {
                    group: {
                        ...account,
                        name: integrationObj.accountName,
                    },
                    options: lists.filter((list) => !list.isReadOnly),
                };
            }).value();
        },
        nativeGrouped() {
            return _(this.pageAvailableLists || []).groupBy('space.id');
        },
        nativeFormatted() {
            return _(this.nativeGrouped).map((lists) => {
                return {
                    group: lists[0].space,
                    options: lists,
                };
            }).value();
        },
        validIntegrationsLength() {
            return this.integrationsForType?.length;
        },
        integrationsForType() {
            return this.integrations?.filter(
                (integration) => integration.scopes.includes(this.featureType)
            );
        },
        featureType() {
            return this.type === 'EVENTS' ? 'CALENDAR' : this.type;
        },
        pageLists() {
            return this.page?.lists || [];
        },
        pageAvailableLists() {
            const available = this.lists || [];
            available.forEach((list) => {
                const index = _.findIndex(available, { id: list.id });
                available[index].isOnPage = this.pageLists.includes(list.id);
            });
            return available;
        },
        pageSymbol() {
            return this.page?.symbol;
        },
        pageSymbolTooltip() {
            return `This list is viewable on the "${this.page?.name}" page`;
        },
    },
    methods: {
        nameWithSpace(list) {
            return list.name;
        },
        integrationIcon(provider) {
            return getIntegrationIcon(provider);
        },
    },
    watch: {
        integrationsForType(integrations) {
            integrations.forEach((integration) => {
                if (this.integrationAccountId && this.integrationAccountId !== integration.id) {
                    return;
                }
                this.$apollo.addSmartQuery(`${integration.id}`, {
                    query: this.getExternalListQuery(this.type),
                    variables: { sourceId: integration.id },
                    manual: true,
                    fetchPolicy: 'network-only',
                    result(results, id) {
                        if (results.data) {
                            this.integrationLists[id] = initializeMap[this.featureType](results.data).data;
                        }
                    },
                });
            });
        },
    },
    created() {
    },
};
</script>

<style scoped>

/*.c-list-picker {

} */

</style>
