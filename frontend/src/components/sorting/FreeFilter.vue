<template>
    <div
        class="c-free-filter"
    >
        <InputBox
            class="w-full"
            :modelValue="modelValue"
            shape="rounded"
            :bgColor="bgColor"
            boxShape="oval"
            data-form-type="other"
            :showClear="true"
            :size="size"
            :icon="finderIcon"
            :placeholder="freePlaceholder"
            :highlightIconOnFocus="true"
            @update:modelValue="updateValue"
        >
        </InputBox>
        <!-- <i
            class="far fa-search mr-3 o-free-filter__icon text-cm-400"
            :class="{ 'text-primary-600': inFocus }"
            aria-hidden="true"
        ></i>
        <input
            class="o-free-filter__input"
            :placeholder="freePlaceholder"
            :value="modelValue"
            @focus="toggleFocus(true)"
            @blur="toggleFocus(false)"
            @input="updateValue"
        />
        <button
            class="circle-center o-free-filter__clear"
            :class="modelValue.length ? 'opacity-100' : 'opacity-0'"
            @click.stop="$emit('update:modelValue', '')"
        >
            <i class="far fa-times"></i>
        </button> -->
    </div>
</template>

<script>
export default {
    name: 'FreeFilter',
    components: {

    },
    mixins: [
    ],
    props: {
        modelValue: {
            type: String,
            default: '',
        },
        freePlaceholder: {
            type: String,
            default: 'Search by name',
        },
        bgColor: {
            type: String,
            default: 'gray',
        },
        size: {
            type: String,
            default: 'base',
            validator(val) {
                return ['lg', 'base', 'sm', 'md'].includes(val);
            },
        },
    },
    emits: [
        'update:modelValue',
        'setFocus',
    ],
    data() {
        return {
            inFocus: false,
        };
    },
    computed: {
        finderIcon() {
            return {
                symbol: 'fal fa-search',
                component: 'div',
                position: 'left',
            };
        },
    },
    methods: {
        updateValue(e) {
            this.$emit('update:modelValue', e);
        },
        toggleFocus(focus) {
            this.inFocus = focus;
            this.$emit('setFocus', focus);
        },
    },
    created() {
    },
};
</script>

<style scoped>
.c-free-filter {
    @apply
        flex
        items-center
        relative
        text-sm
    ;

    &__icon {
        transition: color 0.2s ease-in-out;
    }

    &__input {
        @apply
            flex-1
            pr-8
        ;
    }

    &__clear {
        transition: opacity 0.2s ease-in-out;

        @apply
            bg-primary-600
            cursor-pointer
            h-20p
            text-13p
            text-cm-00
            w-20p
        ;
    }
}
</style>
