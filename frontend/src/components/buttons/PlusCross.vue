<template>
    <component
        :is="circleComponent"
        class="c-plus-cross circle-center"
        :class="[colors, isCross ? 'c-plus-cross--cross' : 'c-plus-cross--plus']"
    >

        <i
            class="far fa-plus"
        >
        </i>
    </component>
</template>

<script>
export default {

    name: 'PlusCross',
    props: {
        isCross: Boolean,
        crossColorClasses: {
            type: String,
            default: 'text-primary-600 bg-cm-200 hover:bg-cm-300',
        },
        plusColorClasses: {
            type: String,
            default: 'text-cm-00 bg-primary-600 hover:bg-primary-500',
        },
        circleComponent: {
            type: String,
            default: 'button',
        },
    },
    computed: {
        colors() {
            return this.isCross ? this.crossColorClasses : this.plusColorClasses;
        },
        // conditionalColor() {
        //     return this.isCross && this.secondaryColorClass;
        // },
    },
};
</script>

<style scoped>
.c-plus-cross {
    animation-duration: 0.2s;
    animation-fill-mode: both;
    animation-name: open;
    height: 18px;
    line-height: 12px;
    min-width: 18px;
    position: relative;
    transition: 0.2s ease-in-out;
    width: 18px;

    &--cross {
        animation-name: close;
    }
}

@keyframes open {
    from {
        transform: rotate3d(0, 0, 1, 135deg);
        transform-origin: center;
    }

    to {
        transform: none;
        transform-origin: center;
    }
}

@keyframes close {
    from {
        transform: none;
        transform-origin: center;
    }

    to {
        transform: rotate3d(0, 0, 1, 135deg);
        transform-origin: center;
    }
}

</style>
