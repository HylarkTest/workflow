import { Extension } from '@tiptap/core';

export default Extension.create({
    name: 'indent',

    addOptions() {
        return {
            level: [0, 1, 2, 3, 4, 5, 6],
        };
    },

    addCommands() {
        return {
            setIndent: (level) => (instance) => {
                const { tr } = instance;
                const { doc, selection } = tr;
                const { $from, $to } = selection;

                doc.nodesBetween($from.pos, $to.pos, (node, pos) => {
                    if (node.type.name === 'rootblock' && node.attrs.alignment !== 'center') {
                        const indent = _.clamp(
                            level,
                            _.head(this.options.level),
                            _.last(this.options.level)
                        );
                        tr.setNodeAttribute(pos, 'indent', indent);
                    }
                    return false;
                });

                return true;
            },
            incrementIndent: (delta = 1) => (instance) => {
                const { tr } = instance;
                const { doc, selection } = tr;
                const { $from, $to } = selection;

                doc.nodesBetween($from.pos, $to.pos, (node, pos) => {
                    if (node.type.name === 'rootblock' && node.attrs.alignment !== 'center') {
                        const indent = _.clamp(
                            node.attrs.indent + delta, // rootblock defines a default value so this is always a number
                            _.head(this.options.level),
                            _.last(this.options.level)
                        );
                        tr.setNodeAttribute(pos, 'indent', indent);
                    }
                    return false;
                });

                return true;
            },
            decrementIndent: () => (instance) => {
                return instance.commands.incrementIndent(-1);
            },
            unsetIndent: () => (instance) => {
                return instance.commands.setIndent(0);
            },
        };
    },

    addKeyboardShortcuts() {
        return {
            Tab: (instance) => {
                return instance.editor.commands.incrementIndent();
            },
            'Shift-Tab': (instance) => {
                return instance.editor.commands.decrementIndent();
            },
        };
    },
});
