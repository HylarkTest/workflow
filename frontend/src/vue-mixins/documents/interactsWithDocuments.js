import AttachmentNew from '@/components/documents/AttachmentNew.vue';

import { createDocument } from '@/core/repositories/documentRepository.js';

export default {
    components: {
        AttachmentNew,
        // AttachmentsOverlay,
    },
    props: {
        displayedList: {
            type: [Object, null],
            default: null,
        },
    },
    data() {
        return {
            processingNew: false,
        };
    },
    computed: {
        defaultAssociations() {
            return null; // Set in component if there are any
        },
    },
    methods: {
        async addFile(file) {
            const list = this.displayedList || this.drives[0];
            this.processingNew = true;
            try {
                await createDocument(file, list, this.defaultAssociations);
            } finally {
                this.processingNew = false;
            }
        },
    },
};
