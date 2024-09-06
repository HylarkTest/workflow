<template>
    <!-- <div
        class="c-landing-burger cursor-pointer"
        :class="{ 'c-landing-burger--active': isActive }"
    > -->
    <div
        class="c-landing-burger cursor-pointer"
        :class="burgerClass"
    >
        <svg
            viewBox="0 0 100 100"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                class="c-landing-burger__line c-landing-burger__a"
                d="M5,50 l90,0"
            />
            <path
                class="c-landing-burger__line c-landing-burger__b"
                d="M5,90 l0,0 l90,0"
            />
            <path
                class="c-landing-burger__line c-landing-burger__c"
                d="M5,10 l90,0"
            />
        </svg>
    </div>
</template>

<script>

export default {
    name: 'LandingBurger',
    components: {

    },
    mixins: [
    ],
    props: {
        isActive: Boolean, // Or null
    },
    data() {
        return {
        };
    },
    computed: {
        burgerClass() {
            // Using null to prevent the animation from being triggered when resizing or on page load
            if (_.isBoolean(this.isActive)) {
                return this.isActive ? 'c-landing-burger--active' : 'c-landing-burger--closed';
            }
            return '';
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-landing-burger {
    --time: 0.3s;

    height: 22px;
    width: 22px;

    &__line {
        fill: none;
        stroke-linecap: round;
        stroke-width: 10;

        @apply
            stroke-current
            text-azure-600
        ;
    }

    .c-landing-burger__a {
        transition: d var(--time) linear;
    }

    &--closed {
        .c-landing-burger__b {
            animation: linear var(--time) b-reverse;
            d: path("M5,90 l90,0 l0,0");
        }

        @keyframes b-reverse {
            0% {
                d: path("M5,90 l0,0 L95,10");
            }

            50% {
                d: path("M5,90 l65,0 L95,50");
            }

            100% {
                d: path("M5,90 l90,0 l0,0 ");
            }
        }

        .c-landing-burger__c {
            animation: linear var(--time) c-reverse;
            d: path("M5,10 l90,0 l0,0 ");
        }

        @keyframes c-reverse {
            0% {
                d: path("M5,10 l0,0 L95,90");
            }

            50% {
                d: path("M5,10 l65,0 L95,50");
            }

            100% {
                d: path("M5,10 l90,0 l0,0 ");
            }
        }
    }

    &--active {
        .c-landing-burger__a {
            d: path("M50,50 L50,50");
        }

        .c-landing-burger__b {
            animation: linear var(--time) b-path;
            d: path("M5,10 L95,90 l0,0");
        }

        @keyframes b-path {
            0% {
                d: path("M5,90 l0,0 l90,0");
            }

            50% {
                d: path("M5,50 l30,40 L95,90");
            }

            100% {
                d: path("M5,10 L95,90 l0,0");
            }
        }

        .c-landing-burger__c {
            animation: linear var(--time) c-path;
            d: path("M5,90 L95,10 l0,0");
        }

        @keyframes c-path {
            0% {
                d: path("M5,10 l0,0 l90,0");
            }

            50% {
                d: path("M5,50 l30,-40 L95,10");
            }

            100% {
                d: path("M5,90 L95,10 l0,0");
            }
        }

    }
}

</style>
