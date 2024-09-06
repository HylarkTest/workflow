<template>
    <ButtonEl
        class="o-link-item feature__item feature__item--style"
        :class="mainFeatureItemClasses"
        @click="$emit('selectLink', link)"
    >
        <div
            class="absolute top-4 -right-px bg-cm-00 h-7 w-7 centered rounded-l-full shadow-md"
        >
            <ExtrasButton
                :options="['DUPLICATE', 'DELETE']"
                :item="featureItem"
                contextItemType="FEATURE_ITEM"
                alignRight
                nudgeDownProp="0.375rem"
                nudgeRightProp="0.375rem"
                :duplicateItemMethod="duplicateItem"
                @click.stop
                @selectOption="selectOption"
            >
            </ExtrasButton>
        </div>

        <FavoriteButton
            class="o-link-item__favorite"
            :isFavorite="isFavorite"
            @click.stop="toggleFavorite"
        >
        </FavoriteButton>

        <div
            v-if="remainingAssociationsLength"
            class="flex flex-wrap gap-0.5 mb-2"
        >
            <div
                v-for="association in remainingAssociations"
                :key="association.id"
                class="w-6 h-6"
            >
                <ConnectedRecord
                    class="h-full w-full text-xssm"
                    :item="association"
                    :isMinimized="true"
                    imageSize="full"
                    @click.stop
                >
                </ConnectedRecord>
            </div>
        </div>

        <div class="flex flex-1">
            <div
                v-if="associationsLength"
                class="h-12 w-12 min-w-12 mr-4 flex flex-wrap"
            >
                <ConnectedRecord
                    class="h-full w-full text-lg"
                    :item="firstAssociation"
                    :isMinimized="true"
                    imageSize="full"
                    @click.stop
                >
                </ConnectedRecord>
            </div>

            <div class="min-w-0">
                <p class="font-semibold">

                    {{ name }}
                </p>

                <p
                    v-if="description"
                    class="feature__item--description mt-1"
                >
                    <i
                        class="fa-regular fa-memo-pad mr-1"
                    >
                    </i>

                    {{ trimmedDescription }}
                </p>

                <div class="flex items-baseline mt-1">
                    <!-- eslint-disable -->
                    <a
                        class="button--xs button-secondary--light shrink-0 mr-2"
                        rel="noreferrer noopener"
                        :href="url"
                        target="_blank"
                        @click.stop
                    >
                        <i
                            class="far fa-up-right-from-square"
                        >
                        </i>
                    </a>
                    <!-- eslint-enable -->
                    <a
                        class="underline text-cm-400 hover:text-secondary-500 text-xxsxs break-all"
                        rel="noreferrer noopener"
                        :href="url"
                        target="_blank"
                        @click.stop
                    >
                        {{ url }}
                    </a>
                </div>
            </div>
        </div>

        <EditableMarkerSet
            v-if="markersLength"
            class="mt-4"
            :item="link"
            :tags="tags"
            :pipelines="pipelines"
            :statuses="statuses"
        >
        </EditableMarkerSet>

        <FeatureSource
            v-if="!linkList"
            class="mt-1"
            :featureItem="link"
            listKey="linkList"
        >
        </FeatureSource>

        <div
            v-if="showAssignees"
            class="mt-2 flex justify-end"
        >
            <AssigneesPicker
                v-model:assigneeGroups="assigneeGroups"
                bgColor="white"
            >
            </AssigneesPicker>
        </div>
    </ButtonEl>
</template>

<script>

import interactsWithFeatureItem from '@/vue-mixins/features/interactsWithFeatureItem.js';
import interactsWithLinkItem from '@/vue-mixins/links/interactsWithLinkItem.js';

import { deleteLink, duplicateLink, toggleFavorite } from '@/core/repositories/linkRepository.js';

export default {
    name: 'LinkItem',
    components: {
    },
    mixins: [
        interactsWithLinkItem,
        interactsWithFeatureItem,
    ],
    props: {
    },
    emits: [
        'selectLink',
    ],
    data() {
        return {
        };
    },
    computed: {
    },
    methods: {
        toggleFavorite() {
            toggleFavorite(this.link);
        },
        duplicateItem(records) {
            return duplicateLink(this.link, records);
        },
    },
    created() {
        this.deleteFunction = deleteLink;
    },
};
</script>

<style scoped>

.o-link-item {
    @apply
        flex
        flex-col
        p-3
        relative
    ;

    &__favorite {
        @apply
            absolute
            -right-2
            -top-2.5
        ;
    }

}

</style>
