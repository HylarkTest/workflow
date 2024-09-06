<template>
    <div class="o-wizard-name-description">
        <form
            class="max-w-xl"
            @submit.prevent="$emit('pressedEnter')"
        >
            <div>
                <h2 class="o-creation-wizard__prompt mt-20">
                    <slot name="nameTitle">
                    </slot>
                </h2>
                <p
                    v-if="showSingularName"
                    v-t="'customizations.pageWizard.naming.instructions'"
                    class="o-creation-wizard__description mb-6"
                >
                </p>

                <InputBox
                    ref="input"
                    :modelValue="form.name"
                    :maxLength="50"
                    bgColor="gray"
                    :placeholder="$t('common.addName')"
                    @update:modelValue="updateForm('name', $event)"
                >
                    <template
                        v-if="showSingularName"
                        #label
                    >
                        {{ nameLabel }}
                    </template>
                </InputBox>

                <div
                    v-if="showSingularName"
                    class="mt-7"
                >
                    <InputBox
                        :modelValue="form.singularName"
                        :maxLength="50"
                        bgColor="gray"
                        :placeholder="$t('customizations.pageWizard.naming.addSingularName')"
                        @update:modelValue="updateForm('singularName', $event)"
                    >
                        <template
                            #label
                        >
                            {{ $t('customizations.pageWizard.naming.singularName') }}
                        </template>
                    </InputBox>
                </div>
            </div>

            <div>
                <h2 class="o-creation-wizard__prompt mt-20">
                    <slot name="descriptionTitle">
                    </slot>
                </h2>

                <TextareaField
                    :modelValue="form.description"
                    :placeholder="$t('customizations.pageWizard.naming.addDescription')"
                    bgColor="gray"
                    boxStyle="plain"
                    @update:modelValue="updateForm('description', $event)"
                >
                </TextareaField>
            </div>
        </form>
    </div>
</template>

<script>

import pluralize from 'pluralize';

export default {
    name: 'WizardNameDescription',
    components: {

    },
    mixins: [
    ],
    props: {
        showNameLabels: Boolean,
        showSingularName: Boolean,
        form: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:form',
        'pressedEnter',
    ],
    data() {
        return {
            editedSingularName: false,
        };
    },
    computed: {
        nameLabel() {
            return this.showSingularName ? this.$t('customizations.pageWizard.naming.pluralName') : '';
        },
    },
    methods: {
        focusName() {
            this.$refs.input.focus();
        },
        updateForm(valKey, newVal) {
            if (valKey === 'singularName') {
                this.editedSingularName = true;
            }
            this.$emit('update:form', { valKey, newVal });
        },
    },
    watch: {
        'form.name': function onChangeName(newName) {
            if (!this.editedSingularName) {
                this.$emit('update:form', { valKey: 'singularName', newVal: pluralize.singular(newName) });
            }
        },
    },
    mounted() {
        this.focusName();
    },
};
</script>

<style scoped>

/*.o-wizard-name-description {
}*/

</style>
