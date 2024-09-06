<template>
    <PopupBasic
        ref="popup"
        class="c-year-options"
        containerClass="p-1"
        widthProp="5rem"
        :maxHeightProp="height"
        nudgeDownProp="0.125rem"
        :alignCenter="true"
    >
        <button
            v-for="year in yearOptions"
            :ref="setRef(year)"
            :key="year"
            type="button"
            class="c-year-options__option"
            :class="{ 'c-year-options__option--selected': year === viewedYear }"
            @click="selectYear(year)"
        >
            {{ year }}
        </button>
    </PopupBasic>
</template>

<script>
import { remToPx } from '@/core/utils.js';

export default {
    name: 'YearOptions',
    components: {

    },
    mixins: [
    ],
    props: {
        maxYear: {
            type: Number,
            default: 2030,
        },
        minYear: {
            type: Number,
            default: 1900,
        },
        viewedYear: {
            type: [Number, String],
            required: true,
        },
    },
    emits: [
        'selectYear',
    ],
    data() {
        return {
            height: '11.25rem',
            yearRefs: {},
        };
    },
    computed: {
        yearOptions() {
            const max = this.maxYear + 1;
            return _.range(this.minYear, max);
        },
        heightPx() {
            return remToPx(this.height);
        },
    },
    methods: {
        setRef(year) {
            return (el) => {
                this.yearRefs[year] = el;
            };
        },
        selectYear(year) {
            this.$emit('selectYear', year);
        },
        scrollToYear() {
            this.yearRefs[this.viewedYear].scrollIntoView({ block: 'center' });
        },
    },
    mounted() {
        this.$nextTick(() => {
            this.scrollToYear();
        });
    },
    created() {

    },
};
</script>

<style scoped>

.c-year-options {
    &__option {
        @apply
            px-2
            py-1
            rounded-lg
            text-center
            text-smbase
        ;

        &:hover {
            @apply
                bg-primary-100
            ;
        }

        &--selected {
            @apply
                bg-primary-600
                font-semibold
                text-cm-00
            ;

            &:hover {
                @apply
                    bg-primary-600
                ;
            }

        }
    }
}

</style>
