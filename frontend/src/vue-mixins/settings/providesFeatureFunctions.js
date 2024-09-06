export default {
    data() {
        return {
            current: '',
            editObj: null,
        };
    },
    methods: {
        checkExisting(arr, comparator) {
            return arr.some((item) => {
                return _.lowerCase(item.name) === _.lowerCase(comparator);
            });
        },
        checkEditActive(index) {
            if (!this.editObj) {
                return false;
            }
            return index === this.editObj.index;
        },
        clearInput() {
            this.current = '';
        },
        stopEdit() {
            this.editObj = null;
        },
    },
};
