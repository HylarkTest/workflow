<template>
    <div
        class="o-page-edit-form"
        :class="{ unclickable: processing }"
    >
        <FormWrapper
            class="relative"
            :form="newItemForm"
        >
            <BooleanButtonList
                :modelValue="fieldsFiltered"
                :fullList="fields"
                :deactivatedItems="processing ? [] : [firstNameField]"
                listType="toggle"
                predicate="id"
                @update:modelValue="updateData($event, 'fields')"
            >
                <template #listItem="{ listItem }">
                    <div class="flex items-center h-full">
                        {{ listItem.name }}
                    </div>
                </template>
            </BooleanButtonList>

            <div
                v-if="hasPossibleMarkers"
                class="mt-8"
            >
                <h3
                    class="text-lg font-semibold mb-4"
                >
                    {{ $t('labels.markers') }}
                </h3>

                <BooleanButtonList
                    :modelValue="includedMarkers"
                    :fullList="possibleMarkers"
                    listType="toggle"
                    predicate="id"
                    @update:modelValue="updateData($event, 'markers')"
                >
                    <template #listItem="{ listItem }">
                        <div class="flex items-center h-full">
                            {{ listItem.name }}
                            <span
                                class="o-page-edit-form__type"
                            >
                                <i
                                    class="fal mr-1 text-secondary-600"
                                    :class="getIcon(listItem.group.type)"
                                >
                                </i>

                                {{ getMarkerType(listItem) }}
                            </span>
                        </div>
                    </template>
                </BooleanButtonList>
            </div>
        </FormWrapper>
    </div>
</template>

<script>
import BooleanButtonList from '@/components/inputs/BooleanButtonList.vue';

import { getIcon } from '@/core/display/typenamesList.js';

import { updateMappingPage } from '@/core/repositories/pageRepository.js';

export default {
    name: 'PageEditForm',
    components: {
        BooleanButtonList,
    },
    mixins: [
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            processing: false,
            newItemForm: this.$apolloForm(() => {
                return {
                    id: this.page.id,
                    newData: {
                        fields: this.page.newData.fields || [],
                        markers: this.page.newData.markers || [],
                    },
                };
            }, {
                reportValidation: true,
            }),
        };
    },
    computed: {
        fields() {
            return this.mapping?.fields || [];
        },
        firstNameField() {
            return this.fields.find((field) => {
                return field.type === 'SYSTEM_NAME';
            });
        },
        fieldsFiltered() {
            return this.fields.filter((field) => {
                return field === this.firstNameField || this.newItemForm.newData.fields.includes(field.id);
            });
        },
        includedMarkers() {
            return this.possibleMarkers.filter((marker) => {
                return this.newItemForm.newData.markers.includes(marker.id);
            });
        },
        possibleMarkers() {
            return this.mapping.markerGroups || [];
        },
        possibleMarkersLength() {
            return this.possibleMarkers.length;
        },
        hasPossibleMarkers() {
            return this.possibleMarkersLength > 0;
        },
    },
    methods: {
        async updateData(newData, dataLabel) {
            this.processing = true;
            this.newItemForm.newData[dataLabel] = _.map(newData, 'id');
            try {
                await updateMappingPage(this.newItemForm, this.page);
                this.$debouncedSaveFeedback();
            } finally {
                this.processing = false;
            }
        },
        getMarkerType(marker) {
            const camelType = _.camelCase(marker.group.type);
            return this.$t(`labels.${camelType}`);
        },
        getIcon(val) {
            return getIcon(val);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-page-edit-form {
    &__type {
        @apply
            bg-secondary-100
            font-bold
            ml-2
            px-1
            py-0.5
            rounded
            text-secondary-400
            text-xs
            uppercase
        ;
    }
}

</style>
