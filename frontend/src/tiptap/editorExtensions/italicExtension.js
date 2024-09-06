import Italic from '@tiptap/extension-italic';

export default Italic.extend({
    addOptions() {
        return {
            HTMLAttributes: {
                class: 'tiptap-italic',
            },
        };
    },
});
