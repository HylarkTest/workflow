export default {
    components: {
    },
    data() {
        return {
            isModalOpen: false,
        };
    },
    methods: {
        openModal() {
            this.isModalOpen = true;
        },
        closeModal() {
            this.isModalOpen = false;
        },
    },
};
