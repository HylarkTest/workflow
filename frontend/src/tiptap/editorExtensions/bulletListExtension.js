import { Extension } from '@tiptap/core';

import {
    selectionContainsOtherNodes,
} from '@/core/helpers/tiptapNodeHelpers.js';

export default Extension.create({
    name: 'bulletList',

    addOptions() {
        return {
            hasBullet: [false, true],
            default: false,
        };
    },

    addCommands() {
        return {
            setBullet: (state = true) => (instance) => {
                const { tr } = instance;
                const { doc, selection } = tr;
                const { $from, $to } = selection;

                doc.nodesBetween($from.pos, $to.pos, (node, pos) => {
                    if (node.type.name === 'rootblock') {
                        tr.setNodeAttribute(pos, 'hasBullet', state);
                    }
                    return false;
                });

                return true;
            },
            toggleBullet: () => (instance) => {
                let newState = true;
                if (!selectionContainsOtherNodes(instance, 'rootblock', { hasBullet: true })) {
                    newState = false;
                }

                return instance.commands.setBullet(newState);
            },
            unsetBullet: () => (instance) => {
                return instance.commands.setBullet(false);
            },
        };
    },
});
