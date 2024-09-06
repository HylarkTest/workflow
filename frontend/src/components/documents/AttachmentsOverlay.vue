<template>
    <div
        class="o-attachments-overlay centered transition-2eio"
        :class="hovering ? 'opacity-100' : 'opacity-0'"
    >
        <!-- <div
            class="absolute bg-cm-00 opacity-25 h-full w-full"
        >
        </div> -->

        <div class="p-4 relative centered">

            <div
                class="absolute bg-cm-00 h-full w-full z-0 rounded-xl shadow-lg shadow-primary-200"
            >
            </div>

            <div
                class="o-attachments-overlay__content"
            >
                {{ uploadText }}
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: 'AttachmentsOverlay',
    components: {

    },
    mixins: [
    ],
    props: {
        hovering: Boolean,
        acceptMultiples: Boolean,
        isImageType: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        uploadText() {
            const pathKey = this.isImageType ? 'dropImages' : 'dropFiles';
            const count = this.acceptMultiples ? 2 : 1;
            return this.$tc(`features.upload.${pathKey}`, count);
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.o-attachments-overlay {
    @apply
        absolute
        pointer-events-none
        top-10
        w-full
        z-cover
    ;

    &__content {
        @apply
            border-2
            border-dashed
            border-primary-300
            font-medium
            px-8
            py-6
            rounded-xl
            z-over
        ;
    }
}

</style>
