import { Extension } from '@tiptap/core';

import {
    selectionContainsOtherNodes,
} from '@/core/helpers/tiptapNodeHelpers.js';

export default Extension.create({
    name: 'alignment',

    addOptions() {
        return {
            alignment: ['left', 'center', 'right'],
            default: 'left',
        };
    },

    addCommands() {
        return {
            setAlignment: (alignment) => (instance) => {
                const { tr } = instance;
                const { doc, selection } = tr;
                const { $from, $to } = selection;

                doc.nodesBetween($from.pos, $to.pos, (node, pos) => {
                    if (node.type.name === 'rootblock') {
                        tr.setNodeAttribute(pos, 'alignment', alignment);

                        if (alignment === 'center') {
                            tr.setNodeAttribute(pos, 'indent', 0);
                        }
                    }
                    return false;
                });

                return true;
            },
            unsetAlignment: () => (instance) => {
                return instance.commands.setAlignment(this.options.default);
            },
            alignText: (alignment) => (instance) => {
                if (selectionContainsOtherNodes(instance, 'rootblock', { alignment })) {
                    return instance.commands.setAlignment(alignment);
                }
                return instance.commands.unsetAlignment();
            },
        };
    },
});
