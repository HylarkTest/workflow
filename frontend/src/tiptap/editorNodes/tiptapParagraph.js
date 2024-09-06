import Paragraph from '@tiptap/extension-paragraph';

import {
    nodeInfo,
} from '@/core/helpers/tiptapNodeHelpers.js';

export default Paragraph.extend({
    ...nodeInfo.paragraph,

    addOptions() {
        return {
            HTMLAttributes: {
                class: 'tiptap-p',
            },
        };
    },
});
