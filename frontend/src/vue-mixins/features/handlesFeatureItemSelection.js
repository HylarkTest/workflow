export default {
    props: {
        selectedItem: {
            type: [Object, null],
            default: null,
        },
    },
    computed: {
        isSelected() {
            return this.featureItem.id === this.selectedItem?.id;
        },
        highlightedClass() {
            return this.isSelected ? 'shadow-primary-400/30 shadow-md' : '';
        },
    },
};
