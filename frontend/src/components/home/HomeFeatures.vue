<template>
    <div class="o-home-features">
        <h4
            class="font-bold text-2xl text-primary-800 mb-4"
        >
            {{ headerText }}
        </h4>

        <div
            class="o-home-features__features"
        >
            <div
                v-for="feature in featureImages"
                :key="feature"
                class="o-home-features__feature w-full sm:w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/6"
            >
                <RouterLink
                    class="o-home-features__inner shadow-primary-400/40"
                    :to="{ name: feature }"
                >
                    <img
                        class="o-home-features__image"
                        :src="`/images/features/${feature}.png`"
                        :alt="getLabel(feature)"
                    >

                    <div
                        class="opacity-20 bg-primary-500 absolute h-full w-full z-over top-0 right-0 rounded-lg"
                    >
                    </div>

                    <div
                        class="o-home-features__tag"
                    >
                        {{ getLabel(feature) }}
                    </div>

                    <div
                        class="o-home-features__text"
                    >
                        <div class="relative text-center px-1 py-1">
                            <p
                                v-t="featureTextPath(feature)"
                                class="relative z-over text-xssm font-semibold text-primary-800"
                            >
                            </p>
                            <div
                                class="opacity-80 h-full w-full rounded bg-cm-00 top-0 right-0 absolute"
                            >

                            </div>
                        </div>
                    </div>
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<script>

const featureImages = [
    'todos',
    'calendar',
    'links',
    'pinboard',
    'documents',
    'timekeeper',
];

export default {
    name: 'HomeFeatures',
    components: {

    },
    mixins: [
    ],
    props: {
        isFirstTime: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        headerText() {
            if (this.isFirstTime) {
                return this.$t('common.discover');
            }

            return this.$t('home.whatDo');
        },
    },
    methods: {
        getLabel(feature) {
            return this.$t(`labels.${feature}`);
        },
        featureTextPath(feature) {
            return `home.features.${feature}.prompt`;
        },
    },
    created() {
        this.featureImages = featureImages;
    },
};
</script>

<style scoped>

.o-home-features {

    &__features {
        @apply
            flex
            flex-wrap
            justify-center
            -m-2
        ;

        /*grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        @apply
            gap-4
            grid
        ;*/
    }

    &__feature {
        @apply
            p-2
        ;
    }

    &__inner {
        transition: 0.2s ease-in-out;

        @apply
            flex
            justify-center
            overflow-hidden
            relative
            rounded-lg
        ;

        &:hover {
            @apply
                shadow-lg
            ;

            .o-home-features__image {
                transform: scale(1.2);
            }
        }
    }

    &__image {
        transition: 0.2s ease-in-out;

        @apply
            h-28
            object-cover
            rounded-lg
            w-full
        ;
    }

    &__tag {
        @apply
            absolute
            bg-primary-100
            font-bold
            px-2
            py-0.5
            right-0
            rounded-bl-lg
            rounded-tr-lg
            text-primary-700
            text-xxsxs
            top-0
        ;
    }

    &__text {
        @apply
            absolute
            bottom-1.5
            px-1.5
            w-full
            z-cover
        ;
    }
}

</style>
