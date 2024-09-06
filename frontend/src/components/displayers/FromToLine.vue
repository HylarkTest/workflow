<template>
    <div class="c-from-to-line flex items-baseline">
        <span class="mr-1 text-xssm w-10 font-medium text-primary-500">
            {{ label }}:
        </span>
        <div
            :class="inputClass"
        >
            <slot>
            </slot>
        </div>
    </div>
</template>

<script>

export default {
    name: 'FromToLine',
    components: {

    },
    mixins: [
    ],
    props: {
        format: {
            type: String,
            default: 'FROM',
            validator(val) {
                return ['FROM', 'TO'].includes(val);
            },
        },
        inputClass: {
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        label() {
            const formatted = _.camelCase(this.format);
            return this.$t(`labels.${formatted}`);
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-from-to-line {

} */

</style>
