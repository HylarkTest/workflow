<template>
    <div class="c-loader-processing">
        <div class="c-loader-processing__loader">
            <div
                class="c-loader-processing__circle c-loader-processing__circle1"
                :class="[heightWidthClass, color1]"
            >

            </div>

            <div
                class="c-loader-processing__circle c-loader-processing__circle2"
                :class="[heightWidthClass, color2]"
            >

            </div>
            <div
                class="c-loader-processing__circle c-loader-processing__circle3"
                :class="[heightWidthClass, color1]"
            >

            </div>
            <div
                class="c-loader-processing__circle c-loader-processing__circle4"
                :class="[heightWidthClass, color2]"
            >

            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: 'LoaderProcessing',
    components: {

    },
    mixins: [
    ],
    props: {
        heightWidthClass: {
            type: String,
            default: 'h-10 w-10',
        },
        colorClass1: {
            type: String,
            default: 'bg-primary-600',
        },
        colorClass2: {
            type: String,
            default: 'bg-secondary-600',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        color1() {
            return this.colorClass1;
        },
        color2() {
            return this.colorClass2;
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

.c-loader-processing {

    &__loader {
        animation-duration: 1.2s;
        animation-iteration-count: infinite;
        animation-name: rotate;
        transform-origin: center center;

        @apply
            flex
            items-center
            justify-center
            relative
        ;
    }

    &__circle {
        animation-duration: 1.2s;
        animation-iteration-count: infinite;

        @apply
            absolute
            rounded-full
        ;
    }

    &__circle1 {
        animation-name: circle1;
    }

    &__circle2 {
        animation-name: circle2;
    }
    &__circle3 {
        animation-name: circle3;
    }
    &__circle4 {
        animation-name: circle4;
    }

    @keyframes rotate {
        0% { transform: rotate(0deg) scale(0.8) }
        50% { transform: rotate(360deg) scale(1) }
        100% { transform: rotate(720deg) scale(0.8) }
    }

    @keyframes circle1 {
        0% {
            transform: translate(0, 0);
        }
        50% {
            transform: translate(60%, 60%);
        }
        100% {
            transform: translate(0, 0);
        }
    }
    @keyframes circle2 {
        0% {
            transform: translate(0, 0);
        }
        50% {
            transform: translate(60%, -60%);
        }
        100% {
            transform: translate(0, 0);
        }
    }
    @keyframes circle3 {
        0% {
            transform: translate(0, 0);
        }
        50% {
            transform: translate(-60%, -60%);
        }
        100% {
            transform: translate(0, 0);
        }
    }
    @keyframes circle4 {
        0% {
            transform: translate(0, 0);
        }
        50% {
            transform: translate(-60%, 60%);
        }
        100% {
            transform: translate(0, 0);
        }
    }
}

</style>
