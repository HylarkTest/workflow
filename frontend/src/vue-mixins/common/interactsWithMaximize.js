import MaximizeSide from '@/components/buttons/MaximizeSide.vue';

export default {
    components: {
        MaximizeSide,
    },
    props: {
        isSideMinimized: Boolean,
    },
    methods: {
        minimizeSide() {
            this.$emit('minimizeSide', false);
        },
    },
};
