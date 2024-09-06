<template>
    <div
        class="bg-secondary-100 rounded-lg text-center font-medium"
    >
        <p :class="headerClass">
            {{ $t('customizations.pages.noPages') }}
        </p>
        <RouterLink
            :to="{ name: 'customizePage' }"
            class="button-secondary"
            :class="buttonClass"
        >
            {{ $t('customizations.pages.addPages') }}
        </RouterLink>
    </div>

</template>

<script>

export default {
    name: 'AddPages',
    components: {
    },
    mixins: [
    ],
    props: {
        headerCustomClass: {
            type: String,
            default: 'mb-4',
        },
        textSize: {
            type: String,
            default: 'base',
            validator(val) {
                return ['xs', 'sm', 'base', 'lg'].includes(val);
            },
        },
        buttonSize: {
            type: String,
            default: '',
        },
    },
    emits: [
    ],
    data() {
        return {

        };
    },
    computed: {
        buttonClass() {
            const suffix = this.buttonSize ? `--${this.buttonSize}` : '';
            return `button${suffix}`;
        },
        textClass() {
            return `text-${this.textSize}`;
        },
        headerClass() {
            return `${this.headerCustomClass} ${this.textClass}`;
        },
    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>
/* .c-add-pages {

} */
</style>
