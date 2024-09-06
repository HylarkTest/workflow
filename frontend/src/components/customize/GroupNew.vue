<template>
    <div class="c-group-new flex flex-col h-full">
        <h1
            v-t="headerPath"
            class="header-2 mb-8"
        >
        </h1>

        <FormWrapper
            class="flex flex-col justify-between flex-1"
            :form="form"
            @submit="saveNewGroup"
        >
            <div class="flex-1 mb-10">
                <div class="mb-6">
                    <InputBox
                        ref="input"
                        formField="name"
                        bgColor="gray"
                    >
                        <template #label>
                            {{ $t(labelPath) }}
                        </template>
                    </InputBox>
                </div>

                <TextareaField
                    v-if="!hideDescription"
                    formField="description"
                    bgColor="gray"
                    boxStyle="plain"
                >
                    {{ $t('labels.description') }} (Optional)
                </TextareaField>
            </div>

            <div class="flex justify-end mt-4">
                <button
                    v-t="addPath"
                    class="button text-cm-00 bg-primary-600"
                    :class="{ unclickable: disabled }"
                    type="submit"
                    :disabled="disabled"
                >
                </button>
            </div>
        </FormWrapper>
    </div>
</template>

<script>

export default {
    name: 'GroupNew',
    components: {

    },
    mixins: [
    ],
    props: {
        groupType: {
            type: String,
            required: true,
        },
        hideDescription: Boolean,
    },
    emits: [
        'saveNewGroup',
    ],
    data() {
        return {
            form: this.$apolloForm(() => {
                const data = {
                    name: '',
                };

                if (['PIPELINE', 'STATUS', 'TAG'].includes(this.groupType)) {
                    data.type = this.groupType;
                }

                if (!this.hideDescription) {
                    data.description = '';
                }
                return data;
            }),
        };
    },
    computed: {
        disabled() {
            return !this.form.name;
        },
        typeString() {
            return _.camelCase(this.groupType);
        },
        headerPath() {
            return this.textPath('header');
        },
        labelPath() {
            return this.textPath('label');
        },
        addPath() {
            return this.textPath('add');
        },
    },
    methods: {
        saveNewGroup() {
            this.$emit('saveNewGroup', this.form);
        },
        textPath(textKey) {
            return `customizations.${this.typeString}.new.${textKey}`;
        },
        focusName() {
            this.$refs.input.focus();
        },
    },
    mounted() {
        this.focusName();
    },
};
</script>

<style scoped>

/*.c-group-new {

} */

</style>
