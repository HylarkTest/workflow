<template>
    <button
        class="c-add-circle centered transition-2eio"
        :class="buttonClass"
        type="button"
    >
        <i
            class="fa-solid fa-plus"
        >
        </i>
    </button>
</template>

<script>

export default {
    name: 'AddCircle',
    components: {

    },
    mixins: [
    ],
    props: {
        buttonType: {
            type: String,
            default: 'secondary',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        buttonClass() {
            return `button-${this.buttonType}`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-add-circle {
    height:  26px;
    width: 26px;

    @apply
        rounded-full
        text-sm
    ;
}

</style>
