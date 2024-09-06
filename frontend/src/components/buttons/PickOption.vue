<template>
    <div class="c-pick-option">
        <button
            type="button"
            class="c-pick-option__option"
            :class="isSelected ? 'bg-primary-700 font-semibold text-cm-00' : 'hover:bg-cm-300'"
            @click="$emit('setOption')"
        >
            <slot></slot>
            <i
                v-show="isUsedElsewhere"
                class="fas fa-star-of-life self-start text-xxxs"
            >
            </i>
        </button>

        <slot
            name="extra"
        >
        </slot>
    </div>
</template>

<script>

export default {
    name: 'PickOption',
    components: {

    },
    mixins: [
    ],
    props: {
        isSelected: Boolean,
        isUsedElsewhere: Boolean,
    },
    emits: [
        'setOption',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>
.c-pick-option {
    @apply
        flex
        items-center
    ;

    &__option {
        @apply
            flex
            px-2
            py-1
            rounded-full
            text-sm
        ;
    }
}
</style>
