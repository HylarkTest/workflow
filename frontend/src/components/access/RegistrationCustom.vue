<template>
    <RegistrationBase
        class="o-registration-custom"
        :showNext="!!features.length"
        :showPrevious="showPrevious"
        @nextStep="$emit('nextStep', 'features')"
        @previousStep="previousStep"
    >
        <template
            #title
        >
            <!-- {{ $t('registration.features.title') }} -->
        </template>

        <!-- <template
            #subtitle
        >
            {{ $t('registration.features.subtitle') }}
        </template> -->

        <ul
            class="o-registration-custom__features"
        >
            <li
                v-for="feature in sortedFeatures"
                :key="feature"
                class="o-registration-custom__item"
            >
                <button
                    type="button"
                    class="o-registration-custom__feature"
                    :class="{ 'o-registration-custom__feature--selected': isSelected(feature) }"
                    @click="toggleFeature(feature)"
                >
                    <div
                        v-if="isSelected(feature)"
                        class="o-registration-custom__check"
                    >
                        <i
                            class="fas fa-check-circle text-turquoise-600"
                        >
                        </i>
                    </div>
                    <div
                        class="o-registration-custom__wrap"
                        :class="{ 'o-registration-custom__placeholder': hasNoImage(feature) }"
                    >
                        <img
                            v-if="!hasNoImage(feature)"
                            class="o-registration-features__image"
                            :src="imageSource(feature)"
                            :alt="$t(getTranslation(feature, 'title'))"
                            @error="notShowing(feature)"
                        >
                    </div>
                    <div class="p-4 text-center">
                        <h4
                            v-t="getTranslation(feature, 'title')"
                            class="o-registration-custom__title"
                        >
                        </h4>
                        <p
                            v-t="getTranslation(feature, 'paragraph')"
                            class="o-registration-custom__paragraph"
                        >
                        </p>
                    </div>
                </button>
            </li>
        </ul>
    </RegistrationBase>
</template>

<script>

import { arrRemove } from '@/core/utils.js';

import RegistrationBase from '@/components/access/RegistrationBase.vue';

const featureOptions = [
    'contacts',
    'campaign',
    'crm',
    'projectManagement',
    'eventPlanning',
    'customerProjects',
    'customerServices',
    'billing',
    'sales',
];

export default {
    name: 'RegistrationCustom',
    components: {
        RegistrationBase,
    },
    mixins: [
    ],
    props: {
        features: {
            type: Array,
            required: true,
        },
    },
    emits: [
        'update:features',
        'nextStep',
        'previousStep',
    ],
    data() {
        return {
            noImages: [],
            showPrevious: true,
        };
    },
    computed: {
        sortedFeatures() {
            return _.sortBy(this.featureOptions, (option) => {
                return this.$t(this.getTranslation(option, 'title'));
            });
        },
    },
    methods: {
        emitEvent(event) {
            this.$emit('update:features', event);
        },
        getTranslation(feature, text) {
            return `registration.features.lists.${feature}.${text}`;
        },
        imageSource(feature) {
            return `/images/thumbnails/${feature}.png` || '';
        },
        hasNoImage(feature) {
            return this.noImages.includes(feature);
        },
        notShowing(feature) {
            this.noImages.push(feature);
        },
        isSelected(feature) {
            return this.features.includes(feature);
        },
        toggleFeature(feature) {
            let selectedFeatures = _.clone(this.features);
            if (this.isSelected(feature)) {
                selectedFeatures = arrRemove(selectedFeatures, feature);
            } else {
                selectedFeatures.push(feature);
            }
            this.$emit('update:features', selectedFeatures);
        },
        resetFeatures() {
            this.$emit('update:features', []);
        },
        previousStep() {
            this.resetFeatures();
            this.showPrevious = false;
            this.$emit('previousStep', 'custom');
        },
    },
    created() {
        this.featureOptions = featureOptions;
    },
};
</script>

<style scoped>

.o-registration-custom {
    @apply
        min-h-screen
    ;

    &__features {

        @apply
            flex
            flex-wrap
        ;
    }

    &__item {
        @apply
            p-2
            w-full
        ;

        @media (min-width: 600px) {
            @apply
                w-1/2
            ;
        }

        @media (min-width: 900px) {
            @apply
                w-1/3
            ;
        }

        @media (min-width: 1200px) {
            @apply
                w-1/4
            ;
        }

        @media (min-width: 1300px) {
            @apply
                w-1/5
            ;
        }
    }

    &__feature {
        @apply
            border
            border-gray-300
            flex
            flex-col
            h-full
            items-center
            relative
            rounded-xl
            w-full
        ;

        &--selected {
            @apply
                border-turquoise-600
                shadow-center
            ;
        }

        &:hover {
            @apply
                shadow-center-dark
            ;
        }
    }

    &__check {
        right: 10px;
        top: 10px;

        @apply
            absolute
            text-2xl
        ;
    }

    &__wrap {
        @apply
            flex
            justify-center
        ;
    }

    &__placeholder {
        height: 120px;

        @apply
            bg-gray-200
            rounded-t-xl
            w-full
        ;
    }

    &__image {
        height: 120px;
    }

    &__title {
        @apply
            font-semibold
            mb-2
        ;
    }

    &__paragraph {
        @apply
            text-gray-600
            text-sm
        ;
    }
}

</style>
