<template>
    <button
        class="c-rounded-icon centered"
        :class="colorClasses"
        type="button"
    >
        <i
            class="fa-light"
            :class="icon"
        >
        </i>
    </button>
</template>

<script>

export default {
    name: 'RoundedIcon',
    components: {

    },
    mixins: [
    ],
    props: {
        icon: {
            type: String,
            required: true,
        },
        colorClasses: {
            type: String,
            default: 'text-primary-700 bg-primary-200 hover:text-primary-800 hover:bg-primary-300',
        },
    },
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-rounded-icon {
    transition: 0.2s ease-in-out;

    @apply
        h-8
        rounded-lg
        w-8
    ;
}

</style>
