import Image from '@tiptap/extension-image';

import {
    nodeInfo,
} from '@/core/helpers/tiptapNodeHelpers.js';

export default Image.extend({
    ...nodeInfo.image,

    addOptions() {
        return {
            HTMLAttributes: {
                class: 'tiptap-img',
            },
        };
    },
});
