<template>
    <IconActionButton
        v-if="isIconActionButton"
        class="c-edit-button"
        :size="size"
        :showText="showText"
        :buttonSpecs="buttonSpecs"
    >
    </IconActionButton>

    <IconButton
        v-else
        class="c-edit-button"
        icon="fal fa-pencil-alt"
        text=""
        colorClasses="bg-primary-600 hover:bg-primary-500"
    >
    </IconButton>
</template>

<script>

import IconActionButton from '@/components/buttons/IconActionButton.vue';
import IconButton from '@/components/buttons/IconButton.vue';

export default {
    name: 'EditButton',
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
        colorSpecs: {
            type: Object,
            default: () => {
                return {
                    colorClasses: 'bg-cm-100 hover:bg-cm-200 text-cm-700',
                };
            },
        },
        buttonComponent: {
            type: String,
            default: 'IconActionButton',
            validator(val) {
                return ['IconActionButton', 'IconButton'].includes(val);
            },
        },
    },
    data() {
        return {
            buttonSpecs: {
                ...this.colorSpecs,
                icon: 'fal fa-pencil-alt',
            },
        };
    },
    computed: {
        isIconActionButton() {
            return this.buttonComponent === 'IconActionButton';
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
.c-edit-button {
}
 */

</style>
