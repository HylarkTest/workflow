<template>
    <IconActionButton
        v-if="isIconActionButton"
        class="c-delete-button"
        :size="size"
        :showText="showText"
        :disabled="disabled"
        :buttonSpecs="specs"
        textPath="common.delete"
    >
    </IconActionButton>

    <IconButton
        v-else
        class="c-delete-button"
        icon="fal fa-trash-alt"
        text=""
        :disabled="disabled"
        colorClasses="bg-peach-600 hover:bg-peach-500"
    >
    </IconButton>
</template>

<script>

import IconActionButton from '@/components/buttons/IconActionButton.vue';
import IconButton from '@/components/buttons/IconButton.vue';

export default {
    name: 'DeleteButton',
    components: {
        IconActionButton,
        IconButton,
    },
    mixins: [
    ],
    props: {
        showText: Boolean,
        size: {
            type: String,
            default: 'base',
        },
        buttonComponent: {
            type: String,
            default: 'IconActionButton',
            validator(val) {
                return ['IconActionButton', 'IconButton'].includes(val);
            },
        },
        disabled: Boolean,
        buttonSpecsProp: {
            type: Object,
            default() {
                return {};
            },
        },
    },
    data() {
        return {
            buttonSpecs: {
                bgColor: 'peach',
                icon: 'fal fa-trash-alt',
            },
        };
    },
    computed: {
        isIconActionButton() {
            return this.buttonComponent === 'IconActionButton';
        },
        specs() {
            return {
                ...this.buttonSpecs,
                ...this.buttonSpecsProp,
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
.c-delete-button {
}
 */

</style>
