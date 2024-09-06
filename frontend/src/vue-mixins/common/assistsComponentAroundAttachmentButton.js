import AttachmentButton from '@/components/documents/AttachmentButton.vue';

export default {
    components: {
        AttachmentButton,
    },
    props: {
        acceptsMultiples: Boolean,
    },
    methods: {
        addFile(file) {
            this.$emit('addFile', file);
        },
        attachFile() {
            this.$refs.attachButton?.$refs.attach.click(null);
        },
    },
};
