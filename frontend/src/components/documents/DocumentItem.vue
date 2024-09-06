<template>
    <ButtonEl
        class="o-document-item feature__item feature__item--style"
        :class="mainFeatureItemClasses"
        @click="$emit('selectDocument', document)"
    >
        <div class="flex items-center">
            <div class="h-8 w-8 bg-primary-200 text-primary-600 circle-center shadow-lg mr-4">
                <i
                    class="far text-base"
                    :class="icon"
                >
                </i>
            </div>

            <div class="flex-1 flex">
                <div class="font-semibold">
                    {{ name }}
                </div>
                <div class="font-light text-gray-400 ml-4">
                    {{ formattedSize }}
                </div>
            </div>

            <div class="flex items-center -mr-4">
                <!-- eslint-disable-next-line -->
                <DownloadButton
                    :url="url"
                >
                    <IconHover
                        class="c-icon-hover"
                        icon="far fa-download"
                        iconColor="text-primary-600"
                    >
                    </IconHover>
                </DownloadButton>

                <div class="ml-2">
                    <FavoriteButton
                        class="relative"
                        :isFavorite="document.isFavorite"
                        @click.stop="toggleFavorite"
                    >
                    </FavoriteButton>
                </div>

                <div
                    class="ml-2 bg-cm-00 -mr-px h-7 w-7 centered rounded-l-full shadow-md"
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
            </div>
        </div>
        <div
            v-if="markersLength || associationsLength"
            class="flex flex-wrap mt-2"
            :class="!markersLength ? 'justify-end' : 'justify-between'"
        >
            <EditableMarkerSet
                v-if="markersLength"
                :item="document"
                :tags="tags"
                :pipelines="pipelines"
                :statuses="statuses"
            >
            </EditableMarkerSet>

            <div
                v-if="associationsLength"
                class="flex flex-wrap gap-0.5"
            >
                <div
                    v-for="association in associations"
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
        </div>
        <div
            v-if="!drive || showAssignees"
            class="flex flex-wrap justify-end mt-2 items-center"
        >
            <FeatureSource
                v-if="!drive"
                :featureItem="document"
                listKey="drive"
            >
            </FeatureSource>

            <AssigneesPicker
                v-if="showAssignees"
                v-model:assigneeGroups="assigneeGroups"
                class="ml-2"
                bgColor="white"
            >
            </AssigneesPicker>
        </div>
    </ButtonEl>
</template>

<script>

import IconHover from '@/components/buttons/IconHover.vue';

import { deleteDocument, duplicateDocument, toggleFavorite } from '@/core/repositories/documentRepository.js';

import interactsWithFeatureItem from '@/vue-mixins/features/interactsWithFeatureItem.js';
import interactsWithDocumentItem from '@/vue-mixins/documents/interactsWithDocumentItem.js';

export default {
    name: 'DocumentItem',
    components: {
        // DateStackedIcons,
        IconHover,
    },
    mixins: [
        interactsWithFeatureItem,
        interactsWithDocumentItem,
    ],
    props: {
    },
    emits: [
        'selectDocument',
    ],
    data() {
        return {
        };
    },
    computed: {
    },
    methods: {
        toggleFavorite() {
            toggleFavorite(this.document);
        },
        duplicateItem(records) {
            return duplicateDocument(this.document, records);
        },
    },
    created() {
        this.deleteFunction = deleteDocument;
    },
};
</script>

<style scoped>

.o-document-item {
    @apply
        px-4
        py-2
        relative
        text-sm
    ;
}

</style>
