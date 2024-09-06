<template>
    <div class="o-registration-mappings">
        <div class="mb-10">
            <h1
                class="o-registration-mappings__title"
            >
                {{ mappingTitle }}
            </h1>
            <p
                class="o-registration-mappings__subtitle"
            >
                {{ mappingDescription }}
                <!-- <span class="bg-red-100 py-1 px-2 break-words text-xs"> {{ mappingDescriptionPath }}</span> -->
            </p>
        </div>

        <div
            class="mb-10"
        >
            <h2
                v-t="'registration.common.templateBenefits'"
                class="o-registration-mappings__header"
            >
            </h2>

            <div>
                <div
                    v-for="benefit in benefits"
                    :key="benefit"
                    class="o-registration-mappings__benefit"
                >
                    <i class="fal fa-check-circle mt-1">
                    </i>

                    <div class="o-registration-mappings__details">
                        <h6
                            v-t="benefitTranslation(benefit, 'title')"
                            class="font-semibold"
                        >
                        </h6>
                        <!-- <span
                            class="bg-red-100 py-1 px-2 break-words text-xs"
                        >
                            {{ benefitsPath }}.{{ benefit }}.title
                        </span> -->

                        <p
                            v-t="benefitTranslation(benefit, 'description')"
                            class="text-gray-700"
                        >

                        </p>
                        <!-- <span
                            class="bg-red-100 py-1 px-2 break-words text-xs"
                        >
                            {{ benefitsPath }}.{{ benefit }}.description
                        </span> -->
                    </div>
                </div>
                <!-- <template
                    v-if="!benefits.length"
                >
                    <div
                        v-for="benefit in [1, 2, 3]"
                        :key="benefit"
                        class="o-registration-mappings__benefit"
                    >

                        <i class="fal fa-check-circle mt-1">
                        </i>

                        <div class="o-registration-mappings__details">
                            <span
                                class="bg-red-100 py-1 px-2 break-words text-xs"
                            >
                                {{ benefitsPath }}.{{ benefit }}.title
                            </span>
                            <span
                                class="bg-red-100 py-1 px-2 break-words text-xs"
                            >
                                {{ benefitsPath }}.{{ benefit }}.description
                            </span>
                        </div>

                    </div>
                </template> -->
            </div>
        </div>

        <div>
            <div class="mb-4">
                <h2
                    v-t="'registration.common.included'"
                    class="o-registration-mappings__header"
                >
                </h2>

                <p
                    v-t="'registration.common.customizeMore'"
                    class="text-gray-700 text-sm"
                >
                </p>
            </div>

            <div class="mb-4">
                <h6
                    v-t="'common.features'"
                    class="o-registration-mappings__subheader"
                >
                </h6>

                <div
                    class="o-registration-mappings__features"
                >
                    <FeatureItem
                        v-for="feature in allFeatures"
                        :key="feature.val"
                        class="o-registration-mappings__feature"
                        :feature="feature"
                        :includeText="true"
                    >

                    </FeatureItem>
                </div>
            </div>

            <div v-if="hasPages">
                <h3
                    v-t="'common.pages'"
                    class="o-registration-mappings__subheader"
                >
                </h3>

                <div
                    v-if="!noPages"
                    class="flex flex-wrap -mx-2"
                >
                    <div
                        v-for="(page, index) in structurePages"
                        :key="index"
                        class="o-registration-mappings__page"
                    >
                        <i
                            class="fal fa-fw mr-1"
                            :class="page.symbol"
                        >
                        </i>
                        {{ page.pageName || page.name }}
                    </div>
                </div>

                <div
                    v-else
                    class="text-sm py-1 px-2 bg-purple-100"
                >
                    Pages in progress
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import { templates } from '@/core/mappings/templates/templates.js';

// import { findTemplateInCategory } from '@/core/mappings/defaults/defaultMappingCreators.js';

import FeatureItem from '@/components/product/FeatureItem.vue';

export default {
    name: 'RegistrationMappings',
    components: {
        FeatureItem,
    },
    mixins: [
    ],
    props: {
        mappingId: {
            type: String,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        structureDetails() {
            return _.find(templates(), { id: this.mappingId });
            // return findTemplateInCategory(mainTemplates, this.mappingId, 'templates');
        },
        structureFull() {
            return this.structureDetails.structure;
        },
        structurePages() {
            return this.structureFull.spaces[0].pages;
        },
        hasPages() {
            return this.structurePages?.length;
        },
        pagesFeatures() {
            return _(this.structurePages).flatMap('features').compact().value();
        },
        allFeatures() {
            return _.uniqBy(this.pagesFeatures, 'val');
        },
        mappingTitle() {
            return this.$t(`templates.${this.structureDetails?.id}`);
        },
        mappingDescriptionPath() {
            return `registration.templates.templateDetails.${this.structureDetails?.id}.description`;
        },
        mappingDescription() {
            return this.$t(this.mappingDescriptionPath);
        },
        benefitsPath() {
            return `registration.templates.templateDetails.${this.structureDetails.id}.benefits`;
        },
        benefits() {
            const benefitsRaw = this.$translationRaw(this.benefitsPath);
            // Babel object, obtain keys
            return _.isString(benefitsRaw) ? [] : _.keys(benefitsRaw);
        },
        // benefits() {
        //     return [1, 2, 3, 4];
        // },
        noPages() {
            return !this.structurePages?.length
                || (this.structurePages?.length === 1
                    && _.isEmpty(this.structurePages[0]));
        },
    },
    methods: {
        benefitTranslation(number, text) {
            return `${this.benefitsPath}.${number}.${text}`;
        },
    },
    created() {
    },
};
</script>x

<style scoped>

.o-registration-mappings {
    &__title {
        @apply
            font-bold
            mb-4
            text-2xl
            text-center
        ;
    }

    &__subtitle {
        @apply
            text-center
            text-sm
        ;
    }

    &__header {
        @apply
            font-semibold
            mb-2
            text-lg
        ;
    }

    &__subheader {
        @apply
            font-semibold
        ;
    }

    &__benefit {
        @apply
            flex
            text-sm
        ;

        &:not(:last-child) {
            @apply
                mb-3
            ;
        }
    }

    &__details {
        @apply
            ml-2
        ;
    }

    &__features {
        @apply
            flex
            flex-wrap
        ;

        /*
        @media (min-width: 700px) {
            &:nth-child(even) {
                @apply
                    pl-1
                ;
            }

            &:nth-child(odd) {
                @apply
                    pr-1
                ;
            }
        }
        */
    }

    &__feature {
        @apply
            py-2
            w-full
        ;

        @media (min-width: 700px) {
            & {
                @apply
                    w-1/2
                ;
            }

            &:nth-child(even) {
                @apply
                    pl-2
                ;
            }

            &:nth-child(odd) {
                @apply
                    pr-2
                ;
            }
        }
    }

    &__page {
        @apply
            bg-gray-200
            m-2
            px-4
            py-2
            rounded-lg
            text-sm
        ;
    }
}

</style>
