<template>
    <div class="o-entity-relationships">
        <div
            v-if="!isLoading"
            class="bg-cm-100 p-4 rounded-lg mb-4"
        >
            <div
                class="text-lg font-semibold mb-1"
            >
                <h3 v-if="singular && !relationship">
                    Relate one "{{ toInfo.singularName }}" record to
                    <span class="font-bold text-primary-600">{{ item.name }}</span>
                </h3>
                <h3 v-if="singular && relationship">
                    Replace the relationship to "{{ toInfo.singularName }}"
                </h3>
                <!-- <h3 v-else-if="relationship">
                    Set relationships to many "{{ toInfo.name }}" records
                </h3> -->
                <h3 v-else-if="relationship">
                    Relate "{{ toInfo.name }}" records to
                    <span class="font-bold text-primary-600">{{ item.name }}</span>
                </h3>
            </div>
            <p class="text-sm">
                Add a relationship by linking to an existing record or
                creating a new "{{ toInfo.singularName }}" record.
            </p>

            <div
                class="mt-4 flex items-center"
                :class="{ unclickable: processing }"
            >
                <div class="max-w-md">
                    <EntitiesPicker
                        :entityVal="null"
                        :mappingId="toInfo.id"
                        @update:modelValue="addAssociation"
                    >
                    </EntitiesPicker>
                </div>
                <span class="mx-3 text-sm font-semibold">
                    or
                </span>

                <button
                    class="button--sm button-primary"
                    type="button"
                    @click="createNewRecord"
                >
                    Add new record
                </button>

                <button
                    v-if="hasOneToOneRelationship"
                    class="button--sm button-gray ml-auto"
                    type="button"
                    @click="removeAssociation(relationship)"
                >
                    Remove current record
                </button>
            </div>
        </div>

        <div v-if="relationship && !isLoading">
            <div v-if="singular">
                <EntityBrief
                    :item="relationship"
                    :mapping="relatedMapping"
                    :showClear="true"
                >
                </EntityBrief>
            </div>

            <div v-else>
                <LoadMore
                    :hasNext="hasMore"
                    :cursor="pageInfo?.endCursor"
                    class="flex flex-wrap -m-1"
                    @nextPage="showMore"
                >
                    <div
                        v-for="record in relationship"
                        :key="record.id"
                        class="m-1 max-w-full"
                    >
                        <ConnectedRecord
                            :item="record"
                            :showClear="true"
                            :deactivated="record.id === processingId"
                            @removeItem="removeAssociation(record)"
                        >
                        </ConnectedRecord>
                    </div>
                </LoadMore>
            </div>
        </div>

        <Modal
            v-if="isModalOpen"
            containerClass="p-4 w-600p"
            @closeModal="closeModal"
        >
            <EntityNew
                :mapping="toInfo.id"
                :page="null"
                @closeModal="closeModal"
                @saved="addAssociation"
            >
            </EntityNew>
        </Modal>
    </div>
</template>

<script>

import EntitiesPicker from '@/components/pickers/EntitiesPicker.vue';
import LoadMore from '@/components/data/LoadMore.vue';
import EntityBrief from '@/components/fullViews/EntityBrief.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import { addRelationship, removeRelationship } from '@/core/repositories/itemRepository.js';
import { simpleMappingRequest } from '@/http/apollo/buildMappingRequests.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';

import MAPPING from '@/graphql/mappings/queries/Mapping.gql';

export default {
    name: 'EntityRelationships',
    components: {
        EntitiesPicker,
        LoadMore,
        EntityBrief,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
        page: {
            type: Object,
            required: true,
        },
        currentTab: {
            type: Object,
            required: true,
        },
    },
    apollo: {
        relationship: {
            query() {
                return simpleMappingRequest(this.relatedMapping, 'MANY', true);
            },
            variables() {
                return {
                    forRelation: {
                        relationId: this.fullRelationship.id,
                        itemId: this.item.id,
                    },
                };
            },
            skip() {
                return !this.relatedMapping;
            },
            update(data) {
                const results = _.get(data, ['items', this.relatedMapping.apiName]);
                if (this.singular) {
                    return _.first(results.edges)?.node;
                }
                return initializeConnections(results);
            },
        },
        relatedMapping: {
            query: MAPPING,
            variables() {
                return {
                    id: this.fullRelationship.to.id,
                };
            },
            update(data) {
                return data.mapping;
            },
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
            processing: false,
            processingId: null,
        };
    },
    computed: {
        isLoading() {
            return this.$apollo.loading && !this.relationship;
        },
        fullRelationship() {
            return _.find(this.relationships, { id: this.currentTab.value });
        },
        relationships() {
            return this.mapping.relationships;
        },
        toInfo() {
            return this.fullRelationship?.to;
        },
        type() {
            return this.fullRelationship?.type;
        },
        thisNumber() {
            return this.type.split('_')[2];
        },
        singular() {
            return this.thisNumber === 'ONE';
        },
        pageInfo() {
            const connection = `__${_.upperFirst(this.toInfo.apiSingularName)}ItemConnection`;
            return this.relationship[connection]?.pageInfo;
        },
        hasMore() {
            return this.pageInfo?.hasNextPage;
        },
        hasOneToOneRelationship() {
            return this.relationship && this.singular && !this.isLoading;
        },
    },
    methods: {
        async addAssociation(foreign) {
            this.processing = true;
            try {
                await addRelationship(this.item, foreign, this.mapping, this.fullRelationship);
            } finally {
                this.processing = false;
            }
        },
        async removeAssociation(foreign) {
            this.processingId = foreign.id;
            try {
                await removeRelationship(this.item, foreign, this.mapping, this.fullRelationship);
            } finally {
                this.processingId = null;
            }
        },
        showMore() {
            const variables = {
                after: this.pageInfo.endCursor,
            };
            this.$apollo.queries.relationship.fetchMore({ variables });
        },
        createNewRecord() {
            this.openModal();
        },
    },
    created() {
    },
};
</script>

<style scoped>

/*.o-entity-relationships {

} */

</style>
