<template>
    <div class="c-steps-display">
        <div
            v-for="(step, index) in steps"
            :key="step"
            class="c-steps-display__step"
            :class="stepClass(index)"
        >
            <div
                class="c-steps-display__circle circle-center"
                :class="circleClass(index)"
            >
                <i
                    :class="stepIcon(index)"
                >
                </i>
                <!-- <i
                    class="fal"
                    :class="step.icon"
                >
                </i> -->
            </div>
            <p
                v-t="'registration.steps.' + step.val"
                class="c-steps-display__name"
            >
            </p>
        </div>
    </div>
</template>

<script>

export default {
    name: 'StepsDisplay',
    components: {

    },
    mixins: [
    ],
    props: {
        steps: {
            type: Array,
            required: true,
        },
        currentStep: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        stepsLength() {
            return this.steps.length;
        },
    },
    methods: {
        circleClass(index) {
            if (index <= this.currentStep) {
                return 'c-steps-display__circle--done';
            }
            return '';
        },
        stepClass(index) {
            if (index <= this.currentStep) {
                return 'c-steps-display__step--done';
            }
            return '';
        },
        stepIcon(index) {
            if (index < this.currentStep) {
                return 'fas fa-check c-steps-display__check';
            }
            if (index === this.currentStep) {
                return 'fas fa-circle c-steps-display__current';
            }
            return '';
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-steps-display {
    @apply
        flex
    ;

    &__step {
        @apply
            flex
            flex-col
            items-center
            justify-end
            px-4
            relative
        ;

        &::before,
        &::after {
            height: 2px;
            top: 10px;

            @apply
                absolute
                bg-cm-200
                block
                w-1/4
                -z-1
            ;
        }

        &:not(:first-child)::before {
            content: "";
            left: 0;
        }

        &:not(:last-child)::after {
            content: "";
            right: 0;
        }
    }

    &__circle {
        height: 10px;
        width: 10px;

        @apply
            bg-cm-300
            mb-3
            text-cm-00
        ;

        &--done {
            height: 21px;
            width: 21px;

            @apply
                bg-primary-600
                mb-2
            ;
        }
    }

    &__name {
        @apply
            text-cm-700
            text-xs
        ;
    }

    &__check {
        font-size: 11px;
    }

    &__current {
        font-size: 9px;
    }
}

@media (min-width: 640px) {
    .c-steps-display__step {
        @apply
            px-8
        ;
    }
}

</style>
