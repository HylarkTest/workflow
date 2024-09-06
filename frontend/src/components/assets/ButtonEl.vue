<template>
    <component
        :is="component"
        class="c-button-el"
        tabindex="0"
        role="button"
        aria-pressed="false"
    >
        <slot>
        </slot>
    </component>
</template>

<script>

export default {
    props: {
        component: {
            type: String,
            default: 'div',
        },
    },
};

</script>
