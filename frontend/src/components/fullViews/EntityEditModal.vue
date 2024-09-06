<template>
    <Modal
        class="o-entity-edit-modal"
        containerClass="w-600p"
        :header="true"
        v-bind="$attrs"
        @closeModal="closeModal"
    >
        <template
            #header
        >
            <h1 class="u-text">
                Edit - {{ itemName }}
            </h1>

        </template>

        <LoaderFetch
            v-if="isLoading"
            class="py-10"
            :sphereSize="40"
            :isFull="true"
        >
        </LoaderFetch>

        <FormWrapper
            v-if="!isLoading"
            class="py-4 px-8"
            :form="form"
            @submit="saveItem"
        >
            <div
                v-for="(section, index) in infoSource"
                :key="index"
                class="mb-8"
            >
                <h2
                    v-if="section.header"
                    class="header-display-section"
                >
                    {{ section.header }}
                </h2>

                <FormFields
                    v-if="mapping"
                    v-model:form="form.data"
                    :mapping="mapping"
                    :item="fullItem"
                    :errors="form.errors()"
                    :formattedFields="section.fields"
                >
                </FormFields>
            </div>

            <SaveButtonSticky
                type="submit"
                :disabled="processing"
            >
            </SaveButtonSticky>
        </FormWrapper>
    </Modal>
</template>

<script>

import { getFullFieldsInfoDefault } from '@/core/display/fullViewFunctions.js';
import {
    itemDisplayFlatAndFormatted,
    getBasicFormattedData,
} from '@/core/display/theStandardizer.js';

import { simpleMappingRequest } from '@/http/apollo/buildMappingRequests.js';
import MAPPING from '@/graphql/mappings/queries/Mapping.gql';
import { updateItem } from '@/core/repositories/itemRepository.js';

export default {
    name: 'EntityEditModal',
    components: {
    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        page: {
            type: [Object, null],
            required: true,
        },
    },
    emits: [
        'closeModal',
    ],
    apollo: {
        mapping: {
            query: MAPPING,
            variables() {
                if (this.item?.mapping?.id) {
                    return {
                        id: this.item.mapping.id,
                    };
                }
                return {
                    itemId: this.item.id,
                };
            },
            fetchPolicy: 'cache-first',
        },
        fullItem: {
            query() {
                return simpleMappingRequest(this.mapping, 'ONE');
            },
            variables() {
                return { id: this.item.id };
            },
            skip() {
                return !this.mapping;
            },
            update: _.property('items.item'),
        },
    },
    data() {
        return {
            form: this.$apolloForm({
                id: this.item.id,
                data: null,
            }),
            processing: false,
        };
    },
    computed: {
        isLoading() {
            return this.$apollo.queries.mapping.loading
                || this.$apollo.queries.fullItem.loading;
        },

        // Display
        itemDisplay() {
            return this.page?.design?.itemDisplay;
        },
        infoSource() {
            return this.itemDisplay
                ? itemDisplayFlatAndFormatted(this.itemDisplay, this.fields)
                : this.defaultInfo;
        },
        defaultInfo() {
            return getFullFieldsInfoDefault(this.mapping);
        },

        // Fields
        itemName() {
            return this.item.name;
        },
        fields() {
            return this.mapping?.fields;
        },
        formattedFields() {
            return getBasicFormattedData(this.fields, 'FIELDS');
        },
        itemData() {
            return this.fullItem?.data;
        },
    },
    methods: {
        initializeFields() {
            return _(this.formattedFields).map((field) => {
                return [
                    field.id,
                    this.itemData[field.id] || null,
                ];
            }).fromPairs().value();
        },
        async saveItem() {
            this.processing = true;
            try {
                await updateItem(this.form, this.mapping);
                this.$saveFeedback();
                this.closeModal();
            } finally {
                this.processing = false;
            }
        },
        closeModal() {
            this.$emit('closeModal');
        },
    },
    watch: {
        itemData(newVal) {
            if (newVal) {
                this.form.data = this.initializeFields();
            }
        },
    },
    created() {
    },
};
</script>

<style scoped>

/*.o-entity-edit-modal {

} */

</style>
