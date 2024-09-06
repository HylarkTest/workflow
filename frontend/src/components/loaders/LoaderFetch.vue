<template>
    <div
        class="c-loader-fetch flex"
        :class="containerClass"
    >
        <div
            class="c-loader-fetch__left c-loader-fetch__ball"
            :class="bgColorClass"
            :style="sphereStyle"
        >

        </div>
        <div
            class="c-loader-fetch__middle c-loader-fetch__ball"
            :class="bgColorClass"
            :style="sphereStyle"
        >

        </div>
        <div
            class="c-loader-fetch__right c-loader-fetch__ball"
            :class="bgColorClass"
            :style="sphereStyle"
        >

        </div>
    </div>
</template>

<script>

export default {
    name: 'LoaderFetch',
    components: {

    },
    mixins: [
    ],
    props: {
        isFull: Boolean,
        sphereSize: {
            type: Number,
            default: 70,
        },
        bgColorClass: {
            type: String,
            default: 'bg-primary-200',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        containerClass() {
            return {
                'c-loader-fetch--full': this.isFull,
            };
        },
        sphereStyle() {
            const size = `${this.sphereSize}px`;
            return {
                height: size,
                width: size,
            };
        },

    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-loader-fetch {
    &--full {
        @apply
            h-full
            items-center
            justify-center
            w-full
        ;
    }

    &__ball {
        @apply
            rounded-full
        ;
    }

    &__left {
        animation: ballLeft 1s ease-in infinite;
    }

    &__middle {
        animation: moveX 1s linear infinite;
    }

    &__right {
        animation: ballRight 1s ease-in infinite;
    }

    @keyframes ballLeft {
        0%,50%,100%  {
            transform: translate(0);
        }
        75% {
            transform: translate(-70%);
        }
    }

    @keyframes ballRight {
        0%,50%,100%  {
            transform: translate(0);
        }
        25% {
            transform: translate(70%);
        }
    }

    @keyframes moveX {
        25% {
            transform: translate(70%);
        }
        50% {
            transform: translate(0);
        }
        75% {
            transform: translate(-70%);
        }
        100% {
            transform: translate(0);
        }
    }

}

</style>
