<template>
    <div
        class="c-check-holder flex"
        :class="[holderClasses, { 'no-pointer opacity-25': disabled }]"
    >
        <CheckButton
            ref="checkButton"
            class="mr-3"
            indicatorClass="c-check-holder__indicator--hover"
            :disabled="disabled"
            :size="size"
            v-bind="$attrs"
        >
        </CheckButton>
        <span
            class="c-check-holder__label cursor-pointer leading-tight flex-1 max-w-full min-w-0"
            :class="textSize"
            @click.stop="clickNative"
        >
            <slot>
            </slot>
        </span>
    </div>
</template>

<script>

export default {
    name: 'CheckHolder',
    components: {

    },
    mixins: [
    ],
    props: {
        disabled: Boolean,
        size: {
            type: String,
            default: 'base',
        },
        holderClasses: {
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        textSize() {
            return `text-${this.size}`;
        },
    },
    methods: {
        clickNative() {
            this.$refs.checkButton.$el.click();
        },
    },
};
</script>

<style>
.c-check-holder {
    &:hover .c-check-holder__indicator--hover {
        @apply border-cm-700;
    }
}
</style>
