<template>
    <div class="o-history-details">
        <component
            :is="hasMore ? 'ButtonEl' : 'div'"
            class="o-history-details__main"
            :class="{ 'hover:bg-cm-100': hasMore }"
            @click="toggleViewMore"
        >
            <p class="text-cm-700">
                {{ change.description }}
            </p>

            <PlusCross
                v-if="hasMore"
                circleComponent="div"
                class="o-history-details__more"
                :isCross="viewingMore"
            >
            </PlusCross>
        </component>
        <div
            v-if="hasMore"
            v-show="viewingMore"
            class="mb-6 mt-1 text-xs"
        >
            <div
                v-if="!!change.before"
                class="o-history-details__container bg-peach-100 mb-2"
                :class="typeStyle[change.type]?.class"
            >
                <label class="o-history-details__label text-peach-900">
                    Previous
                </label>
                <div>
                    {{ change.before }}
                </div>
            </div>
            <div
                v-if="!!change.after"
                class="o-history-details__container bg-emerald-100"
                :class="typeStyle[change.type]?.class"
            >
                <label class="o-history-details__label text-emerald-900">
                    New
                </label>
                <div>
                    {{ change.after }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import PlusCross from '@/components/buttons/PlusCross.vue';

const typeStyle = {
    paragraph: {
        class: 'w-full',
    },
    line: {
        class: 'inline-flex',
    },
};

export default {
    name: 'HistoryDetails',
    components: {
        PlusCross,
    },
    mixins: [
    ],
    props: {
        change: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            viewingMore: false,
        };
    },
    computed: {
        hasMore() {
            return this.change.before || this.change.after;
        },
    },
    methods: {
        toggleViewMore() {
            if (this.hasMore) {
                this.viewingMore = !this.viewingMore;
            }
        },
    },
    created() {
        this.typeStyle = typeStyle;
    },
};
</script>

<style scoped>
.o-history-details {
    &__main {
        @apply
            flex
            items-center
            justify-between
            px-2
            py-0.5
            rounded-lg
        ;
    }

    &__more {
        @apply
            ml-3
        ;
    }

    &__container {
        @apply
            p-5
            relative
            rounded-lg
        ;
    }

    &__label {
        @apply
            absolute
            font-semibold
            left-1
            text-xxs
            top-1
            uppercase
        ;
    }
}
</style>
