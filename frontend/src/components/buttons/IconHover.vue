r<template>
    <button
        type="button"
        class="c-icon-hover circle-center"
        :class="iconClasses"
        @mouseover="changeHover(true)"
        @mouseleave="changeHover(false)"
    >
        <slot>
            <i
                class="absolute z-over"
                :class="icon"
            >
            </i>
        </slot>
        <span
            class="c-icon-hover__wrap block"
            :class="wrapClass"
        >
        </span>
    </button>
</template>

<script>
export default {

    name: 'IconHover',
    components: {

    },
    mixins: [
    ],
    props: {
        icon: {
            type: String,
            default: 'far fa-pencil-alt',
        },
        iconSize: {
            type: String,
            default: 'sm',
            validator(val) {
                return ['xs', 'sm', 'lg', 'xl'].includes(val);
            },
        },
        iconColor: {
            type: String,
            default: 'text-cm-500',
        },
        iconHoverColor: {
            type: String,
            default: 'text-cm-00',
        },
        bgHoverColor: {
            type: String,
            default: 'bg-primary-600',
        },
        forceActive: Boolean,
    },
    data() {
        return {
            hoverState: false,
        };
    },
    computed: {
        activeState() {
            return this.forceActive || this.hoverState;
        },
        activeClass() {
            return { 'c-icon-hover--active': this.forceActive };
        },
        iconColorClass() {
            return this.activeState ? this.iconHoverColor : this.iconColor;
        },
        iconSizeClass() {
            return `text-${this.iconSize}`;
        },
        iconClasses() {
            return [this.activeClass, this.iconColorClass, this.iconSizeClass];
        },
        wrapClass() {
            return this.activeState ? this.bgHoverColor : '';
        },
    },
    methods: {
        changeHover(val) {
            if (!this.forceActive) {
                this.hoverState = val;
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>
.c-icon-hover {
    transition: 0.3s ease-in-out;
    @apply
        h-8
        relative
        w-8
        z-0
    ;

    &__wrap {
        transform: scale(1);
        transition: 0.3s ease-in-out;

        @apply
            h-0
            left-4
            pointer-events-none
            rounded-full
            top-4
            w-0
        ;
    }

    &:hover .c-icon-hover__wrap {
        @apply
            h-8
            w-8
        ;
    }
}

.c-icon-hover--sm {
    height: 21px;
    width: 21px;
    @apply
        text-xs
    ;

    &:hover {
        .c-icon-hover__wrap {
            height: 21px;
            width: 21px;
        }
    }
}

.c-icon-hover--active {
    .c-icon-hover__wrap {
        @apply
            h-8
            w-8
        ;
    }

    &.c-icon-hover--sm .c-icon-hover__wrap {
        @apply
            h-6
            w-6
        ;
    }
}
</style>
