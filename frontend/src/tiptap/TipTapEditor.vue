<template>
    <div
        v-if="editor"
        class="o-tip-tap-editor"
        :class="bgClass"
    >
        <TipTapHeader
            v-if="isChangeable"
            :editor="editor"
        >
        </TipTapHeader>
        <EditorContent
            class="tiptap-editor"
            :editor="editor"
            @paste="handlePaste"
        >
        </EditorContent>
    </div>
</template>

<script>
import { Editor, EditorContent } from '@tiptap/vue-3';
import Extensions from '@/tiptap/extensions/index.js';

import TipTapHeader from '@/tiptap/headerComponents/TipTapHeader.vue';

const editModeClasses = ['py-3', 'px-4'];

export default {
    name: 'TipTapEditor',
    components: {
        TipTapHeader,
        EditorContent,
    },
    props: {
        content: {
            type: [Object, null],
            default: null,
        },
        bgColor: {
            type: String,
            default: 'white',
            validator(val) {
                return ['white', 'gray'].includes(val);
            },
        },
        editorClasses: {
            type: Array,
            default: () => ['min-h-[200px]', 'max-h-[500px]'],
        },
        isReadOnly: Boolean,
    },
    emits: [
        'update:content',
        'tiptapReady',
    ],
    data() {
        return {
            editor: null,
        };
    },
    computed: {
        bgClass() {
            return `o-tip-tap-editor__bg--${this.bgColor}`;
        },
        isChangeable() {
            return !this.isReadOnly;
        },
        editModeClasses() {
            return this.isChangeable ? editModeClasses : [];
        },
    },
    methods: {
        resetEditor() {
            this.editor.commands.clearContent(true);
        },
        handlePaste(e) {
            const files = e.clipboardData.files;
            const images = Array.from(files)?.filter((file) => /image/i.test(file.type));
            if (images.length) {
                e.preventDefault();
                const firstImg = images[0];
                const reader = new FileReader();
                reader.readAsDataURL(firstImg);
                reader.onloadend = async () => {
                    const src = reader.result;
                    this.editor.commands.setImage({ src });

                    this.editor.commands.insertContentAt(
                        this.editor.state.selection.$to.end() + 1,
                        { type: 'rootblock', content: [{ type: 'paragraph' }] }
                    );
                };
            }
        },
    },
    watch: {
        content(val) {
            if (!this.editor) {
                return;
            }
            if (!_.isEqual(val, this.editor.getJSON())) {
                this.editor.commands.setContent(val);
            }
        },
    },
    mounted() {
        this.editor = new Editor({
            editable: this.isChangeable,
            extensions: Extensions,
            editorProps: {
                attributes: {
                    class: [
                        'focus:outline-none',
                        'overflow-y-auto',
                        'rounded-lg',
                        ...this.editModeClasses,
                        ...this.editorClasses,
                    ].join(' '),
                },
            },
            content: this.content,
            onUpdate: ({ editor }) => this.$emit('update:content', editor.getJSON()),
        });
        this.$emit('tiptapReady', this.editor);
    },
    beforeUnmount() {
        this.editor.destroy();
    },
};
</script>

<style>
.o-tip-tap-editor {
    @apply
        h-full
        p-1
        rounded-lg
    ;

    &__buttons {
        @apply
            flex
            gap-[2px]
        ;
    }

    &__bg {
        &--gray {
            @apply
                bg-cm-100
            ;
        }

        &--white {
            @apply
                bg-cm-00
            ;
        }
    }
}

.tiptap-p {
    @apply
        text-smbase
    ;
}

.tiptap-codeBlock {
    @apply
        bg-cm-300
        p-1
    ;
}

.tiptap-blockquote {
    @apply
        border-cm-300
        border-l
        border-solid
        px-2
        py-1
    ;
}

.tiptap-link {
    @apply
        cursor-pointer
        text-azure-600
        underline
    ;
}

.tiptap-italic {
    @apply
        italic
    ;
}

.tiptap-highlight {
    @apply
        p-1
        rounded
    ;
}

.tiptap-img {
    display: block;
    height: auto;
    max-width: 100%;
    padding: 4px;

    &.ProseMirror-selectednode {
        padding: 0;
        @apply
            border-4
            border-primary-300
            border-solid
        ;
    }
}

.tiptap-h1 {
    @apply
        text-2xl
    ;
}
.tiptap-h2 {
    @apply
        text-xl
    ;
}
.tiptap-h3 {
    @apply
        text-lg
    ;
}
.tiptap-h4 {
    @apply
        text-base
    ;
}
.tiptap-h5 {
    @apply
        text-sm
    ;
}
.tiptap-h6 {
    @apply
        text-xssm
    ;
}
</style>
