<template>
    <div
        v-blur="closeYears"
        class="c-year-viewed-options"
        :class="{ unclickable: !isYearInRange }"
    >
        <button
            ref="yearInitiator"
            class="c-year-viewed-options__selector text-xssm md:text-sm"
            type="button"
            @click="toggleYears"
        >
            {{ viewedYear }}
        </button>

        <YearOptions
            v-if="showYears"
            :viewedYear="viewedYear"
            :activator="$refs.yearInitiator"
            :maxYear="maxYear"
            :minYear="minYear"
            @selectYear="selectYear"
        >
        </YearOptions>
    </div>
</template>

<script>

import YearOptions from './YearOptions.vue';

export default {
    name: 'YearViewedOptions',
    components: {
        YearOptions,
    },
    mixins: [
    ],
    props: {
        viewedYear: {
            type: Number,
            required: true,
        },
        maxYear: {
            type: Number,
            default: 2030,
        },
        minYear: {
            type: Number,
            default: 1900,
        },
    },
    emits: [
        'selectYear',
    ],
    data() {
        return {
            showYears: false,
        };
    },
    computed: {
        isYearInRange() {
            return (this.viewedYear >= this.minYear)
                && (this.viewedYear <= this.maxYear);
        },
    },
    methods: {
        toggleYears() {
            if (this.isYearInRange) {
                this.showYears = !this.showYears;
            }
        },
        closeYears() {
            this.showYears = false;
        },
        selectYear(year) {
            this.$emit('selectYear', year);
            this.closeYears();
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-year-viewed-options {
    &__selector {
        @apply
            bg-primary-800
            font-semibold
            px-3
            py-1
            rounded-lg
            text-cm-00
        ;
    }
}

</style>
