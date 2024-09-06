<template>
    <div class="c-tip-tap-button relative">
        <ButtonEl
            class="rounded-md relative overflow-hidden"
            :class="{ unclickable: isDeactivated }"
            :title="option.text"
            @click="$emit('handleClick')"
        >
            <div
                class="c-tip-tap-button__button relative"
                :class="buttonClasses"
            >
                <slot name="button">
                    <i :class="option.icon"></i>
                    <div
                        class="absolute w-full h-full opacity-50"
                        :style="{ backgroundColor: backgroundColor }"
                    >
                    </div>
                </slot>
            </div>
        </ButtonEl>

        <slot name="popup"></slot>
    </div>
</template>

<script>
export default {
    name: 'TipTapButton',
    props: {
        option: {
            type: Object,
            required: true,
        },
        backgroundColor: {
            type: [String, null],
            default: null,
        },
        buttonClass: {
            type: [String, null],
            default: null,
        },
        isActive: Boolean,
        isDeactivated: Boolean,
    },
    emits: [
        'handleClick',
    ],
    computed: {
        buttonClasses() {
            const classes = [
                this.buttonClass || 'w-7',
            ];
            if (!this.backgroundColor && this.isActive) {
                classes.push('c-tip-tap-button__button--active');
            }
            return classes;
        },
    },
};
</script>

<style scoped>
.c-tip-tap-button {
    &__button {
        box-sizing: border-box;

        @apply
            border
            flex
            h-7
            items-center
            justify-center
            px-2
            py-1
            rounded-md
        ;

        &:hover {
            @apply
                bg-cm-100
                border-cm-300
                border-solid
                cursor-pointer
            ;
        }

        &--active {
            @apply
                bg-primary-200
                hover:bg-primary-300
        }
    }
}
</style>
