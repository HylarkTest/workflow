<template>
    <ButtonEl
        class="o-pin-item feature__item feature__item--style"
        :class="mainFeatureItemClasses"
        @click="$emit('selectPin', pin)"
    >
        <div class="relative">
            <img
                class="w-full min-h-6 rounded-t-lg"
                :src="image"
                :title="name"
            />
            <FavoriteButton
                class="o-pin-item__favorite"
                :isFavorite="isFavorite"
                @click.stop="toggleFavorite"
            >
            </FavoriteButton>

            <div
                class="o-pin-item__lower flex"
            >
                <DownloadButton
                    class="mr-1"
                    :url="downloadUrl"
                >
                    <div
                        class="w-5 h-5 relative centered text-xs"
                    >
                        <div
                            class="o-pin-item__download transition-2eio"
                        >
                        </div>
                        <i
                            class="fal fa-download z-over text-cm-500 relative pointer-events-none"
                        >
                        </i>
                    </div>
                </DownloadButton>

                <ExpandedImageButton
                    :header="name"
                    :image="image"
                >
                </ExpandedImageButton>
            </div>
        </div>

        <div
            class="p-2.5 relative"
        >
            <div
                class="absolute top-2 -right-px bg-cm-00 h-7 w-7 centered rounded-l-full shadow-md"
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
            <div class="pr-4">
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

                <div class="flex">
                    <div
                        v-if="associationsLength"
                        class="h-10 w-10 min-w-10 mr-4 flex flex-wrap"
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

                    <div>
                        <p
                            class="font-medium text-sm"
                        >
                            {{ name }}
                        </p>
                        <p
                            v-if="description"
                            class="feature__item--description"
                        >
                            <i
                                class="fa-regular fa-memo-pad mr-1"
                            >
                            </i>

                            {{ trimmedDescription }}
                        </p>
                    </div>
                </div>
            </div>

            <EditableMarkerSet
                v-if="markersLength"
                class="mt-4"
                :item="pin"
                :tags="tags"
                :pipelines="pipelines"
                :statuses="statuses"
            >
            </EditableMarkerSet>

            <FeatureSource
                v-if="!pinboard"
                class="mt-1"
                :featureItem="pin"
                listKey="pinboard"
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
        </div>
    </ButtonEl>
</template>

<script>
import ExpandedImageButton from '@/components/buttons/ExpandedImageButton.vue';

import { deletePin, duplicatePin, toggleFavorite } from '@/core/repositories/pinRepository.js';

import interactsWithPinItem from '@/vue-mixins/pinboard/interactsWithPinItem.js';
import interactsWithFeatureItem from '@/vue-mixins/features/interactsWithFeatureItem.js';

export default {
    name: 'PinItem',
    components: {
        ExpandedImageButton,
    },
    mixins: [
        interactsWithPinItem,
        interactsWithFeatureItem,
    ],
    props: {
    },
    emits: [
        'selectPin',
    ],
    data() {
        return {
        };
    },
    computed: {
    },
    methods: {
        toggleFavorite() {
            toggleFavorite(this.pin);
        },
        duplicateItem(records) {
            return duplicatePin(this.pin, records);
        },
    },
    created() {
        this.deleteFunction = deletePin;
    },
};
</script>

<style scoped>

.o-pin-item {
    @apply
        relative
    ;

    &__favorite {
        @apply
            absolute
            right-1
            top-1
        ;
    }

    &__lower {
        @apply
            absolute
            bottom-1
            right-1
        ;
    }

    &__download {
        @apply
            absolute
            bg-cm-00
            h-full
            opacity-70
            right-0
            rounded-full
            top-0
            w-full
        ;

        &:hover {
            @apply
                opacity-100
            ;
        }
    }
}

</style>
