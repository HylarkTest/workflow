<template>
    <ButtonEl
        class="c-theres-more"
        :class="[backgroundColor, hoverClass]"
    >
        <div
            v-for="circle in [0, 1, 2]"
            :key="circle"
            class="c-theres-more__circle bg-cm-400"
            :class="circleColor"
        >

        </div>
    </ButtonEl>
</template>

<script>

export default {
    name: 'TheresMore',
    components: {

    },
    mixins: [
    ],
    props: {
        colorTheme: {
            type: String,
            default: 'gray',
            validator(val) {
                return ['gray'].includes(val);
            },
        },
        hoverEffect: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        circleColor() {
            return `c-theres-more__circle--${this.colorTheme}`;
        },
        backgroundColor() {
            return `c-theres-more--${this.colorTheme} bg-cm-200`;
        },
        hoverClass() {
            return this.hoverEffect ? 'c-theres-more--hover' : '';
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-theres-more {
    transition: 0.2s ease-in-out;

    @apply
        flex
        flex-col
        p-1.5
        rounded-md
    ;

    &--gray {
        &:hover {
            @apply
                bg-primary-200
            ;

            .c-theres-more__circle--gray {
                @apply
                    bg-primary-400
                ;
            }
        }

    }

    &__circle {
        @apply
            h-1
            rounded-full
            w-1
        ;

        &:not(:last-child) {
            margin-bottom: 2px;
        }
    }

    &--hover {
        @apply
            bg-primary-200
        ;

        .c-theres-more__circle--gray {
            @apply
                bg-primary-400
            ;
        }
    }
}

</style>
