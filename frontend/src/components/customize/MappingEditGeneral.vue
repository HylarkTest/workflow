<template>
    <div class="o-mapping-edit-general">

        <div class="flex items-center">
            <label class="mr-2 text-sm font-bold">
                Blueprint type:
            </label>
            <TemplateTags
                :dataValue="{ name: mappingType }"
                :container="{ style: mappingTypeSmall }"
            >
            </TemplateTags>
        </div>
        <FormWrapper
            class="rounded-xl p-6 bg-cm-100 mt-3 relative"
            :form="blueprintForm"
            @submit="saveBlueprint"
        >
            <SettingsHeaderLine
                class="mb-8"
            >
                <template
                    #header
                >
                    Name
                </template>
                <template
                    #description
                >
                    Blueprints outline your data structure and require both a singular and a plural name.
                </template>
                <div
                    class="max-w-sm"
                >
                    <InputBox
                        formField="name"
                        placeholder="Blueprint name"
                    >
                        <template
                            #label
                        >
                            Name - Plural
                        </template>
                    </InputBox>
                </div>

                <div
                    class="max-w-sm mt-6"
                >
                    <InputBox
                        formField="singularName"
                        placeholder="Singular page name"
                    >
                        <template
                            #label
                        >
                            Name - Singular
                        </template>
                    </InputBox>
                </div>
            </SettingsHeaderLine>

            <SettingsHeaderLine
                class="mb-8"
            >
                <template
                    #header
                >
                    Description
                </template>

                <TextareaField
                    formField="description"
                    :placeholder="'Add a description for ' + mapping.name"
                    bgColor="white"
                    boxStyle="plain"
                >
                </TextareaField>

            </SettingsHeaderLine>

            <SaveButtonSticky
                :disabled="saveTurnedOff"
                :pulse="true"
            >
            </SaveButtonSticky>
        </FormWrapper>

        <div
            class="rounded-xl p-6 bg-cm-100 mt-3"
        >
            <div class="flex items-center">
                <div class="flex items-center text-sm">
                    <div class="h-8 w-8 circle-center text-primary-600 bg-primary-200 mr-2">
                        <i
                            class="far fa-memo"
                        >
                        </i>
                    </div>
                    <span
                        class="uppercase font-semibold text-cm-400 mr-2"
                    >
                        Used by pages:
                    </span>
                </div>

                <span class="font-semibold">
                    {{ pageList }}
                </span>
            </div>
        </div>
    </div>
</template>

<script>

import interactsWithFormCanSave from '@/vue-mixins/common/interactsWithFormCanSave.js';
import providesBlueprintGeneralForm from '@/vue-mixins/customizations/providesBlueprintGeneralForm.js';
import { mappingTypeSmall } from '@/core/display/systemTagDesigns.js';
import { updateMapping } from '@/core/repositories/mappingRepository.js';

export default {
    name: 'MappingEditGeneral',
    components: {
    },
    mixins: [
        providesBlueprintGeneralForm,
        interactsWithFormCanSave,
    ],
    props: {
        mapping: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            requiredFields: [
                'name',
            ],
            processing: false,
        };
    },
    computed: {
        saveTurnedOff() {
            return !this.canSave || this.processing;
        },
        mappingType() {
            return this.$t(`customizations.blueprint.type.${_.camelCase(this.mapping.type)}`);
        },
        checkerForm() {
            return this.blueprintForm;
        },
        checkerOriginal() {
            return this.mapping;
        },
        pages() {
            return this.mapping.pages;
        },
        pageNames() {
            return _.map(this.pages, 'name');
        },
        pageList() {
            return _.join(this.pageNames, ', ');
        },
    },
    methods: {
        async saveBlueprint() {
            this.processing = true;
            try {
                await updateMapping(this.blueprintForm);
                this.$saveFeedback();
            } finally {
                this.processing = false;
            }
        },
    },
    created() {
        this.mappingTypeSmall = mappingTypeSmall;
    },
};
</script>

<style scoped>

/*.o-mapping-edit-general {

} */

</style>
