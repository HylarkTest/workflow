import { Extension } from '@tiptap/core';

const getNode = (editor, depth) => editor.state.tr.selection.$from.node(...(depth ? [depth] : []));

export default Extension.create({
    name: 'keyboardShortcuts',

    addKeyboardShortcuts() {
        return {
            Enter: ({ editor }) => {
                if (getNode(editor).type.name === 'codeBlock') {
                    return editor.commands.newlineInCode();
                }
                if (getNode(editor).content.content[0]?.type.name === 'image') {
                    return editor.commands.deleteSelection();
                }
                return editor.commands.insertRootblock();
            },
            Backspace: ({ editor }) => {
                const attrs = getNode(editor, 1)?.attrs;

                if (editor.commands.isEmptySelection() && attrs?.indent) {
                    editor.commands.decrementIndent();
                    return true;
                }

                if (editor.commands.isEmptySelection() && attrs?.hasBullet) {
                    editor.commands.toggleBullet();
                    return true;
                }

                return false;
            },
        };
    },
});
