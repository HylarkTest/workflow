import CodeBlock from '@tiptap/extension-code-block';

export default CodeBlock.extend({
    addOptions() {
        return {
            HTMLAttributes: {
                class: 'tiptap-codeBlock',
            },
        };
    },
});
