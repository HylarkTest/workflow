import BaseHighlight from '@tiptap/extension-highlight';

export default BaseHighlight.extend({
    addOptions() {
        return {
            multicolor: true,
            HTMLAttributes: {
                class: 'tiptap-highlight',
            },
        };
    },
});
