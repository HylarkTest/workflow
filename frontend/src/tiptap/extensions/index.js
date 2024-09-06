import colorBaseExtension from '@tiptap/extension-color';
import starterKitBaseExtension from '@tiptap/starter-kit';
import underlineBaseExtension from '@tiptap/extension-underline';
import textStyleBaseExtension from '@tiptap/extension-text-style';
import superscriptBaseExtension from '@tiptap/extension-superscript';
import subscriptBaseExtension from '@tiptap/extension-subscript';

//nodes
import docNode from '@/tiptap/editorNodes/tiptapDoc.js';
import rootblockNode from '@/tiptap/editorNodes/tiptapRootblock.js';
import paragraphNode from '@/tiptap/editorNodes/tiptapParagraph.js';
import headingNode from '@/tiptap/editorNodes/tiptapHeading.js';
import codeBlockNode from '@/tiptap/editorNodes/tiptapCodeBlock.js';
import blockquoteNode from '@/tiptap/editorNodes/tiptapBlockquote.js';
import imageNode from '@/tiptap/editorNodes/tiptapImage.js';

import customCommandsExtension from '@/tiptap/editorExtensions/customCommandsExtension.js';
import keyboardShortcutsExtension from '@/tiptap/editorExtensions/keyboardShortcutsExtension.js';

// marks
import highlightExtension from '@/tiptap/editorExtensions/highlightExtension.js';
import italicExtension from '@/tiptap/editorExtensions/italicExtension.js';
import linkExtension from '@/tiptap/editorExtensions/linkExtension.js';

// attributes
import indentExtension from '@/tiptap/editorExtensions/indentExtension.js';
import alignmentExtension from '@/tiptap/editorExtensions/alignmentExtension.js';
import bulletListExtension from '@/tiptap/editorExtensions/bulletListExtension.js';

const baseExtensions = [
    starterKitBaseExtension.configure({
        // disable default extensions and import them with custom options
        document: false,
        paragraph: false,
        heading: false,
        codeBlock: false,
        blockquote: false,
        bulletList: false,
        italic: false,
    }),
    underlineBaseExtension,
    textStyleBaseExtension,
    colorBaseExtension,
    superscriptBaseExtension,
    subscriptBaseExtension,
];

// order matters! e.g. keyboardShortcuts uses commands created in customCommands
const customExtensions = [
    customCommandsExtension,
    keyboardShortcutsExtension,

    highlightExtension,
    italicExtension,
    linkExtension,

    indentExtension,
    alignmentExtension,
    bulletListExtension,
];

const nodes = [
    docNode,
    rootblockNode,
    paragraphNode,
    headingNode,
    codeBlockNode,
    blockquoteNode,
    imageNode,
];

export default [
    ...baseExtensions,
    ...customExtensions,
    ...nodes,
];
