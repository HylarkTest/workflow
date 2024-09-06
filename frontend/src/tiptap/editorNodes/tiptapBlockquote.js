import Blockquote from '@tiptap/extension-blockquote';

export default Blockquote.extend({
    addOptions() {
        return {
            HTMLAttributes: {
                class: 'tiptap-blockquote',
            },
        };
    },
});
