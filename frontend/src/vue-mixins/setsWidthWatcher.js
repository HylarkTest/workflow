export default {
    created() {
        this.containerWidth = false;
    },
    mounted() {
        if (this.containerRef) {
            this.container = this.$refs[this.containerRef];
        } else {
            this.container = this.$el;
        }
        const resizeFunction = () => {
            this.containerWidth = this.$el.clientWidth;
        };
        this.throttledResizeFunction = _.throttle(resizeFunction, 300);
        resizeFunction();
        window.addEventListener('resize', this.throttledResizeFunction);
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.throttledResizeFunction);
    },
};
