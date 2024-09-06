<template>
    <div class="c-loader-main center">

        <div class="c-loader-main__loader">
        </div>
        <div class="c-loader-main__sub">

        </div>
        <img
            class="c-loader-main__image"
            :src="'/images/logos/hylarkCircle.svg'"
        />
    </div>
</template>

<script>

export default {
    name: 'LoaderMain',
    components: {

    },
    mixins: [
    ],
    props: {

    },
    data() {
        return {

        };
    },
    computed: {
    },
    methods: {

    },
    created() {
    },
};
</script>

<style>

.c-loader-main {
    @apply
        bg-cm-00
        fixed
        h-screen
        right-0
        top-0
        w-screen
        z-modal
    ;

    &__image {
        animation-duration: 1.5s;
        animation-iteration-count: infinite;
        animation-name: pulse;
        animation-timing-function: ease-in-out;
        height:  66px;
        width:  66px;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
        }
    }

/*    stylelint-disable*/

    &__loader {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        display: inline-block;
        border-top-style: solid;
        border-top-width: 8px;
        border-right: 4px solid transparent;
        animation: rotation 2s linear infinite;

        @apply
            absolute
            border-azure-600
        ;
    }

    &__sub {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        border-left: 8px solid;
        border-bottom: 4px solid transparent;
        animation: rotation 1s linear infinite reverse;

        @apply
            absolute
            border-gold-500
        ;
    }

    @keyframes rotation {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
}

</style>
