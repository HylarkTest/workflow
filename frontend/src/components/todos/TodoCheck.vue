<template>
    <button
        class="o-todo-check circle-center transition-2eio"
        :class="[checkClass, sizeClass]"
        type="button"
        @click.stop
        @click="toggleCompletion"
    >
        <i
            class="o-todo-check__tick fas fa-check"
        >
        </i>
    </button>
</template>

<script>

export default {
    name: 'TodoCheck',
    components: {

    },
    mixins: [
    ],
    props: {
        isCompleted: {
            type: [Boolean, String, Date, null],
            default: null,
        },
        size: {
            type: String,
            default: 'base',
            validator(val) {
                return ['sm', 'base'].includes(val);
            },
        },
    },
    emits: [
        'toggleCompletion',
    ],
    data() {
        return {

        };
    },
    computed: {
        checkClass() {
            if (this.isCompleted) {
                return 'o-todo-check--complete';
            }
            return 'o-todo-check--incomplete';
        },
        sizeClass() {
            return `o-todo-check--${this.size}`;
        },
    },
    methods: {
        toggleCompletion() {
            this.$emit('toggleCompletion', this.isCompleted);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-todo-check {
    font-size: 11px;
    height: 18px;
    min-width: 18px;
    width: 18px;

    &--sm {
        font-size: 10px;
        height: 16px;
        min-width: 16px;
        width: 16px;
    }

    @apply
        border
        border-primary-600
        border-solid
    ;

    &--complete {
        @apply
            bg-primary-600
            text-cm-00
        ;

        &:hover {
            @apply
                bg-cm-00
                text-primary-600
            ;
        }
    }

    &--incomplete {
        @apply
            bg-cm-00
            text-primary-600
        ;

        .o-todo-check__tick {
            transition: 0.2s opacity ease-in-out;

            @apply
                opacity-0
            ;
        }

        &:hover .o-todo-check__tick {
            @apply
                opacity-100
            ;
        }
    }
}

</style>
