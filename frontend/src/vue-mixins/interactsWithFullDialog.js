import FullDialog from '@/components/dialogs/FullDialog.vue';

export default {
    components: {
        FullDialog,
    },
    data() {
        return {
            isDialogOpen: false,
        };
    },
    methods: {
        openFullDialog() {
            this.isDialogOpen = true;
        },
        closeFullDialog() {
            this.isDialogOpen = false;
        },
    },
};
