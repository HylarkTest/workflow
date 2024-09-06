import { checkIsFileTypeValid } from '@/core/validation.js';

export default {
    data() {
        return {
            hovering: false,
        };
    },
    computed: {
        maxAttachments() {
            return 5; // Set in component
        },
        acceptMultiples() {
            return false; // Set in component
        },
    },
    methods: {
        invalidAttachmentFeedback(textKey) {
            this.$errorFeedback({
                customHeaderPath: `feedback.responses.${textKey}.header`,
                customMessagePath: `feedback.responses.${textKey}.message`,
            });
        },
        preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        },
        onDragEnter(e) {
            const containsFiles = e.dataTransfer.types.includes('Files');
            if (containsFiles) {
                this.hovering = true;
            }
            this.preventDefaults(e);
        },
        onDragLeave(e) {
            this.preventDefaults(e);
            this.hovering = false;
        },
        onDrop(e) {
            this.onDragLeave(e);
            let file;
            const filesLength = e.dataTransfer.files.length;
            if (this.acceptMultiples && filesLength) {
                const max = this.maxAttachments < filesLength ? this.maxAttachments : filesLength;
                const arr = _.range(0, max);
                file = [];

                // only push valid files
                arr.forEach((number) => {
                    const nextFile = e.dataTransfer.files[number];
                    if (checkIsFileTypeValid(nextFile, this.fileTypeKey)) {
                        file.push(e.dataTransfer.files[number]);
                    }
                });

                // warn the user about invalid files, but still add the valid ones
                if (file.length < arr.length) {
                    this.invalidAttachmentFeedback('multipleInvalidAttachments');
                }

                if (!file.length) {
                    return;
                }
            } else {
                file = e.dataTransfer.files[0] ?? null;

                if (file && !checkIsFileTypeValid(file, this.fileTypeKey)) {
                    this.invalidAttachmentFeedback('invalidAttachment');
                    return;
                }
            }
            if (file) {
                this.addFile(file);
            }
        },
        addFile() {
            // Add in component
        },
    },
};
