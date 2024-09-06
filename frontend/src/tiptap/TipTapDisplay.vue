<template>
    <div class="o-tip-tap-display">
        <TipTapEditor
            :content="content"
            :editorClasses="editorClasses"
            :isReadOnly="true"
            :bgColor="bgColor"
            @tiptapReady="$emit('tiptapReady', $event)"
        >
        </TipTapEditor>
    </div>
</template>

<script>
import TipTapEditor from '@/tiptap/TipTapEditor.vue';

export default {
    name: 'TipTapDisplay',
    components: {
        TipTapEditor,
    },
    props: {
        bgColor: {
            type: String,
            default: 'white',
            validator(val) {
                return ['white', 'gray'].includes(val);
            },
        },
        content: {
            type: [String, Object],
            default: () => {},
        },
        editorClasses: {
            type: Array,
            default: () => ['overflow-y-hidden'],
        },
    },
    emits: [
        'tiptapReady',
    ],
};
</script>

<style>
/* .o-tip-tap-display {
} */
</style>
