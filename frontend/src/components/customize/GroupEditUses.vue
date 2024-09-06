<template>
    <div
        class="c-group-edit-uses"
    >
        <LoaderFetch
            v-if="$isLoadingFirstTime"
            :isFull="true"
            class="py-10"
            :sphereSize="50"
        >
        </LoaderFetch>

        <div v-else>
            <SpacesList
                :spaces="spacesFormatted"
            >
                <template #space="{ space }">
                    <div class="flex-1">
                        <h5
                            v-t="'labels.blueprints'"
                            class="text-sm mb-2 text-primary-800 font-semibold"
                        >
                        </h5>
                        <div
                            v-if="space.mappings?.length"
                        >
                            <LinkCheckbox
                                v-for="option in space.mappings"
                                :key="option.id"
                                :modelValue="usedByMappings"
                                :val="option.id"
                                :disabled="processing"
                                :predicate="predicate"
                                class="my-1"
                                :link="option"
                                @update:modelValue="updateUsedByMappings"
                            >
                            </LinkCheckbox>
                        </div>
                        <p
                            v-else
                            v-t="'customizations.blueprint.noneOnSpace'"
                            class="text-sm text-cm-500"
                        >
                        </p>
                    </div>

                    <div class="flex-1">
                        <h5
                            v-t="'labels.features'"
                            class="text-sm mb-2 text-primary-800 font-semibold"
                        >
                        </h5>
                        <div>
                            <LinkCheckbox
                                v-for="option in featureTypes"
                                :key="option.val"
                                :modelValue="usedByFeatures.find(({ spaceId }) => spaceId === space.id)?.features"
                                :val="option.val"
                                :disabled="processing"
                                textPathStart="labels"
                                :predicate="predicate"
                                class="my-1"
                                :link="option"
                                @update:modelValue="updateGroupForSpace(space, $event)"
                            >
                            </LinkCheckbox>
                        </div>
                    </div>
                </template>
            </SpacesList>
        </div>
    </div>
</template>

<script>

import LinkCheckbox from '@/components/assets/LinkCheckbox.vue';
import SpacesList from '@/components/customize/SpacesList.vue';

import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';

import { featureTypes } from '@/core/display/typenamesList.js';
import MAPPINGS from '@/graphql/mappings/queries/Mappings.gql';
import SPACES from '@/graphql/spaces/queries/Spaces.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import { updateMarkerGroup } from '@/core/repositories/markerRepository.js';
import { arrReplaceOrPushId } from '@/core/utils.js';
import { reportValidationError } from '@/core/uiGenerators/userFeedbackGenerators.js';

export default {
    name: 'GroupEditUses',
    components: {
        LinkCheckbox,
        SpacesList,
    },
    mixins: [
        interactsWithApolloQueries,
    ],
    props: {
        group: {
            type: Object,
            required: true,
        },
        groupType: {
            type: String,
            required: true,
        },
    },
    apollo: {
        mappings: {
            query: MAPPINGS,
        },
        spaces: {
            query: SPACES,
            update: (data) => initializeConnections(data).spaces,
        },
    },
    data() {
        return {
            usedByMappings: _.map(this.group.usedByMappings, 'id'),
            usedByFeatures: _.map(this.group.usedByFeatures || [], ({ space: { id: spaceId }, features }) => ({
                spaceId,
                features,
            })),
            processing: false,
            predicate: (val) => (_.isString(val) ? val : (val.id || val.val)),
        };
    },
    computed: {
        featureTypes() {
            return _(featureTypes).filter((feature) => {
                return !['EMAILS', 'TIMEKEEPER'].includes(feature.val);
            }).value();
        },
        edges() {
            return this.mappings?.edges || [];
        },
        possibleMappings() {
            return this.edges.map((item) => {
                const mapping = item.node;
                const pages = mapping.pages;
                const pagesLength = pages.length;
                const symbol = pagesLength === 1 ? pages[0].symbol : 'fa-compass-drafting';
                const sections = pagesLength > 1 ? pages : null;
                return {
                    id: mapping.id,
                    name: mapping.name,
                    symbol,
                    sublinks: sections,
                    space: mapping.space,
                };
            });
        },
        spacesFormatted() {
            return this.spaces.map((space) => {
                const mappings = this.possibleMappings.filter((mapping) => {
                    return mapping.space.id === space.id;
                });
                return {
                    ...space,
                    mappings,
                };
            });
        },
    },
    methods: {
        async saveGroup(data) {
            try {
                this.processing = true;
                await updateMarkerGroup(this.$apolloForm({
                    id: this.group.id,
                    ...data,
                }));
                this.$debouncedSaveFeedback();
            } catch (error) {
                reportValidationError(error, ['input.usedByMappings', 'input.usedByFeatures']);
            } finally {
                this.processing = false;
            }
        },
        async updateUsedByMappings(val) {
            const oldVal = this.usedByMappings;
            this.usedByMappings = val;
            try {
                await this.saveGroup({ usedByMappings: val });
            } catch (e) {
                this.usedByMappings = oldVal;
                throw e;
            }
        },
        async updateGroupForSpace(space, features) {
            const val = arrReplaceOrPushId(
                this.usedByFeatures,
                space.id,
                { spaceId: space.id, features },
                'spaceId'
            );
            const oldVal = this.usedByFeatures;
            this.usedByFeatures = val;
            try {
                await this.saveGroup({ usedByFeatures: val });
            } catch (e) {
                this.usedByFeatures = oldVal;
                throw e;
            }
        },
    },
    created() {
    },
};
</script>

<style scoped>

.c-group-edit-uses {
    &__space {
        @apply
            border-b
            border-cm-200
            border-solid
            mb-2
            pb-2
        ;

        &:last-child {
            @apply
                border-none
                mb-0
                pb-0
            ;
        }
    }
}

</style>
