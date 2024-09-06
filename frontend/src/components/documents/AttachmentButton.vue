<template>
    <div class="c-attachment-button">
        <input
            ref="attach"
            id="attachment"
            class="hidden"
            type="file"
            accept=""
            :multiple="acceptMultiples"
            :disabled="disabled"
            @change="onChange"
        >

        <slot>
        </slot>
    </div>
</template>

<script>

export default {
    name: 'AttachmentButton',
    components: {

    },
    mixins: [
    ],
    props: {
        /**
         * Whether to keep the input value after emitting the file.
         * Normally you want to clear the input value so that the user can
         * select a new file. But there could be cases where you want to keep
         * the selected file.
         */
        keepInputAfterEmit: Boolean,
        acceptMultiples: Boolean,
        disabled: Boolean,
        maxAttachments: {
            type: Number,
            default: 5,
        },
    },
    emits: [
        'addFile',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        onChange(event) {
            let file;
            const filesLength = event.target.files.length;
            if (this.acceptMultiples && filesLength) {
                const max = this.maxAttachments < filesLength ? this.maxAttachments : filesLength;
                const arr = _.range(0, max);
                file = [];
                arr.forEach((number) => {
                    file.push(event.target.files[number]);
                });
            } else {
                file = event.target.files[0] ?? null;
            }
            if (file) {
                this.$emit('addFile', file);
                if (!this.keepInputAfterEmit) {
                    // eslint-disable-next-line no-param-reassign
                    event.target.value = '';
                }
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-attachment-button {

} */

</style>
