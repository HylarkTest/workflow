<template>
    <div
        class="o-mapping-edit-features"
        :class="{ unclickable: processing }"
    >
        <p class="mb-8">
            Select the features you want associated with "{{ mapping.name }}".
        </p>

        <BooleanButtonList
            :modelValue="featureOptionsFiltered"
            :fullList="featureOptions"
            :confirmationList="featureOptionsRequiringConfirmation"
            listType="toggle"
            predicate="val"
            @update:modelValue="updateFeatures"
        >
            <template #listItem="{ listItem }">
                <div class="flex">
                    <i
                        class="fal mr-3 mt-1 fa-fw text-primary-500"
                        :class="listItem.symbol"
                    >
                    </i>
                    <div>
                        <h4
                            v-t="titleText(listItem)"
                            class="text-smbase font-semibold"
                        >
                        </h4>
                        <p
                            v-t="blueprintText(listItem, 'description')"
                            class="text-sm text-cm-500"
                        >
                        </p>
                    </div>
                </div>
            </template>

            <template #confirmationContent="{ listItem }">
                <p
                    v-t="blueprintText(listItem, 'deactivation')"
                    class="mb-4"
                >
                </p>
                <p
                    v-if="listItem.val !== 'TIMEKEEPER'"
                    v-t="blueprintText(listItem, 'hideAssociations')"
                    class="mb-4"
                >
                </p>
                <p
                    v-t="blueprintText(listItem, 'reactivation')"
                >
                </p>
            </template>
        </BooleanButtonList>
    </div>
</template>

<script>
import BooleanButtonList from '@/components/inputs/BooleanButtonList.vue';

import { allFeatures } from '@/core/display/typenamesList.js';
import { updateMappingFeatures } from '@/core/repositories/mappingRepository.js';
import { reportValidationError } from '@/core/uiGenerators/userFeedbackGenerators.js';

const features = [
    'EVENTS',
    'TODOS',
    'NOTES',
    'PINBOARD',
    'DOCUMENTS',
    'TIMEKEEPER',
    'LINKS',
    'PRIORITIES',
    'FAVORITES',
    'EMAILS',
];

const deactivatesImmediately = [
    'PRIORITIES',
    'FAVORITES',
];

export default {
    name: 'MappingEditFeatures',
    components: {
        BooleanButtonList,
    },
    mixins: [
    ],
    props: {
        mapping: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            processing: false,
        };
    },
    computed: {
        featureOptions() {
            return this.validFeatures.map((feature) => {
                return allFeatures[feature];
            });
        },
        featureOptionsFiltered() {
            return this.featureOptions.filter((option) => {
                return this.mapping.features.find((feature) => feature.val === option.val);
            });
        },
        featureOptionsRequiringConfirmation() {
            return this.featureOptions.filter((option) => {
                return !deactivatesImmediately.includes(option.val);
            });
        },
    },
    methods: {
        blueprintText(feature, key) {
            return {
                path: `customizations.blueprint.features.${_.camelCase(feature.val)}.${key}`,
                args: { mappingName: this.mapping.name },
            };
        },
        titleText(feature) {
            return `features.${_.camelCase(feature.val)}.title`;
        },
        async updateFeatures(newFeatures) {
            this.processing = true;
            try {
                await updateMappingFeatures(
                    this.mapping,
                    newFeatures.map((feature) => ({ val: feature.val }))
                );
                this.$debouncedSaveFeedback();
            } catch (error) {
                reportValidationError(error, 'input.id');
            } finally {
                this.processing = false;
            }
        },
    },
    created() {
        this.validFeatures = features;
    },
};
</script>

<style scoped>

/*.o-mapping-edit-features {

} */

</style>
