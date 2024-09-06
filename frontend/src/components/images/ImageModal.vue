<template>
    <Modal
        :header="header"
        @closeModal="$emit('closeModal')"
    >
        <div class="flex align-center justify-center">
            <img
                class="rounded-b-lg"
                :src="image"
                :title="header"
            />
        </div>
    </Modal>
</template>

<script>
export default {
    name: 'ImageModal',
    components: {
    },
    mixins: [
    ],
    props: {
        header: {
            type: [String, null],
            default: null,
        },
        image: {
            type: String,
            required: true,
        },
    },
    emits: [
        'closeModal',
    ],
    data() {
        return {
        };
    },
    computed: {
    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>
/* .c-image-modal {

} */
</style>
