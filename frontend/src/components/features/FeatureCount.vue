<template>
    <div class="o-feature-count flex justify-center flex-wrap gap-4">
        <ButtonEl
            v-for="feature in features"
            :key="feature.value"
            class="o-feature-count__panel hover:shadow-secondary-400/20"
            @click="$emit('openViewModal', feature.value)"
        >
            <div class="o-feature-count__icon circle-center">
                <i
                    class="fa-light text-secondary-700"
                    :class="feature.icon"
                >
                </i>
            </div>
            <div class="o-feature-count__context w-full">
                <div class="flex justify-between items-center h-8">
                    <div
                        v-t="featureNamePath(feature.value)"
                        class="text-medium font-medium text-cm-400"
                    >
                    </div>
                    <span
                        v-if="feature.dot"
                        class="o-feature-count__dot"
                    >
                    </span>
                </div>
                <div class="text-xl font-semibold text-secondary-900">
                    {{ feature.count || 0 }}
                </div>
            </div>
        </ButtonEl>
    </div>
</template>

<script>
export default {
    name: 'FeatureCount',
    props: {
        features: {
            type: Array,
            default: () => [],
        },
    },
    emits: [
        'openViewModal',
    ],
    methods: {
        featureNamePath(value) {
            return `features.${_.camelCase(value)}.title`;
        },
    },
};
</script>

<style scoped>
.o-feature-count {
    &__panel {
        height: 80px;
        width: 160px;

        @apply
            bg-secondary-100
            duration-200
            ease-in-out
            flex
            p-3
            rounded-xl
        ;

        &:hover {
            @apply
                shadow-lg
            ;
        }
    }

    &__icon {
        @apply
            bg-secondary-200
            h-8
            mr-2
            w-8
        ;
    }

    &__dot {
        @apply
            bg-primary-400
            block
            h-1.5
            rounded-full
            w-1.5
        ;
    }
}
</style>
