<template>
    <ButtonEl
        class="c-snazzy-option button shadow-lg hover:shadow-primary-600/20"
        :class="{ 'bg-primary-600 text-cm-00': isSelected }"
    >
        <slot>
        </slot>
    </ButtonEl>
</template>

<script>

export default {
    name: 'SnazzyOption',
    components: {

    },
    mixins: [
    ],
    props: {
        isSelected: Boolean,
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

/*.c-snazzy-option {

} */

</style>
