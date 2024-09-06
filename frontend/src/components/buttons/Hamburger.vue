<template>
    <button
        type="button"
        class="c-hamburger"
        :class="active ? 'c-hamburger--active' : 'c-hamburger--off'"
    >
        <div class="c-hamburger__box">
            <div class="c-hamburger__inner">
            </div>
        </div>
    </button>
</template>

<script>
export default {

    name: 'Hamburger',
    props: {
        active: Boolean,
    },
};
</script>

<style scoped>
/* stylelint-disable no-duplicate-selectors */
.c-hamburger {
    --bar-width: 24px;
    cursor: pointer;

    &__box {
        height: 24px;
        position: relative;
        width: var(--bar-width);
    }

    &__inner {
        position: relative;
        top: 50%;
        transition-duration: 0.1s;
        transition-timing-function: cubic-bezier(0.55, 0.055, 0.675, 0.19);

        &,
        &::before,
        &::after {
            background-color: #999;
            border-radius: 4px;
            content: "";
            display: block;
            height: 2px;
            position: absolute;
            transition-timing-function: ease;
            width: var(--bar-width);
        }

        &::before {
            top: -8px;
            transition: background-color 0.2s ease-in-out, top 0.1s 0.14s ease, opacity 0.1s ease;
        }

        &::after {
            bottom: -8px;
            transition: background-color 0.2s ease-in-out,
                bottom 0.1s 0.14s ease,
                transform 0.1s cubic-bezier(0.55, 0.055, 0.675, 0.19);
        }
    }

    &:hover {
        .c-hamburger__inner {
            &,
            &::before,
            &::after {
                @apply
                    bg-primary-600
                ;
            }
        }
    }
}

.c-hamburger--active {
    .c-hamburger__inner {
        transform: rotate(45deg);
        transition-delay: 0.1s;
        transition-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);

        &::before {
            opacity: 0;
            top: 0;
            transition: background-color 0.2s ease-in-out, top 0.1s ease, opacity 0.1s 0.14s ease;
        }

        &::after {
            bottom: 0;
            transform: rotate(-90deg);
            transition: background-color 0.2s ease-in-out,
                bottom 0.1s ease,
                transform 0.1s 0.14s cubic-bezier(0.215, 0.61, 0.355, 1);
        }
    }
}

/* stylelint-enable */
</style>
