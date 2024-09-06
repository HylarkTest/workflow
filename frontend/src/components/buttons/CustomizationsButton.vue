<template>
    <button
        type="button"
        class="c-customizations-button centered"
        @click="$emit('click', $event)"
    >
        <i
            class="fal fa-sliders-simple"
        >
        </i>
    </button>
</template>

<script>

export default {
    name: 'CustomizationsButton',
    components: {

    },
    mixins: [
    ],
    props: {

    },
    emits: [
        'click',
    ],
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

.c-customizations-button {
    @apply
        bg-cm-100
        h-10
        w-10
    ;
}

</style>
