<template>
    <IconActionButton
        class="c-angle-button"
        size="xs"
        :buttonSpecs="buttonSpecs"
        shape="circle"
    >
    </IconActionButton>
</template>

<script>

import IconActionButton from '@/components/buttons/IconActionButton.vue';

export default {
    name: 'AngleButton',
    components: {
        IconActionButton,
    },
    mixins: [
    ],
    props: {
        colorSpecs: {
            type: Object,
            default: () => {
                return {
                    bgColor: 'primary',
                };
            },
        },
        angleDirection: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
        };
    },
    computed: {
        buttonSpecs() {
            return {
                ...this.colorSpecs,
                icon: `fas fa-angle-${this.angleDirection}`,
            };
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*
.c-angle-button {
}
 */

</style>
