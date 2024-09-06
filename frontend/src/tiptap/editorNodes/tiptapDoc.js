import { Node } from '@tiptap/core';

import {
    nodeInfo,
} from '@/core/helpers/tiptapNodeHelpers.js';

export default Node.create({
    ...nodeInfo.doc,
    topNode: true,
});
