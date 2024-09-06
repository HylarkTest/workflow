<template>
    <div class="o-registration-base">

        <h2 class="o-registration-base__title">
            <slot name="title">
            </slot>
        </h2>

        <div
            v-if="$slots.subtitle"
            class="mt-3 text-center w-full"
        >
            <p
                class="o-registration-base__subtitle"
            >
                <slot name="subtitle">
                </slot>
            </p>
        </div>

        <div class="mt-10">
            <slot>
            </slot>
        </div>

        <div
            v-if="showPrevious || showNext"
            class="o-registration-base__bottom justify-between"
        >
            <button
                type="button"
                class="o-registration-base__button button-primary"
                :class="{ 'opacity-0': !showPrevious }"
                @click="$emit('previousStep')"
            >
                <i
                    class="fal fa-chevron-double-left mr-2 hidden sm:inline"
                    aria-hidden="true"
                >
                </i>

                {{ $t('common.previous') }}
            </button>

            <slot name="footer">
            </slot>

            <button
                type="button"
                class="o-registration-base__button button-primary"
                :class="showNext ? 'opacity-100' : 'opacity-0 unclickable'"
                @click="$emit('nextStep')"
            >
                {{ $t(nextPath) }}

                <i
                    class="fal fa-chevron-double-right ml-2 hidden sm:inline"
                    aria-hidden="true"
                >
                </i>

            </button>
        </div>
    </div>
</template>

<script>

export default {
    name: 'RegistrationBase',
    components: {
    },
    mixins: [
    ],
    props: {
        showNext: Boolean,
        showPrevious: Boolean,
        detailsOpen: Boolean,
        nextPath: {
            type: String,
            default: 'common.next',
        },
    },
    emits: [
        'nextStep',
        'previousStep',
    ],
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
    mounted() {
    },
};
</script>

<style scoped>

.o-registration-base {
    @apply
        pb-24
        pt-32
        px-10
    ;

    &__title {
        font-size: 30px;

        @apply
            font-semibold
            text-center
        ;
    }

    &__button {
        transition: 0.2s ease-in-out;

        @apply
            font-semibold
            px-3
            py-2
            rounded-md
            text-sm
        ;
    }

    &__subtitle {
        max-width: 600px;

        @apply
            mx-auto
            text-gray-700
            text-sm
        ;
    }

    &__bottom {
        @apply
            bg-white
            bottom-0
            fixed
            flex
            items-center
            left-0
            px-8
            py-3
            w-screen
            z-over
        ;
    }
}

@media (min-width: 640px) {
    .o-registration-base {
        @apply
            px-24
        ;

        &__title {
            font-size: 40px;
        }

        &__button {
            @apply
                px-6
                py-3
                rounded-xl
                text-base
            ;
        }
    }
}

</style>
