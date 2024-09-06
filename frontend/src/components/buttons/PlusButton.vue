<template>
    <IconActionButton
        class="c-plus-button"
        :size="size"
        :showText="showText"
        :buttonSpecs="buttonSpecs"
        textPath="common.new"
        shape="circle"
    >
    </IconActionButton>
</template>

<script>

import IconActionButton from '@/components/buttons/IconActionButton.vue';

export default {
    name: 'PlusButton',
    components: {
        IconActionButton,
    },
    mixins: [
    ],
    props: {
        showText: Boolean,
        size: {
            type: String,
            default: 'base',
        },
        colorSpecs: {
            type: Object,
            default: () => {
                return {
                    bgColor: 'primary',
                };
            },
        },
    },
    data() {
        return {
            buttonSpecs: {
                ...this.colorSpecs,
                icon: 'far fa-plus',
            },
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

/*
.c-plus-button {
}
 */

</style>
