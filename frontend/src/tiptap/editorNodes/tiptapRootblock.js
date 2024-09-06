import { mergeAttributes, Node } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';

import {
    nodeInfo,
} from '@/core/helpers/tiptapNodeHelpers.js';

import RootBlockComponent from '@/tiptap/editorComponents/RootblockComponent.vue';

export default Node.create({
    ...nodeInfo.rootblock,
    // draggable: true,

    addOptions() {
        return {
            alignment: ['left', 'center', 'right'],
            indent: [0, 1, 2, 3, 4, 5, 6],
            hasBullet: [false, true],
            HTMLAttributes: {},
        };
    },

    addAttributes() {
        return {
            alignment: {
                default: 'left',
            },
            indent: {
                default: 0,
            },
            hasBullet: {
                default: false,
            },
        };
    },

    // Since this is a custom Node, we don't need to parse HTML from other sources
    // parseHTML() {
    // },

    renderHTML({ HTMLAttributes }) {
        return [
            'div',
            mergeAttributes(HTMLAttributes),
            0,
        ];
    },

    addNodeView() {
        return VueNodeViewRenderer(RootBlockComponent);
    },
});
