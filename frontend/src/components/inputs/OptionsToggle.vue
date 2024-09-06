<template>
    <div class="c-options-toggle">
        <button
            v-for="option in options"
            :key="option"
            class="c-options-toggle__option"
            :class="selectedClass(option)"
            type="button"
            @click="$emit('update:modelValue', option)"
        >
            {{ getDisplay(option) }}
        </button>
    </div>
</template>

<script>

export default {
    name: 'OptionsToggle',
    components: {
    },
    mixins: [
    ],
    props: {
        options: {
            type: Array,
            required: true,
        },
        display: {
            type: [Function, String, Array],
            default: _.identity,
        },
        modelValue: {
            type: String,
            required: true,
        },
    },
    emits: [
        'update:modelValue',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        getDisplay(option) {
            const fn = _.iteratee(this.display);
            return fn(option);
        },
        selectedClass(option) {
            if (option === this.modelValue) {
                return 'bg-primary-100 text-primary-600 font-semibold';
            }
            return '';
        },
    },
    created() {
    },
};
</script>

<style scoped>

.c-options-toggle {
    @apply
        border
        border-primary-600
        border-solid
        p-1
        rounded
        text-sm
    ;

    &__option {
        @apply
            p-2
        ;
    }
}

</style>
