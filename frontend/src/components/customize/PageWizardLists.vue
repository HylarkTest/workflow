<template>
    <div class="o-page-wizard-lists">
        <div class="max-w-xl">
            <h2 class="o-creation-wizard__prompt mt-10">
                What do you want to see in your new {{ featureName }} page?
            </h2>
            <p
                class="o-creation-wizard__description text-center mb-4"
            >
                Select from existing {{ featureName }} lists in your "{{ space.name }}"
                space and/or create new ones to add to "{{ pageForm.name }}"
            </p>

            <div class="flex items-center flex-col mb-2">
                <button
                    class="button-rounded--sm button-secondary mb-2"
                    type="button"
                    @click="addList"
                >
                    <i class="far fa-circle-plus mr-2">
                    </i>
                    Add a list
                </button>

                <div class="w-72">
                    <div
                        v-for="(list, index) in listForm.newLists"
                        :key="list.val"
                        class="button bg-cm-100 my-2 relative flex items-center justify-between"
                    >
                        <div class="flex items-center flex-1">
                            <ColorSquare
                                class="mr-2"
                                :currentColor="list.color"
                                :isModifiable="true"
                                @update:currentColor="updateNewList($event, index, 'color')"
                            >
                            </ColorSquare>

                            <div
                                class="relative flex-1"
                            >
                                <div
                                    @click="editName(list.val)"
                                >
                                    {{ list.name }}
                                </div>

                                <InputSubtle
                                    v-if="listBeingEdited === list.val"
                                    ref="nameInput"
                                    v-blur="closeEditing"
                                    :modelValue="list.name"
                                    displayClasses="absolute -top-1 -left-1 w-full"
                                    :alwaysHighlighted="true"
                                    @click.stop
                                    @keydown.enter.stop="closeEditing"
                                    @keydown.space.stop
                                    @update:modelValue="updateNewList($event, index, 'name')"
                                >
                                </InputSubtle>
                            </div>

                            <div
                                v-t="'common.new'"
                                class="o-page-wizard-lists__new"
                            >
                            </div>
                        </div>

                        <ClearButton
                            positioningClass="relative ml-2"
                            @click="removeNew(index)"
                        >
                        </ClearButton>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap">
                <div
                    v-for="list in lists"
                    :key="list.id"
                    class="p-2 w-1/2"
                >
                    <div
                        class="button bg-cm-100 hover:shadow-lg"
                    >
                        <CheckHolder
                            ref="check"
                            :modelValue="listForm.lists"
                            :val="list"
                            predicate="id"
                            class="items-center"
                            :ellipsis="true"
                            @update:modelValue="setList"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center max-w-full min-w-0">
                                    <ColorSquare
                                        class="mr-2"
                                        :currentColor="list.color"
                                    >
                                    </ColorSquare>
                                    <div class="text-cm-700 font-medium u-ellipsis">
                                        {{ list.name }}
                                    </div>
                                </div>

                                <div
                                    v-if="list.account && list.account.provider"
                                    class="ml- text-primary-300"
                                >
                                    <i
                                        :class="integrationIcon(list.account.provider)"
                                    >
                                    </i>
                                </div>
                            </div>
                        </CheckHolder>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import ColorSquare from '@/components/assets/ColorSquare.vue';
import ClearButton from '@/components/buttons/ClearButton.vue';

import interactsWithFeatureListLoading from '@/vue-mixins/features/interactsWithFeatureListLoading.js';

// import { initializeTodoLists } from '@/core/repositories/todoListRepository.js';
// import INTEGRATIONS from '@/graphql/account-integrations/AccountIntegrations.gql';
// import EXTERNAL_CALENDARS from '@/graphql/calendar/queries/ExternalCalendars.gql';
// import EXTERNAL_TODO_LISTS from '@/graphql/todos/queries/ExternalTodoLists.gql';
// import { initializeCalendars } from '@/core/repositories/calendarRepository.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';

import { getIntegrationIcon } from '@/core/display/integrationIcons.js';

const newListObj = {
    name: 'New list',
    color: '#48a728',
    val: '',
};

export default {
    name: 'PageWizardLists',
    components: {
        ColorSquare,
        ClearButton,
    },
    mixins: [
        interactsWithFeatureListLoading,
    ],
    props: {
        listForm: {
            type: Object,
            required: true,
        },
        pageForm: {
            type: Object,
            required: true,
        },
        space: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:listForm',
    ],
    apollo: {
        internalLists: {
            query() {
                return this.listQuery;
            },
            variables() {
                return {
                    spaceIds: this.space.id ? [this.space.id] : null,
                };
            },
            update(data) {
                return this.initializeLists(data);
            },
            skip() {
                return !this.listQuery;
            },
        },
        // integrations: {
        //     query: INTEGRATIONS,
        // },
    },
    data() {
        return {
            integrationLists: {},
            listBeingEdited: null,
        };
    },
    computed: {
        lists() {
            return this.internalLists;
            // return (this.internalLists || []).concat(..._.values(this.integrationLists));
        },
        listQuery() {
            return this.getListQuery(this.pageType);
        },
        // externalListQuery() {
        //     if (this.pageType === 'TODOS') {
        //         return EXTERNAL_TODO_LISTS;
        //     }
        //     if (this.pageType === 'CALENDAR') {
        //         return EXTERNAL_CALENDARS;
        //     }
        //     return null;
        // },
        // integrationsForType() {
        //     if (!['TODOS', 'CALENDAR'].includes(this.pageType)) {
        //         return [];
        //     }
        //     return this.integrations?.filter((integration) => integration.scopes.includes(this.pageType));
        // },
        featureName() {
            return this.$t(`common.pageTypes.${_.camelCase(this.pageType)}`);
        },
        pageType() {
            return this.pageForm.type;
        },
    },
    methods: {
        addList() {
            const newVal = _.clone(this.listForm.newLists || []);
            const newItem = _.clone(newListObj);
            newItem.val = new Date().getTime();
            newVal.push(newItem);
            this.$emit('update:listForm', { valKey: 'newLists', newVal });
            this.editName(newItem.val);
        },
        updateNewList(newVal, index, valKey) {
            const clone = _.clone(this.listForm.newLists);
            clone[index][valKey] = newVal;
            this.$emit('update:listForm', { valKey: 'newLists', newVal: clone });
        },
        removeNew(index) {
            const clone = _.clone(this.listForm.newLists);
            clone.splice(index, 1);
            this.$emit('update:listForm', { valKey: 'newLists', newVal: clone });
        },
        async editName(val) {
            this.listBeingEdited = val;
            await this.$nextTick();
            this.$refs.nameInput[0].select();
        },
        closeEditing() {
            const index = _.findIndex(this.listForm.newLists, { val: this.listBeingEdited });
            const list = this.listForm.newLists[index];

            if (!list.name) {
                this.setNameIfNone(index);
            } else {
                this.listBeingEdited = null;
            }
        },
        async setNameIfNone(index) {
            this.updateNewList('New list', index, 'name');
            await this.$nextTick();
            this.$refs.nameInput[0].select();
        },
        initializeLists(data) {
            const results = initializeConnections(data);
            return results[_.keys(data)[0]];
        },
        // initializeExternalLists(data) {
        //     if (this.pageType === 'TODOS') {
        //         return initializeTodoLists(data).data;
        //     }
        //     if (this.pageType === 'CALENDAR') {
        //         return initializeCalendars(data).data;
        //     }
        //     return null;
        // },
        integrationIcon(provider) {
            return getIntegrationIcon(provider);
        },
        setList(val) {
            this.$emit('update:listForm', { valKey: 'lists', newVal: val });
        },
    },
    watch: {
        // integrationsForType(integrations, oldIntegrations) {
        //     if (oldIntegrations) {
        //         oldIntegrations.forEach((integration) => {
        //             this.$apollo.queries[`${integration.id}`].destroy();
        //         });
        //     }
        //     integrations.forEach((integration) => {
        //         this.$apollo.addSmartQuery(`${integration.id}`, {
        //             query: this.externalListQuery,
        //             variables: { sourceId: integration.id },
        //             manual: true,
        //             fetchPolicy: 'network-only',
        //             result(results, id) {
        //                 if (results.data) {
        //                     this.integrationLists[id] = this.initializeExternalLists(results.data);
        //                 }
        //             },
        //         });
        //     });
        // },
    },
    created() {

    },
};
</script>

<style scoped>

.o-page-wizard-lists {
    &__new {
        @apply
            absolute
            bg-primary-200
            font-semibold
            px-2
            py-0.5
            -right-5
            rounded-full
            text-primary-600
            text-xs
            -top-3
        ;
    }
}

</style>
