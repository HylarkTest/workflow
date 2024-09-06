<template>
    <div class="c-displayer-edit-paragraph">
        <TextareaField
            :modelValue="modifiableFieldValue"
            v-bind="$attrs"
            @update:modelValue="updateDataValue"
        >
        </TextareaField>
    </div>
</template>

<script>

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';

export default {
    name: 'DisplayerEditParagraph',
    components: {

    },
    mixins: [
        interactsWithDisplayersEdit,
    ],
    props: {

    },
    emits: [
        'update:dataValue',
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

/*.c-displayer-edit-paragraph {

} */

</style>
