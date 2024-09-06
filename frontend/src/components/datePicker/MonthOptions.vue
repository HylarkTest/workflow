<template>
    <div class="c-month-options flex flex-wrap text-sm text-cm-700">
        <button
            v-for="month in months"
            :key="month"
            class="c-month-options__option"
            :class="{ 'c-month-options__option--selected': month === viewedMonth }"
            type="button"
            @click="selectMonth(month)"
        >
            {{ $dayjs().month(month).format('MMM') }}
        </button>
    </div>
</template>

<script>

export default {
    name: 'MonthOptions',
    components: {

    },
    mixins: [
    ],
    props: {
        viewedMonth: {
            type: Number,
            required: true,
        },
    },
    emits: [
        'selectMonth',
    ],
    data() {
        return {

        };
    },
    computed: {
        months() {
            return _.range(0, 12);
        },
    },
    methods: {
        selectMonth(month) {
            this.$emit('selectMonth', month);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-month-options {
    &__option {
        @apply
            block
            px-3
            py-1
            rounded-lg
            text-center
            w-1/3
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
                    bg-primary-500
                ;
            }

        }
    }
}

</style>
