<template>
    <div
        class="c-attachment-display bg-cm-100 py-1 px-2 flex text-xssm rounded items-center"
    >
        {{ file.name }}

        <ClearButton
            positioningClass="ml-2"
            @click="removeAttachment(index)"
        >
        </ClearButton>
    </div>
</template>

<script>

import ClearButton from '@/components/buttons/ClearButton.vue';

export default {
    name: 'AttachmentDisplay',
    components: {
        ClearButton,
    },
    mixins: [
    ],
    props: {
        file: {
            type: Object,
            required: true,
        },
        index: {
            type: Number,
            required: true,
        },
    },
    emits: [
        'removeAttachment',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        removeAttachment(index) {
            this.$emit('removeAttachment', index);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-attachment-display {

} */

</style>
