import { Extension } from '@tiptap/core';

import {
    getFromRootblock,
} from '@/core/helpers/tiptapNodeHelpers.js';

export default Extension.create({
    name: 'customCommands',

    addCommands() {
        return {
            selectStartOfLine: () => (instance) => {
                const { tr, commands } = instance;
                const { $from } = tr.selection;
                return commands.setTextSelection($from.start());
            },
            selectEndOfLine: () => (instance) => {
                const { tr, commands } = instance;
                const { $to } = tr.selection;
                return commands.setTextSelection($to.end());
            },
            isEmptySelection: () => (instance) => {
                const { tr } = instance;
                const { selection, doc } = tr;
                const { $from, $to } = selection;
                return !doc.slice($from.start(), $to.end()).content.size;
            },
            inheritRootblockAttrs: () => (instance) => {
                const { tr, chain } = instance;
                const { doc, selection } = tr;
                const { $from } = selection;

                // When pressing the enter key within a bullet list we expect certain behaviors.
                // This function is called when the enter key is pressed on an empty bullet point.
                // We "search" backwards through the document to find a previous rootblock with a bullet,
                // and then set the selected rootblock's attributes to match.
                const compareHierarchyToPrevious = (comparatorIndent, $pos) => {
                    const previousNode = doc.childBefore($pos.before(1)).node; // previous rootblock
                    if (!previousNode || !previousNode.attrs.hasBullet) {
                        // current selection is the first rootblock, or a "gap" in the bullet chain is found
                        return { indent: 0, hasBullet: false };
                    }

                    const { indent, hasBullet } = previousNode.attrs;

                    if ((comparatorIndent > indent)) {
                        return { indent, hasBullet };
                    }

                    const $previousPos = doc.resolve($pos.before() - previousNode.nodeSize + 1);
                    return compareHierarchyToPrevious(comparatorIndent, $previousPos);
                };

                const currentAttrs = $from.node(1).attrs;
                const { indent, hasBullet } = compareHierarchyToPrevious(currentAttrs.indent, $from);

                if (!hasBullet && currentAttrs.hasBullet) {
                    chain()
                        .toggleBullet()
                        .setIndent(indent)
                        .run();

                    return true;
                }

                chain()
                    .setIndent(indent)
                    .run();

                return true;
            },
            insertRootblock: () => (instance) => {
                const { tr, chain, commands } = instance;
                const { selection, doc } = tr;
                const { $from, $to } = selection;

                const blockNode = { type: 'paragraph' };
                const attrs = getFromRootblock(instance).attrs;

                if (commands.isEmptySelection() && attrs.hasBullet) {
                    commands.inheritRootblockAttrs();
                    return true;
                }

                if ($to.pos !== $to.end()) {
                    // Save content between cursor and end of Node
                    blockNode.content = doc.slice($to.pos, $to.end()).toJSON().content;
                }

                const newRootblock = {
                    type: 'rootblock',
                    content: [blockNode],
                    attrs,
                };

                chain()
                    .deleteSelection() // Clear any selected text
                    .setTextSelection({ from: $from.pos, to: $to.end() })
                    .deleteSelection() // Clear selected content (it has already been added in the new blockNode)
                    .insertContentAt($from.pos, newRootblock)
                    .selectStartOfLine()
                    .run();

                return true;
            },
        };
    },
});
