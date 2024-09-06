export const nodeInfo = {
    doc: {
        name: 'doc',
        content: 'rootblock+',
    },
    rootblock: {
        name: 'rootblock',
        group: 'rootblock',
        content: 'block',
    },
    paragraph: {
        name: 'paragraph',
        group: 'block',
        content: 'inline*',
    },
    heading: {
        name: 'heading',
        group: 'block',
        content: 'inline*',
    },
    image: {
        name: 'image',
        group: 'block',
    },
};

export const nodesByKey = (key) => {
    return Object.values(nodeInfo).reduce((keys, node) => {
        const keyToMatch = node[key];
        const nodes = keys[keyToMatch] || [];

        return {
            ...keys,
            [keyToMatch]: [...nodes, node.name],
        };
    }, {});
};
export const nodesByGroup = nodesByKey('group');
export const nodesByContent = nodesByKey('content');

export const hasNoSelection = (instance) => {
    const { $from, $to } = instance.tr.selection;
    return $from.pos === $to.pos;
};

export const selectionIsAtEndOfNode = (instance) => {
    const { $to } = instance.tr.selection;
    return $to.pos === $to.end();
};

export const selectionHasMultipleRootblocks = (instance) => {
    const { $to, $from } = instance.tr.selection;
    return $to.start() !== $from.start();
};

export const getFromRootblock = (instance) => {
    // doc can only contain rootblocks so depth = 1 is always a rootblock
    return instance.tr.selection.$from.node(1);
};
// Used to find other Nodes of the same content type within a selection, or if the chosen node type has different markup
// e.g. when setting a header, we want to know if the selection contains other "inline*" nodes,
// or another header node with a different level.
// Another example: finding the alignment attributes of all rootblocks in a selection
export const selectionContainsOtherNodes = (
    instance,
    name,
    attrs, // optional
    marks, // optional
    contentSensitive = true
) => {
    const { tr, editor } = instance;
    const { from, to } = tr.selection;

    const otherNodes = contentSensitive
        ? nodesByContent[nodeInfo[name].content]
        : Object.keys(nodeInfo);

    const _NodeType = editor.schema.nodes[name];

    // node is the same type but has different markup
    let otherNodeFound = false;
    tr.doc.nodesBetween(from, to, (node) => {
        if (!otherNodeFound && otherNodes.includes(node.type.name)) {
            if (node.type.name === name) {
                // 1) The node is of the same type but has markup different from the parameters.
                // 2) If parameters are undefined, then we only care about the type,
                // i.e. a matching type means `otherNodeFound = false`.
                // 3) `undefined` being a valid value may be confusing,
                // but it is in-line with prosemirror reference manual.
                const args = [
                    _NodeType,
                    ...(attrs ? [{ ...node.attrs, ...attrs }] : []),
                    ...(marks ? [[...node.marks, ...marks]] : []),
                ];
                otherNodeFound = !node.hasMarkup(...args);
            } else {
                // The node is of a different type
                otherNodeFound = true;
            }
        }

        return true;
    });

    return otherNodeFound;
};
