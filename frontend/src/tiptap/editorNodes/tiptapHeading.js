import Heading from '@tiptap/extension-heading';

import {
    nodeInfo,
    selectionContainsOtherNodes,
} from '@/core/helpers/tiptapNodeHelpers.js';

export default Heading.extend({
    ...nodeInfo.heading,

    renderHTML({ node }) {
        const level = node.attrs.level;

        return [`h${level}`, { class: `tiptap-h${level}` }, 0];
    },

    addCommands() {
        return {
            setHeading: (level) => (instance) => {
                return instance.commands.setNode(this.name, { level });
            },
            unsetHeading: () => (instance) => {
                return instance.commands.setNode('paragraph');
            },
            toggleHeading: (level) => (instance) => {
                if (selectionContainsOtherNodes(instance, 'heading', { level })) {
                    return instance.commands.setHeading(level);
                }
                return instance.commands.unsetHeading();
            },
        };
    },
});
