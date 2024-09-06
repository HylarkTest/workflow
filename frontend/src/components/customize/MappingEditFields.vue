<template>
    <div class="o-mapping-edit-fields">
        <div
            class="flex justify-end mb-4"
        >
            <button
                class="button button-primary--light mr-2"
                type="button"
                @click="openSectionsModal"
            >
                Edit sections
            </button>

            <button
                class="button button-primary--light"
                type="button"
                @click="openField({})"
            >
                Add a field
            </button>
        </div>

        <div>
            <FieldItem
                v-for="field in fields"
                :key="field.id"
                class="o-mapping-edit-fields__field"
                :field="field"
                :mappingSections="mapping.sections"
                :disabled="disabledFields.includes(field.id)"
                @deleteField="deleteField"
                @editField="openField"
            >
            </FieldItem>
        </div>

        <div
            v-if="canSeeDesignShortcut"
            class="sticky bottom-2 centered bg-secondary-100 rounded-lg py-1 px-3 mt-10"
        >
            <p class="text-xs">
                {{ updateText }}
            </p>

            <button
                class="button--xs button-secondary ml-2"
                type="button"
                @click.stop="goToDesign"
            >
                Update designs
            </button>
        </div>

        <FieldForms
            v-if="isModalOpen"
            :mapping="mapping"
            :field="selectedField"
            :hasShowAddAnotherProp="true"
            @closeModal="closeField"
        >
        </FieldForms>

        <Modal
            v-if="isSectionsOpen"
            containerClass="w-300p"
            :header="true"
            @closeModal="closeSectionsModal"
        >
            <template
                #header
            >
                Edit sections
            </template>
            <SectionsEdit
                :mapping="mapping"
            >
            </SectionsEdit>
        </Modal>
    </div>
</template>

<script>

import FieldItem from '@/components/customize/fields/FieldItem.vue';
import FieldForms from '@/components/customize/fields/FieldForms.vue';
import SectionsEdit from '@/components/customize/SectionsEdit.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import { deleteMappingField } from '@/core/repositories/mappingRepository.js';

export default {
    name: 'MappingEditFields',
    components: {
        FieldItem,
        FieldForms,
        SectionsEdit,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        mapping: {
            type: Object,
            required: true,
        },
        page: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'switchSectionAndTab',
    ],
    data() {
        return {
            selectedField: null,
            isSectionsOpen: false,
            disabledFields: [],
            startingFieldsLength: this.mapping.fields.length,
        };
    },
    computed: {
        fields() {
            return this.mapping.fields;
        },
        fieldsLength() {
            return this.fields.length;
        },
        hasFields() {
            return !!this.fieldsLength;
        },
        updateText() {
            return this.hasNewFields
                ? 'Looking to add the new fields to this page\'s views?'
                : 'Want to update the views for this page?';
        },
        hasNewFields() {
            return this.fieldsLength > this.startingFieldsLength;
        },
        isEntityPage() {
            return this.page?.type === 'ENTITIES';
        },
        canSeeDesignShortcut() {
            return this.isEntityPage;
        },
    },
    methods: {
        openField(field = {}) {
            this.openModal();
            this.selectedField = field;
        },
        async deleteField(field) {
            this.disabledFields.push(field.id);
            try {
                await deleteMappingField(this.mapping, field);
            } catch (e) {
                this.disabledFields = this.disabledFields.filter((id) => id !== field.id);
            }
        },
        closeField() {
            this.closeModal();
        },
        closeSectionsModal() {
            this.isSectionsOpen = false;
        },
        openSectionsModal() {
            this.isSectionsOpen = true;
        },
        goToDesign() {
            this.$emit('switchSectionAndTab', 'VIEWS');
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-mapping-edit-fields {
    &__field {
        @apply
            min-w-fit
            px-2
            py-1
            rounded-lg
        ;

        &:nth-child(odd) {
            @apply
                bg-cm-100
            ;
        }
    }
}

</style>
