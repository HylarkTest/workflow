<template>
    <div class="c-entities-picker relative">
        <!-- v-blur="selectIfOneResultOrReset" -->
        <DropdownInput
            ref="dropdownInput"
            :is="dropdownComponent"
            v-model:inputVal="filterText"
            class="w-full"
            :groups="filteredEntities"
            groupDisplayRule="name"
            :displayRule="entitiesDisplay"
            :placeholder="placeholder"
            comparator="id"
            popupConditionalDirective="show"
            :dropdownComponent="dropdownComponent"
            :neverHighlighted="true"
            :hasCircleForSelected="true"
            :processing="processing"
            v-bind="$attrs"
            @update:modelValue="selectEntity"
        >
            <template
                v-for="(_, slot) in $slots"
                #[slot]="scope"
            >
                <slot
                    :name="slot"
                    v-bind="scope"
                />
            </template>

            <template
                v-if="showPostTopSlot"
                #postTop="{ selectedEvents, noResults }"
            >
                <slot
                    name="postTop"
                    :selectedEvents="selectedEvents"
                    :processing="processing"
                    :inputVal="inputVal"
                    :noResults="noResults"
                >
                    <div
                        v-if="showCreateNew && !processing"
                        class="flex flex-col items-center mt-1"
                    >
                        <div
                            v-if="!filterText"
                            class="font-semibold"
                        >
                            Or
                        </div>
                        <button
                            class="button--sm button-primary--light mt-1"
                            type="button"
                            @click="openModal"
                        >
                            Create new record
                        </button>
                    </div>
                </slot>

                <div
                    v-if="!filterText && !noResults"
                    class="pt-1.5 border-t text-sm border-solid border-cm-300 mt-2 w-full text-center font-semibold"
                >
                    <i
                        class="fa-regular fa-calendar-lines-pen mr-1"
                    >
                    </i>

                    {{ $t('common.recentRecords') }}
                </div>
            </template>
        </DropdownInput>
        <transition name="t-fade">
            <AlertTooltip
                v-if="error"
            >
                {{ error }}
            </AlertTooltip>
        </transition>

        <CreateRecordModal
            v-if="isModalOpen"
            :withFeatures="withFeatures"
            :nodeToAssociate="nodeToAssociate"
            :suggestedRecords="suggestedRecords"
            @closeModal="closeModal"
        >
        </CreateRecordModal>
    </div>
</template>

<script>

import AlertTooltip from '@/components/popups/AlertTooltip.vue';
import CreateRecordModal from '@/components/records/CreateRecordModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import assistsWithEntityQueries from '@/vue-mixins/features/assistsWithEntityQueries.js';

import { getName } from '@/core/display/theStandardizer.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';

import ENTITY_QUERY from '@/graphql/items/EntitySearch.gql';

export default {
    name: 'EntitiesPicker',
    components: {
        AlertTooltip,
        CreateRecordModal,
    },
    mixins: [
        assistsWithEntityQueries,
        interactsWithModal,
    ],
    props: {
        entityVal: {
            type: [Object, Array, null],
            required: true,
        },
        inputVal: {
            type: String,
            default: null,
        },
        type: {
            type: String,
            default: null,
        },
        withFeatures: {
            type: Array,
            default: null,
        },
        hasEmails: Boolean,
        spaceId: {
            type: String,
            default: null,
        },
        mappingId: {
            type: String,
            default: null,
        },
        error: {
            type: String,
            default: '',
        },
        placeholder: {
            type: String,
            default: 'Associate to data',
        },
        dropdownComponent: {
            type: String,
            default: 'DropdownBox',
        },
        showCreateNew: Boolean,
        suggestedRecords: {
            type: [Array, null],
            default: null,
        },
        nodeToAssociate: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'update:entityVal',
        'update:inputVal',
    ],
    apollo: {
        entities: {
            query: ENTITY_QUERY,
            variables() {
                const search = this.filters.freeText;
                const variables = {
                    ...this.basicEntityVariables,
                    ...(search ? { search } : {}),
                };
                variables.orderBy = search
                    ? [
                        { field: 'MAPPING', direction: 'DESC' },
                        { field: 'MATCH', direction: 'DESC' },
                    ]
                    : [
                        { field: 'UPDATED_AT', direction: 'DESC' },
                        { field: 'MAPPING', direction: 'DESC' },
                    ];
                return variables;
            },
            // skip() {
            //     return !this.filters.freeText;
            // },
            update: (data) => initializeConnections(data).allItems,
            debounce: 300,
            fetchPolicy: 'network-only',
        },
    },
    data() {
        return {
            filters: {
                freeText: '',
            },
        };
    },
    computed: {
        processing() {
            return this.$apollo.queries.entities.loading;
        },
        filterText: {
            get() {
                return !_.isNull(this.inputVal)
                    ? this.inputVal
                    : this.filters.freeText;
            },
            set(val) {
                if (_.isNull(this.inputVal)) {
                    this.filters.freeText = val;
                } else {
                    this.$emit('update:inputVal', val);
                }
            },
        },
        filteredEntities() {
            return _(this.entities).groupBy('mapping.id')
                .values()
                .map((groupedEntities) => ({
                    group: groupedEntities[0].mapping,
                    options: groupedEntities,
                }))
                .value();
        },
        basicEntityVariables() {
            return this.getRequestVariables({
                hasEmails: this.hasEmails,
                type: this.type,
                withFeatures: this.withFeatures,
                spaceId: this.spaceId,
                mappingId: this.mappingId,
            });
        },
        showRecentHeader() {
            return !this.processing && !this.filterText;
        },
        showPostTopSlot() {
            return this.$slots.postTop // If there's slot content
                || !this.filterText // For most recent items
                || (this.showCreateNew && !this.processing); // For the custom button in this component
        },
    },
    methods: {
        selectEntity(entity) {
            this.$emit('update:entityVal', entity);
            this.filterText = '';
        },
        updateInput(entity) {
            this.filters.freeText = getName(entity);
        },
        selectIfOneResultOrReset() {
            if (this.entities.length === 1) {
                if (this.entities[0].id === this.entityVal.id) {
                    return;
                }
                this.selectEntity(this.entities[0]);
            }
        },
    },
    watch: {
        entityVal(entity) {
            this.updateInput(entity);
        },
        inputVal(input) {
            this.filters.freeText = input;
        },
    },
    created() {
        this.entitiesDisplay = getName;
    },
};
</script>
