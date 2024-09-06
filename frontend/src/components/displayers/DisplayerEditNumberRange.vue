<template>
    <div class="c-displayer-edit-number-range">
        <FromToLine
            class="mb-2"
            inputClass="c-displayer-edit-number-range__input"
        >
            <InputBox
                :modelValue="modifiableFieldValue?.from"
                type="number"
                v-bind="$attrs"
                placeholder="From"
                @update:modelValue="updateFromTo($event, 'from')"
            >
            </InputBox>
        </FromToLine>
        <FromToLine
            format="TO"
            inputClass="c-displayer-edit-number-range__input"
        >
            <InputBox
                :modelValue="modifiableFieldValue?.to"
                type="number"
                v-bind="$attrs"
                placeholder="To"
                @update:modelValue="updateFromTo($event, 'to')"
            >
            </InputBox>
        </FromToLine>
    </div>
</template>

<script>

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';
import FromToLine from '@/components/displayers/FromToLine.vue';

export default {
    name: 'DisplayerEditNumberRange',
    components: {
        FromToLine,
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

<style>

.c-displayer-edit-number-range {
    &__input {
        max-width: 180px;
    }
}

</style>
