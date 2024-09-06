import { computed } from 'vue';

export default {
    computed: {
        supportPropsObj() {
            return {}; // Define in component
        },
    },
    created() {
        this.$callSupportInfo(computed(() => this.supportPropsObj));
    },
    unmounted() {
        this.$removeSupport(this.supportPropsObj.val);
    },
};
