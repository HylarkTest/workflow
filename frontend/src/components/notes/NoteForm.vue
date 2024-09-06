<template>
    <FeatureFormBase
        v-model:form="form"
        v-model:formAssociations="form.associations"
        v-model:formMarkers="form.markers"
        v-model:formListId="form.notebookId"
        v-model:formAssigneeGroups="form.assigneeGroups"
        class="o-note-form"
        v-bind="baseProps"
        :changeListFunction="changeNotebook"
        @saveItem="saveNote(true)"
        @deleteItem="deleteItem"
    >
        <div class="mb-2">
            <label class="header-form">
                Title
            </label>
            <InputBox
                ref="nameInput"
                bgColor="gray"
                formField="name"
                placeholder="Add a title (optional)"
            >
            </InputBox>
        </div>

        <div class="mb-2">
            <label class="header-form">
                Content
            </label>

            <TipTapInput
                formField="tiptap"
                bgColor="gray"
                :error="form.errors().getFirst('tiptap')"
                :showCharsRemaining="true"
            >
            </TipTapInput>
        </div>
    </FeatureFormBase>
</template>

<script>
import TipTapInput from '@/tiptap/TipTapInput.vue';

import interactsWithNoteForm from '@/vue-mixins/notes/interactsWithNoteForm.js';
import interactsWithFeatureForms from '@/vue-mixins/features/interactsWithFeatureForms.js';

import {
    changeNotebook,
    createNoteFromObject,
    createNote,
    updateNote,
    deleteNote,
} from '@/core/repositories/noteRepository.js';

import NOTE from '@/graphql/notes/queries/Note.gql';

export default {
    name: 'NoteForm',
    components: {
        TipTapInput,
    },
    mixins: [
        interactsWithFeatureForms,
        interactsWithNoteForm,
    ],
    props: {
        note: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'closeModal',
    ],
    apollo: {
        fullNote: {
            query: NOTE,
            variables() {
                return { id: this.note.id };
            },
            skip() {
                return !this.note?.id;
            },
            update: ({ note }) => createNoteFromObject(note),
        },
    },
    data() {
        return {
            tiptapBgClass: ['bg-cm-100'],
            listObjKey: 'notebook',
            form: this.$apolloForm(() => {
                const data = {
                    name: this.note?.name || '',
                    tiptap: null,
                };

                if (this.isNew) {
                    data.assigneeGroups = [];
                    data.associations = this.defaultAssociations || [];
                    data.markers = [];
                    data.notebookId = this.notebook?.id;
                } else {
                    data.id = this.note.id;
                }

                return data;
            }),
        };
    },
    computed: {
        savedItem() {
            return this.fullNote;
        },
        hiddenSections() {
            return ['NAME', 'DESCRIPTION'];
        },
    },
    methods: {
        saveNote(hasCloseModal) {
            if (!this.form.name) {
                this.form.name = 'Untitled';
            }
            this.saveItem(hasCloseModal);
        },
    },
    watch: {
        fullNote: {
            immediate: true,
            handler(newVal, oldVal) {
                if (newVal && !oldVal) {
                    this.form.tiptap = newVal.tiptap;
                }
            },
        },
    },
    created() {
        this.changeNotebook = changeNotebook;
        this.createFunction = createNote;
        this.updateFunction = updateNote;
        this.deleteFunction = deleteNote;
    },
    mounted() {
        if (this.isNew) {
            this.$refs.nameInput?.select();
        }
    },
};
</script>

<style>
/* .o-note-form {
} */
</style>
