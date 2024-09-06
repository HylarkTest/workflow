<template>
    <div class="c-displayer-edit-number">
        <InputBox
            ref="input"
            :modelValue="modifiableFieldValue"
            type="number"
            v-bind="$attrs"
            @update:modelValue="updateDataValue"
        >
        </InputBox>
    </div>
</template>

<script>

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';

export default {
    name: 'DisplayerEditNumber',
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

.c-displayer-edit-number {
    max-width: 180px;
}

</style>
