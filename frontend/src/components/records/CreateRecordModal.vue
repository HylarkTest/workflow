<template>
    <Modal
        class="o-create-record-modal"
        containerClass="p-4 w-450p"
        v-bind="$attrs"
    >
        <div
            :class="{ unclickable: processing }"
        >
            <div
                v-if="suggestedLength"
                class="mb-8"
            >
                <h3 class="header-2 text-center mb-4">
                    Create a record for...
                </h3>

                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="option in recordOptions"
                        :key="option.id"
                        type="button"
                        class="button-rounded relative"
                        :class="selectedOptionClass(option)"
                        @click="selectOption(option)"
                    >
                        {{ option.SYSTEM_NAME || option.name }}

                        <span
                            v-if="isSelectedOption(option)"
                            class="o-create-record-modal__circle circle-center"
                        >
                            <i
                                class="fa-solid fa-check"
                            >
                            </i>
                        </span>
                    </button>
                </div>
            </div>
            <div
                ref="modalParent"
            >
                <h3 class="header-2 text-center mb-4">
                    What type of record is this?
                </h3>

                <div>
                    <div
                        v-for="(space, key) in mappingsBySpace"
                        :key="key"
                        class="mb-4 last:mb-0"
                    >
                        <p class="block font-semibold mb-1 text-primary-700">
                            {{ space[0].space.name }}
                        </p>

                        <div
                            class="flex flex-wrap gap-2"
                        >
                            <button
                                v-for="mapping in space"
                                :key="mapping.id"
                                type="button"
                                class="button-rounded relative"
                                :class="selectedMappingClass(mapping)"
                                @click="selectMapping(mapping)"
                            >
                                {{ mapping.name }}

                                <span
                                    v-if="isSelectedMapping(mapping)"
                                    class="o-create-record-modal__circle circle-center"
                                >
                                    <i
                                        class="fa-solid fa-check"
                                    >
                                    </i>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal
            v-if="isModalOpen"
            containerClass="p-4 w-600p"
            :modalParent="$refs.modalParent"
            @closeModal="closeNewModal"
        >
            <EntityNew
                :mapping="selectedMapping"
                :page="null"
                :prepopulatedValues="selectedRecord"
                @closeModal="closeNewModal"
                @saved="associateNewNode"
            >
            </EntityNew>
        </Modal>

        <Modal
            v-if="isEmailCheckOpen"
            containerClass="p-4 w-450p"
            :modalParent="$refs.modalParent"
            @closeModal="closeEmailCheck"
        >
            <DirectOrRule
                :record="newRecord"
                :account="nodeToAssociate.account"
                @closeModal="closeEmailCheck"
                @setSpecificAssociation="addAssociation(newRecord)"
            >
            </DirectOrRule>
        </Modal>
    </Modal>
</template>

<script>

import DirectOrRule from '@/components/records/DirectOrRule.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import { associateItem } from '@/core/repositories/itemRepository.js';

import MAPPINGS from '@/graphql/mappings/queries/Mappings.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

const other = {
    id: 'OTHER',
    name: 'Something else',
    EMAIL: null,
};
export default {
    name: 'CreateRecordModal',
    components: {
        DirectOrRule,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        suggestedRecords: {
            type: [Array, null],
            default: null,
        },
        withFeatures: {
            type: [Array, null],
            default() {
                return [];
            },
        },
        spaceId: {
            type: String,
            default: '',
        },
        nodeToAssociate: {
            type: Object,
            required: true,
        },
        emailAssociationCheck: Boolean,
    },
    apollo: {
        mappings: {
            query: MAPPINGS,
            update: initializeConnections,
            variables() {
                if (this.spaceId) {
                    return { spaceId: this.spaceId };
                }
                return null;
            },
        },
    },
    data() {
        return {
            selectedRecord: other,
            selectedMapping: null,
            processing: false,
            isEmailCheckOpen: false,
            newRecord: null,
        };
    },
    computed: {
        suggestedLength() {
            return this.suggestedRecords?.length;
        },
        recordOptions() {
            const options = [
                other,
            ];
            return this.suggestedRecords?.concat(options);
        },
        mappingsLength() {
            return this.validMappings?.length;
        },
        mappingsArr() {
            return this.mappings?.mappings || [];
        },
        featuresArr() {
            return this.withFeatures.map((feature) => {
                return { val: feature };
            });
        },
        validMappings() {
            return this.mappingsArr.filter((mapping) => {
                return _.some(mapping.features, (feature) => {
                    return this.withFeatures.includes(feature.val);
                });
            });
        },
        mappingsBySpace() {
            return _.groupBy(this.validMappings, 'space.id');
        },
        hasEmailAssociationsCheck() {
            return this.withFeatures.length === 1 && this.withFeatures[0] === 'EMAILS';
        },
    },
    methods: {
        selectedOptionClass(option) {
            return this.isSelectedOption(option) ? 'button-primary no-pointer' : 'button-gray';
        },
        isSelectedOption(option) {
            return option.id === this.selectedRecord.id;
        },
        selectOption(option) {
            this.selectedRecord = option;
        },
        selectedMappingClass(mapping) {
            return this.isSelectedMapping(mapping) ? 'button-primary no-pointer' : 'button-gray';
        },
        isSelectedMapping(mapping) {
            return mapping.id === this.selectedMapping?.id;
        },
        selectMapping(mapping) {
            this.selectedMapping = mapping;
            this.openModal();
        },
        closeNewModal() {
            this.closeModal();
            this.selectedMapping = null;
        },
        associateNewNode(node) {
            this.closeNewModal();
            this.newRecord = node;
            if (this.hasEmailAssociationsCheck && this.recordHasEmails(node)) {
                this.$saveFeedback({ customMessageString: 'Your record was created' });
                this.openEmailCheck();
            } else {
                this.addAssociation(node);
            }
        },
        async addAssociation(node) {
            this.processing = true;
            try {
                await associateItem(this.nodeToAssociate, node);
                this.$saveFeedback({ customMessageString: 'Your record was created and associated to this email' });
                this.selectedRecord = other;
                this.closeEmailCheck();
                this.newRecord = null;
            } finally {
                this.processing = false;
            }
        },
        recordHasEmails(record) {
            const allFields = record.mapping.fields;
            const emailFields = allFields.filter((field) => {
                return field.type === 'EMAIL';
            });
            const emailValues = emailFields.some((field) => {
                return record.data[field.id];
            });
            return emailValues || null;
        },
        openEmailCheck() {
            this.isEmailCheckOpen = true;
        },
        closeEmailCheck() {
            this.newRecord = null;
            this.isEmailCheckOpen = false;
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-create-record-modal {
    &__circle {
        height: 20px;
        min-width: 20px;

        @apply
            absolute
            bg-cm-00
            border
            border-primary-600
            border-solid
            -right-1
            text-primary-600
            -top-1
            z-over
        ;
    }
}

</style>
