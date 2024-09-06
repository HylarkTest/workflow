<template>
    <div class="o-space-name-edit">
        <h2
            class="font-semibold mb-4"
        >
            Edit space name
        </h2>
        <FormWrapper
            :form="form"
            @submit="finish"
        >
            <InputBox
                ref="input"
                formField="name"
                bgColor="gray"
                maxlength="50"
            >
                <template #label>
                    {{ $t('labels.spaceName') }}
                </template>
            </InputBox>

            <div class="flex justify-end mt-2">
                <button
                    v-t="'common.done'"
                    type="submit"
                    class="button--sm text-cm-00 bg-azure-600"
                >

                </button>
            </div>
        </FormWrapper>
    </div>
</template>

<script>

import FormWrapper from '@/components/inputs/FormWrapper.vue';

export default {
    name: 'SpaceNameEdit',
    components: {
        FormWrapper,
    },
    mixins: [
    ],
    props: {
        space: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'saved',
        'closeModal',
    ],
    data() {
        return {
            form: this.$form({
                name: this.space.name,
                id: this.space.id,
            }),
        };
    },
    computed: {

    },
    methods: {
        finish() {
            this.$emit('saved', this.form);
            this.$emit('closeModal');
        },
    },
    mounted() {
        this.$refs.input.select();
    },
};
</script>

<style scoped>

/*
.o-space-name-edit {

}
 */

</style>
