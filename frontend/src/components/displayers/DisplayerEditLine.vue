<template>
    <div class="c-displayer-edit-line">
        <InputBox
            ref="input"
            :modelValue="modifiableFieldValue"
            v-bind="inputProps"
            dontUpdateForm
            @update:modelValue="updateDataValue"
        >
        </InputBox>
    </div>
</template>

<script>

import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';

export default {
    name: 'DisplayerEditLine',
    components: {

    },
    mixins: [
        interactsWithDisplayersEdit,
    ],
    props: {
        focusInitially: Boolean,
    },
    emits: [
        'update:dataValue',
    ],
    data() {
        return {

        };
    },
    computed: {
        isSystemName() {
            return this.dataInfo?.info?.fieldType === 'SYSTEM_NAME';
        },
        isUrlField() {
            return this.dataInfo?.info?.fieldType === 'URL';
        },
        inputProps() {
            let baseProps = this.$attrs;
            if (this.isUrlField) {
                baseProps = {
                    ...baseProps,
                    maxLength: 1000,
                };
            }
            return baseProps;
        },
    },
    methods: {

    },
    created() {

    },
    mounted() {
        if (this.isSystemName && this.focusInitially) {
            this.$refs.input.$refs.input.select();
        }
    },
};
</script>

<style scoped>

/*.c-displayer-edit-line {

} */

</style>
