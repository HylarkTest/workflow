<template>
    <div
        class="c-toggle-holder flex"
        :class="[holderClasses, { 'no-pointer opacity-25': disabled }]"
    >
        <ToggleButton
            ref="toggleButton"
            class="mr-5"
            indicatorClass="c-toggle-holder__indicator--hover"
            :disabled="disabled"
            :size="size"
            v-bind="$attrs"
        >
        </ToggleButton>
        <span
            class="c-toggle-holder__label cursor-pointer flex-1 max-w-full min-w-0"
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
    name: 'ToggleHolder',
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
            this.$refs.toggleButton.$el.click();
        },
    },
};
</script>

<style>
.c-toggle-holder {
    &:hover .c-toggle-holder__indicator--hover {
        @apply border-cm-700;
    }
}
</style>
