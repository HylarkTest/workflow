<template>
    <ButtonEl
        class="o-finder-result"
        @click="goPlaces"
    >
        <div
            class="mr-6 w-12 h-12 rounded-lg"
        >
            <IconsComposition
                v-if="showPageIcon"
                sizeClass="w-12 h-12 text-lg"
                :iconsComposition="icons"
            >
            </IconsComposition>
            <ConnectedRecord
                v-else
                :item="fullObj"
                imageSize="lg"
                isMinimized
                stopModalClicks
            >
            </ConnectedRecord>
        </div>

        <div class="flex-1 mr-5 min-w-min grow">
            <div class="flex">
                <div
                    v-for="(nameObj, index) in names"
                    :key="index"
                    class="max-w-full break-words"
                >
                    <p
                        v-if="nameObj.highlight"
                        v-dompurify-html="nameObj.name"
                        class="font-semibold"
                    >
                    </p>

                    <p
                        v-else
                        class="font-semibold"
                    >
                        {{ nameObj.name }}
                    </p>

                    <p
                        v-if="index !== (namesLength - 1)"
                        class="mx-1 text-gray-400"
                    >
                        /
                    </p>
                </div>
            </div>

            <div v-if="showSecondaryHighlightsBelow">
                <div
                    v-for="(highlight, index) in secondaryHighlights"
                    :key="index"
                    class="flex items-baseline"
                >
                    <div class="mr-1 uppercase text-gray-400 font-semibold text-xxs shrink-0">
                        {{ highlight.name }}:
                    </div>

                    <p
                        v-dompurify-html="highlight.value"
                        class="text-sm min-w-0"
                    >
                    </p>
                </div>
            </div>

            <div v-if="hasAdditionalFields">
                <component
                    :is="additionalComponent"
                    :highlightedFields="secondaryFields"
                    :node="node"
                >
                </component>
            </div>

            <div v-if="hasMarkerGroups">
                <FinderResultMarkers
                    :markerGroups="markerGroups"
                    :item="fullObj"
                >
                </FinderResultMarkers>
            </div>
        </div>

        <div class="o-finder-result__extras">
            <div class="flex flex-col items-end">

                <SpaceNameLabel
                    class="mb-1"
                    size="sm"
                    :spaceName="space.name"
                >
                </SpaceNameLabel>

                <div
                    class="o-finder-result__tag ml-1 mb-1"
                    :class="mainTag.colorClasses"
                    :title="mainTag.title"
                >
                    <i
                        v-if="mainTag.icon"
                        class="fal mr-1"
                        :class="mainTag.icon"
                    >
                    </i>
                    {{ mainTag.label }}
                </div>

                <div
                    v-if="isEntity"
                    class="flex flex-wrap justify-end gap-1"
                >
                    <div
                        v-for="(tag, index) in tags"
                        :key="index"
                        class="o-finder-result__tag"
                        :class="tag.colorClasses"
                        :title="tag.title"
                    >
                        <i
                            v-if="tag.icon"
                            class="fal mr-1"
                            :class="tag.icon"
                        >
                        </i>
                        {{ tag.label }}
                    </div>
                </div>

                <DateLabel
                    v-if="createdAt"
                    :date="createdAt"
                    class="mt-1"
                >
                </DateLabel>

                <AssigneesPicker
                    v-if="showAssignees && isAssignable"
                    v-model:assigneeGroups="assigneeGroups"
                    class="mt-1"
                    displaySize="xs"
                >
                </AssigneesPicker>
            </div>
        </div>
    </ButtonEl>
</template>

<script>

import FinderResultTodo from './FinderResultTodo.vue';
import FinderResultEntities from './FinderResultEntities.vue';
import FinderResultMarkers from './FinderResultMarkers.vue';
import IconsComposition from '@/components/assets/IconsComposition.vue';
import SpaceNameLabel from '@/components/display/SpaceNameLabel.vue';

import sortsOutTypes from '@/vue-mixins/sortsOutTypes.js';
import interactsWithAssigneesPicker from '@/vue-mixins/features/interactsWithAssigneesPicker.js';

const additionalComponents = {
    todo: 'FinderResultTodo',
    entitiesPage: 'FinderResultEntities',
};

export default {
    name: 'FinderResult',
    components: {
        FinderResultTodo,
        FinderResultEntities,
        FinderResultMarkers,
        SpaceNameLabel,
        IconsComposition,
    },
    mixins: [
        sortsOutTypes,
        interactsWithAssigneesPicker,
    ],
    props: {
        result: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'closeModal',
    ],
    data() {
        return {

        };
    },
    computed: {
        node() {
            return this.result.node;
        },
        id() {
            return this.node.id;
        },

        // For sortsOutTypes mixin
        fullObj() {
            return this.node;
        },

        // For interactsWithAssigneesPicker mixin
        assigneeGroupsObject() {
            return this.node;
        },

        // Result names
        filteredNames() {
            let names = [];
            if (this.objName) {
                names = [{
                    value: this.objName,
                    fieldId: 'name',
                }];
            } else {
                names = this.fullObj.names;
            }
            return names?.filter((name) => {
                return name.value;
            });
        },
        names() {
            return this.filteredNames?.map((name) => {
                const highlighted = _.find(this.highlights, { path: name.fieldId });
                if (highlighted) {
                    return {
                        highlight: true,
                        name: highlighted.highlight,
                    };
                }
                return {
                    name: name.value,
                };
            });
        },
        namesLength() {
            return this.names.length;
        },

        // Space
        space() {
            if (this.isEntity) {
                return this.fullObj.mapping.space;
            }
            if (this.isFeatureItem) {
                return this.fullObj[this.resultData.listName].space;
            }
            return this.fullObj.space;
        },

        // Fields and highlights
        highlights() {
            return this.result.highlights;
        },
        secondaryFields() {
            if (this.mapping) {
                return this.mapping.fields?.filter((field) => {
                    return field.type !== 'SYSTEM_NAME';
                });
            }
            if (this.highlights?.length) {
                return this.highlights.filter((highlight) => {
                    return highlight.path !== 'name';
                });
            }
            return [];
        },
        secondaryHighlights() {
            if (this.mapping) {
                return _(this.highlights).map((highlight) => {
                    const field = _.find(this.secondaryFields, { id: highlight.path });
                    if (field) {
                        return {
                            ...field,
                            value: highlight.highlight,
                        };
                    }
                    return null;
                }).compact().value();
            }
            return this.secondaryFields.map((highlight) => {
                return {
                    id: highlight.path,
                    name: this.$t(`labels.${highlight.path}`),
                    value: highlight.highlight,
                };
            });
        },
        showSecondaryHighlightsBelow() {
            return this.mapping;
        },

        // Markers
        markerGroups() {
            return this.fullObj.markerGroups;
        },
        hasMarkerGroups() {
            return !!this.markerGroups?.length;
        },

        // General info
        createdAt() {
            return this.node.createdAt;
        },
        firstImageUrl() {
            return _.get(this.node, 'images[0].value.url');
        },
        showPageIcon() {
            // IconsComposition.vue is used for any result that is not an entity.
            // Entity displays prioritize (1) showing an image,
            // then (2) their first page icon if no image exists, then (3) their initials if the entity is pageless.
            // Entities displaying their first page icon use IconsComposition.vue
            return !this.isEntity || (!this.firstImageUrl && this.hasPages);
        },
        featureListId() {
            if (this.isFeatureItem) {
                const listType = this.resultData.listName;
                return this.fullObj[listType].id;
            }
            if (this.isFeatureList) {
                return this.id;
            }
            return null;
        },

        // Results and typenames
        isTodo() {
            return this.typename === 'Todo';
        },

        hasAdditionalFields() {
            return this.isTodo
                || this.isEntity;
        },

        additionalComponent() {
            if (this.hasAdditionalFields) {
                return additionalComponents[_.camelCase(this.typename)];
            }
            return null;
        },
    },
    methods: {
        goPlaces() {
            if (this.isFeatureItem || this.isFeatureList) {
                this.$router.push({
                    name: _.camelCase(this.resultData.featurePageType),
                    params: { listId: this.featureListId },
                });
            } else if (this.isEntityPage) {
                this.$router.push({
                    name: 'page',
                    params: { pageId: this.id },
                });
            } else if (this.isPagelessEntity) {
                this.$router.push({
                    name: 'recordPage',
                    params: { itemId: this.id },
                });
            } else if (this.isEntity || this.isUserData) {
                this.$router.push({
                    name: 'entityPage',
                    params: { itemId: this.id, pageId: this.firstPage.id },
                });
            } else if (this.isFeaturePage) {
                this.$router.push({
                    name: 'feature',
                    params: { pageId: this.id },
                });
            }
            this.$emit('closeModal');
        },
    },
    created() {
    },
};
</script>

<style scoped>

.o-finder-result {
    @apply
        flex
        p-2
        rounded-lg
        text-smbase
    ;

    &__extras {
        flex-shrink: 2;

        @apply
            flex
        ;
    }

    &__tag {
        padding: 1px 8px;

        @apply
            font-semibold
            rounded-full
            text-xxs
        ;
    }
}

</style>

<style>

.o-finder-result {
    em {
        @apply
            bg-cm-200
            rounded
        ;
    }

    &__description {
        @apply
            text-cm-500
            text-sm
        ;
    }
}

</style>
