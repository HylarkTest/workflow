<!-- WIP -->

<template>
    <IconButton
        class="c-attach-button"
        :hideText="true"
        icon="fal fa-paperclip"
        colorClasses="bg-cm-200 hover:bg-cm-300"
        textColorClass="text-cm-600"
    >
    </IconButton>
</template>

<script>

import IconButton from '@/components/buttons/IconButton.vue';

export default {
    name: 'AttachButton',
    components: {
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
                    bgColor: 'gray',
                };
            },
        },
    },
    data() {
        return {
            buttonSpecs: {
                ...this.colorSpecs,
                textPath: 'common.attach',
                icon: 'fal fa-paperclip',
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

/*.c-attach-button {

} */

</style>
