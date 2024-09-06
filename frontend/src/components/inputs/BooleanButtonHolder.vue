<template>
    <div
        class="c-boolean-button-holder flex"
        :class="[holderClasses, { 'no-pointer': deactivated }]"
    >
        <!-- DISABLED PROP NEEDS REPLACED WITH DEACTIVATED, ONCE BUTTONS ARE CHANGED -->
        <component
            :ref="componentType"
            :is="componentType"
            class="mr-3"
            :class="{ 'opacity-25': deactivated }"
            indicatorClass="c-boolean-button-holder__indicator--hover"
            :disabled="deactivated"
            :size="size"
            v-bind="$attrs"
        >
        </component>
        <span
            class="c-boolean-button-holder__label"
            :class="[textSize, { 'opacity-50': deactivated }]"
            @click.stop="clickNative"
        >
            <slot>
            </slot>
        </span>
    </div>
</template>

<script>
const components = {
    check: 'CheckButton',
    toggle: 'ToggleButton',
};

export default {
    name: 'BooleanButtonHolder',
    props: {
        deactivated: Boolean,
        size: {
            type: String,
            default: 'base',
        },
        holderClasses: {
            type: String,
            default: '',
        },
        buttonType: {
            type: String,
            default: 'check',
            validator: (type) => Object.keys(components).includes(type),
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
        componentType() {
            return components[this.buttonType];
        },
    },
    methods: {
        clickNative() {
            this.$refs[this.componentType].$el.click();
        },
    },
};
</script>

<style scoped>
.c-boolean-button-holder {
    &__label {
        @apply
            cursor-pointer
            flex-1
            leading-tight
            max-w-full
            min-w-0
        ;
    }
}
</style>
