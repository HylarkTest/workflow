<template>
    <ButtonEl
        class="o-note-item relative feature__item feature__item--style"
        :class="mainFeatureItemClasses"
        @click="selectNote"
    >
        <div
            class="o-note-item__buttons flex"
        >
            <button
                type="button"
                class="circle-center relative mr-1 h-[25px] w-[25px]"
                title="Preview note"
                @click.stop="openFullNote"
            >
                <i
                    class="fal fa-arrows-maximize z-over text-cm-00 pointer-events-none"
                >
                </i>
                <span
                    class="h-full w-full rounded-full opacity-70 bg-cm-300 absolute hover:opacity-100 transition-2eio"
                >
                </span>
            </button>

            <FavoriteButton
                class="relative"
                :isFavorite="note.isFavorite"
                @click.stop="toggleFavorite"
            >
            </FavoriteButton>
        </div>

        <div
            class="absolute top-5 -right-px bg-cm-00 h-7 w-7 centered rounded-l-full shadow-md"
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

        <DateLabel
            class="mb-3 text-primary-400 font-medium text-xxsxs"
            textColorClass="text-primary-400"
            iconColorClass="text-primary-300"
            :date="createdAt"
            :fullTime="true"
        >
        </DateLabel>

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

        <div class="flex mr-2">
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

            <div
                class="min-w-0 flex-1 flex flex-col gap-1 relative"
            >
                <p
                    class="font-semibold text-primary-700 mb-2 leading-tight"
                >
                    {{ name }}
                </p>

                <div
                    class="h-[120px] overflow-y-hidden"
                >
                    <TipTapDisplay
                        ref="noteContent"
                        :content="note.tiptap"
                        @tiptapReady="checkTiptapHeight"
                    >
                    </TipTapDisplay>
                </div>

                <div
                    v-if="hasMoreContent"
                    class="o-note-item__more-wrapper absolute -bottom-2 right-0 center"
                >
                    <ButtonEl
                        class="o-note-item__more relative"
                        type="button"
                        @click.stop="openFullNote"
                    >
                        <span
                            class="relative z-over px-2.5 py-1 font-semibold text-primary-600"
                        >
                            <i
                                class="fas fa-ellipsis text-2xl leading-[20px]"
                            >
                            </i>
                        </span>

                        <span
                            class="o-note-item__overlay transition-2eio"
                        >
                            &nbsp;
                        </span>
                    </ButtonEl>
                </div>
            </div>
        </div>

        <EditableMarkerSet
            v-if="markersLength"
            class="mt-4"
            :item="note"
            :tags="tags"
            :pipelines="pipelines"
            :statuses="statuses"
        >
        </EditableMarkerSet>

        <FeatureSource
            v-if="!notebook"
            class="mt-1"
            :featureItem="note"
            listKey="notebook"
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

        <NoteFullModal
            v-if="fullNoteOpen"
            :note="note"
            @closeModal="closeFullNote"
            @openEdit="closeAndEditNote"
        >
        </NoteFullModal>
    </ButtonEl>
</template>

<script>
import NoteFullModal from './NoteFullModal.vue';
import TipTapDisplay from '@/tiptap/TipTapDisplay.vue';

import interactsWithFeatureItem from '@/vue-mixins/features/interactsWithFeatureItem.js';
import interactsWithNoteItem from '@/vue-mixins/notes/interactsWithNoteItem.js';

import { deleteNote, duplicateNote, toggleFavorite } from '@/core/repositories/noteRepository.js';

export default {
    name: 'NoteItem',
    components: {
        NoteFullModal,
        TipTapDisplay,
    },
    mixins: [
        interactsWithNoteItem,
        interactsWithFeatureItem,
    ],
    props: {
    },
    emits: [
        'selectNote',
    ],
    data() {
        return {
            fullNoteOpen: false,
            heightOfContent: null,
        };
    },
    computed: {
        hasMoreContent() {
            return this.heightOfContent > 120;
        },
    },
    methods: {
        toggleFavorite() {
            toggleFavorite(this.note);
        },
        openFullNote() {
            this.fullNoteOpen = true;
        },
        closeFullNote() {
            this.fullNoteOpen = false;
        },
        closeAndEditNote() {
            this.closeFullNote();
            this.selectNote();
        },
        selectNote() {
            this.$emit('selectNote', this.note);
        },
        checkTiptapHeight() {
            this.$nextTick(() => {
                this.heightOfContent = this.$refs.noteContent.$el.clientHeight;
            });
        },
        duplicateItem(records) {
            return duplicateNote(this.note, records);
        },
    },
    created() {
        this.deleteFunction = deleteNote;
    },
};
</script>

<style scoped>

.o-note-item {
    @apply
        flex
        flex-col
        h-full
        p-2.5
        text-sm
    ;

    &__buttons {
        @apply
            absolute
            -right-2
            -top-2
        ;
    }

    &__more-wrapper {
        background-image: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,1));
        @apply
            w-full
        ;

    }

    &__more {
        @apply
            mt-[60px]
        ;

        &:hover {
            .o-note-item__overlay {
                @apply
                    bg-primary-100
                ;
            }
        }
    }

    &__overlay {
        @apply
            absolute
            bg-cm-00
            block
            opacity-90
            right-0
            rounded-full
            shadow-md
            top-0
            w-full
        ;
    }
}

</style>
