<template>
    <Modal
        class="c-displayer-edit-modal"
        containerClass="c-displayer-edit-modal__width"
        :containerStyle="{ maxWidth: '600px' }"
        :header="true"
        v-bind="$attrs"
        @closeModal="closeModal"
    >
        <template
            #header
        >
            <h1 class="u-text flex items-center">
                {{ `${$t('common.edit')} - ${itemName}` }}

                <div
                    class="h-1/2 w-0.5 bg-cm-600 mx-3"
                >
                    &nbsp;
                </div>

                <DataNameDisplay
                    :dataObj="fieldInfo.dataInfo"
                >
                </DataNameDisplay>
            </h1>

        </template>

        <div class="p-4">
            <FormWrapper
                :form="form"
                @submit="emitForm"
            >
                <DisplayerEditContainer
                    :dataValue="usedData"
                    :dataInfo="fieldInfo.dataInfo"
                    bgColor="gray"
                    :item="item"
                    formField="dataValue"
                    :inEditForm="!hideSave"
                    v-bind="$attrs"
                    :error="form.errors().getFirst('dataValue.*')"
                    @update:dataValue="setDataValue"
                >
                </DisplayerEditContainer>

                <SaveButtonSticky
                    v-if="!hideSave"
                    class="mt-2"
                    :disabled="processing"
                >
                </SaveButtonSticky>
            </FormWrapper>
        </div>
    </Modal>
</template>

<script>

import DataNameDisplay from '@/components/customize/DataNameDisplay.vue';

export default {
    name: 'DisplayerEditModal',
    components: {
        DataNameDisplay,
    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        fieldInfo: {
            type: Object,
            required: true,
            // Object with dataValue and dataInfo
        },
        hideSave: Boolean,
        bypassForm: Boolean,
        processing: Boolean,
    },
    emits: [
        'saveField',
        'closeModal',
    ],
    data() {
        return {
            form: this.$apolloForm({
                id: this.fieldInfo.dataInfo.id,
                dataValue: this.fieldInfo.dataValue,
            }),
        };
    },
    computed: {
        usedData() {
            if (this.bypassForm) {
                return this.fieldInfo.dataValue;
            }
            return this.form.dataValue;
        },
        itemName() {
            return this.item?.name;
        },
    },
    methods: {
        emitForm() {
            this.$emit('saveField', this.form);
        },
        closeModal() {
            this.$emit('closeModal');
        },
        setDataValue(event) {
            this.form.dataValue = event;
            if (this.hideSave) {
                this.emitForm();
            }
        },
    },
    created() {

    },
};
</script>

<style>

.c-displayer-edit-modal {
    &__width {
        min-height: 300px;
        min-width: 400px;
    }
}

</style>
