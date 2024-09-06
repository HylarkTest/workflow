<template>
    <div class="o-relationship-form">
        <FormWrapper
            :form="form"
        >
            <SettingsHeaderLine
                class="mb-10"
            >
                <template
                    #header
                >
                    Name
                </template>

                <InputBox
                    formField="name"
                    maxlength="60"
                    placeholder="Add a relationship name"
                    bgColor="gray"
                >
                </InputBox>
            </SettingsHeaderLine>

            <SettingsHeaderLine
                class="mb-10"
            >
                <template
                    #header
                >
                    Relationship is between...
                </template>

                <template
                    v-if="isNew"
                    #description
                >
                    Select how the two blueprints are related to each other.
                </template>

                <div>
                    <div class="flex items-center bg-cm-100 p-3 rounded-xl text-sm">
                        <DropdownBox
                            v-if="isNew"
                            v-model="fromRelationship"
                            class="mb-4 w-20 sm:mb-0 sm:mr-6"
                            :options="numberOptions"
                            :displayRule="numberOptionsDisplay"
                        >
                        </DropdownBox>

                        <div
                            v-else
                            class="button-rounded--sm bg-primary-200 text-primary-600 mr-3"
                        >
                            {{ fromDisplay }}
                        </div>

                        <span class="font-semibold mr-2">
                            {{ mappingName }}
                        </span>

                        {{ recordFrom }}
                    </div>

                    <div class="text-center text-xs font-semibold my-4 uppercase text-cm-500">
                        {{ fromTypeKey === 'ONE' ? 'Is related to...' : 'Are related to...' }}
                    </div>

                    <div class="flex items-center bg-cm-100 p-3 rounded-xl text-sm">
                        <template v-if="isNew">
                            <DropdownBox
                                v-model="toRelationship"
                                class="mb-4 w-20 sm:mb-0 sm:mr-6"
                                :options="numberOptions"
                                :displayRule="numberOptionsDisplay"
                            >
                            </DropdownBox>

                            <div class="mb-4 sm:mb-0 sm:mr-3">
                                <BlueprintPicker
                                    v-model="form.to"
                                    class="w-48"
                                    :spaceId="spaceId"
                                    modelToId
                                    :error="form.errors().getFirst('to')"
                                >
                                </BlueprintPicker>
                            </div>
                        </template>

                        <template v-else>
                            <p
                                class="button-rounded--sm bg-primary-200 text-primary-600 mr-3"
                            >
                                {{ toDisplay }}
                            </p>
                            <p class="font-semibold">
                                {{ toMappingName }}
                            </p>
                        </template>

                        <span class="ml-2">{{ recordTo }}</span>
                    </div>

                </div>
            </SettingsHeaderLine>

            <SettingsHeaderLine>
                <template
                    #header
                >
                    Inverse name
                </template>

                <InputBox
                    formField="inverseName"
                    maxlength="60"
                    placeholder="Add an inverse name"
                    bgColor="gray"
                >
                </InputBox>
            </SettingsHeaderLine>

            <SaveButtonSticky
                :buttons="['another']"
                @save="submitRelationship"
            >
            </SaveButtonSticky>
        </FormWrapper>
    </div>
</template>

<script>

import BlueprintPicker from '@/components/pickers/BlueprintPicker.vue';

// import providesPageOptions from '@/vue-mixins/settings/providesPageOptions.js';

import {
    createMappingRelationship,
    updateMappingRelationship,
} from '@/core/repositories/mappingRepository.js';

export default {

    name: 'RelationshipForm',
    components: {
        BlueprintPicker,
    },
    mixins: [
        // providesPageOptions,
    ],
    props: {
        relationship: {
            type: Object,
            default: () => { return {}; },
        },
        mapping: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'closeModal',
    ],
    data() {
        const isNew = !this.relationship.id;
        return {
            form: this.$apolloForm(() => {
                const data = {
                    mappingId: this.mapping.id,
                    name: this.relationship.name || '',
                    inverseName: this.relationship.inverse?.name || this.mapping.name || '',
                };

                if (isNew) {
                    data.type = 'ONE_TO_ONE';
                    data.to = null;
                } else {
                    data.id = this.relationship.id;
                }

                return data;
            }),
        };
    },
    computed: {
        isNew() {
            return !this.relationship.id;
        },
        recordFrom() {
            return this.fromTypeKey === 'ONE' ? 'record' : 'records';
        },
        recordTo() {
            return this.toTypeKey === 'ONE' ? 'record' : 'records';
        },

        // ONE/MANY for the form
        fromRelationship: {
            get() {
                return this.isNew && this.form.type.split('_TO_')[0];
            },
            set(relationship) {
                this.form.type = [relationship, this.toRelationship].join('_TO_');
            },
        },
        toRelationship: {
            get() {
                return this.isNew && this.form.type.split('_TO_')[1];
            },
            set(relationship) {
                this.form.type = [this.fromRelationship, relationship].join('_TO_');
            },
        },

        // ONE/MANY for the display
        relationshipType() {
            return !this.isNew ? this.relationship.type : this.form.type;
        },
        fromTypeKey() {
            return this.relationshipType.split('_TO_')[0];
        },
        fromDisplay() {
            return _.capitalize(this.fromTypeKey);
        },
        toTypeKey() {
            return this.relationshipType.split('_TO_')[1];
        },
        toDisplay() {
            return _.capitalize(this.toTypeKey);
        },

        mappingName() {
            return this.mapping.name;
        },
        toMappingName() {
            return !this.isNew && this.relationship.to.name;
        },
        spaceId() {
            return this.mapping.space.id;
        },
    },
    methods: {
        async submitAndClose() {
            this.form.setOptions({ clear: false });
            await this.submitForm();
            this.$emit('closeModal');
        },
        submitAndAnother() {
            this.form.setOptions({ clear: true });
            this.submitForm();
        },
        submitForm() {
            const fn = this.isNew ? createMappingRelationship : updateMappingRelationship;
            return fn(this.form);
        },
        submitRelationship(action) {
            this[`submitAnd${_.capitalize(action)}`]();
        },
    },
    watch: {
        // 'form.relationship.type': function onTypeChange() {
        //     if (this.fromTypeKey === 'MANY' && this.form.relationship.inverseName === this.page.singularName) {
        //         this.form.relationship.inverseName = this.page.name;
        //     }
        //     if (this.fromTypeKey === 'ONE' && this.form.relationship.inverseName === this.page.name) {
        //         this.form.relationship.inverseName = this.page.singularName;
        //     }
        // },
        // 'form.relationship.to': function onToChange(id, previousId) {
        //     const previousName = previousId && this.blueprintOptionsDisplay(previousId);
        //     const formName = this.form.relationship.name;
        //     if (!formName || formName === previousName) {
        //         this.form.relationship.name = this.blueprintOptionsDisplay(id);
        //     }
        // },
    },
    created() {
        if (this.isNew) {
            // const nameKeyCondition = () => (this.toRelationship === 'MANY' ? 'name' : 'singularName');
            // this.initPageDisplay(nameKeyCondition);

            this.numberOptions = ['ONE', 'MANY'];
            this.numberOptionsDisplay = _.capitalize;
        }
    },
};
</script>

<style scoped>
.o-relationship-form {
    @apply
        p-6
    ;

    &__container {
        @apply
            bg-cm-200
            flex
            items-center
            px-4
            py-3
            rounded
        ;

        @media (max-width: 640px) {
            & {
                @apply
                    flex-col
                    items-start
                ;
            }
        }
    }
}
</style>
