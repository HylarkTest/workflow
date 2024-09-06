import { toRefs } from 'vue';

export default (props) => {
    const { editor } = toRefs(props);

    const isActiveNode = (...configuration) => {
        return editor.value.isActive(...configuration);
    };

    const getAttribute = (attribute, key) => {
        const editorAttribute = editor.value.getAttributes(attribute);
        return key ? editorAttribute[key] : editorAttribute;
    };

    const getSelection = () => {
        return editor.value.view.state.selection;
    };

    const getTextFromSelection = ({ from, to }) => {
        return editor.value.state.doc.textBetween(from, to, '');
    };

    const runCommands = (callbackFn) => {
        editor.value
            .chain()
            .focus()
            .command(({ commands }) => callbackFn(commands))
            .run();
    };

    const isFirstNodeSelected = () => {
        const { doc, selection } = editor.value.state;
        const endOfFirst = doc.firstChild.nodeSize;
        return endOfFirst > selection.$from.pos;
    };

    const isLastNodeSelected = () => {
        const { doc, selection } = editor.value.state;
        const startOfLast = doc.content.size - doc.lastChild.nodeSize;
        return startOfLast < selection.$from.pos;
    };

    const getSelectedNode = () => {
        const { selection } = editor.value.state;
        return selection.node || selection.$anchor.parent;
    };

    const isNodeOfTypeSelected = (type) => {
        const selectedNode = getSelectedNode();
        return selectedNode.type.name === type;
    };

    const addNodeBeforeSelection = (
        node = { type: 'rootblock', content: [{ type: 'paragraph' }] }
    ) => {
        return runCommands((commands) => commands.insertContentAt(
            editor.value.state.selection.$from.start() - 1, node
        ));
    };

    const addNodeAfterSelection = (
        node = { type: 'rootblock', content: [{ type: 'paragraph' }] }
    ) => {
        return runCommands((commands) => commands.insertContentAt(
            editor.value.state.selection.$to.end() + 1, node
        ));
    };

    return {
        isActiveNode,
        isFirstNodeSelected,
        isLastNodeSelected,
        isNodeOfTypeSelected,
        addNodeAfterSelection,
        addNodeBeforeSelection,
        runCommands,
        getAttribute,
        getSelection,
        getTextFromSelection,
    };
};
