<template>
    <div class="o-note-quick-form">
        <FormWrapper
            class="flex items-start"
            :form="form"
            @submit="saveNote"
        >
            <div class="mr-4 flex-1 min-w-0">
                <InputBox
                    formField="name"
                    placeholder="Add a title to your note (optional)"
                >
                </InputBox>

                <TipTapInput
                    formField="tiptap"
                    class="mt-4"
                    :error="form.errors().getFirst('tiptap')"
                    :showCharsRemaining="true"
                >
                </TipTapInput>
            </div>

            <div>
                <button
                    v-t="'features.notes.add'"
                    class="button button-primary"
                    :class="{ unclickable: isSubmitOff }"
                    :disabled="isSubmitOff"
                    type="submit"
                >
                </button>

                <div
                    v-if="associationsLength"
                    class="mt-2"
                >
                    <div
                        v-for="association in form.associations"
                        :key="association.id"
                        class="h-12 w-12 min-w-12 flex flex-wrap"
                    >
                        <ConnectedRecord
                            class="h-full w-full text-lg"
                            :item="association"
                            :isMinimized="true"
                            imageSize="full"
                            @click.stop
                        >
                        </ConnectedRecord>
                    </div>
                </div>
            </div>

        </FormWrapper>
    </div>
</template>

<script>
import TipTapInput from '@/tiptap/TipTapInput.vue';

import interactsWithNoteForm from '@/vue-mixins/notes/interactsWithNoteForm.js';

import {
    createNote,
} from '@/core/repositories/noteRepository.js';

export default {
    name: 'NoteQuickForm',
    components: {
        TipTapInput,
    },
    mixins: [
        interactsWithNoteForm,
    ],
    props: {
        defaultAssociations: {
            type: [Array, null],
            default: null,
        },
    },
    data() {
        return {
            processing: false,
            form: this.$apolloForm(() => ({
                notebookId: this.notebook.id,
                tiptap: null,
                associations: this.defaultAssociations || [],
                name: '',
            }), { clear: true }),
        };
    },
    computed: {
        isSubmitOff() {
            return this.processing || !this.form.tiptap;
        },
        associationsLength() {
            return this.form.associations?.length;
        },
    },
    methods: {
        async saveNote() {
            this.processing = true;
            try {
                if (!this.form.name) {
                    this.form.name = 'Untitled';
                }
                await createNote(this.form);
                this.form.reset();
            } finally {
                this.processing = false;
            }
        },
    },
    watch: {
        'notebook.id': {
            handler() {
                this.form.notebookId = this.notebook.id;
            },
        },
    },
    created() {

    },
};
</script>

<style>
/* .o-note-quick-form {
} */
</style>
