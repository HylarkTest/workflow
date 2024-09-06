<template>
    <div class="o-password-strength">
        <div
            class="flex justify-between items-baseline mb-1"
        >
            <p
                v-t="'registration.initial.password.passwordStrength'"
                class="o-password-strength__strength"
            >

            </p>

            <span
                v-if="text"
                v-t="'registration.initial.password.ratings.' + currentRating"
                class="o-password-strength__rating"
                :class="getTextColor(currentRatingInfo.color, '600')"
            >
            </span>
        </div>

        <div
            class="o-password-strength__bar"
        >
            <div
                class="o-password-strength__fill"
                :class="[getBgColor(currentRatingInfo.color, '600'), fillWidthClass]"
            >

            </div>
            <!-- Password bar -->
        </div>

        <p>
            <!-- Password verdict -->
        </p>
    </div>
</template>

<script>

import providesColors from '@/vue-mixins/style/providesColors.js';

const ratings = {
    poor: {
        color: 'red',
        percentage: '1/5',
    },
    fair: {
        color: 'yellow',
        percentage: '2/5',
    },
    good: {
        color: 'green',
        percentage: '5/6',
    },
    excellent: {
        color: 'blue',
        percentage: 'full',
    },
};

export default {
    name: 'PasswordStrength',
    components: {

    },
    mixins: [
        providesColors,
    ],
    props: {
        text: {
            type: String,
            required: true,
        },
        criteriaMet: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        strength() {
            if (!this.criteriaMet) {
                return {
                    rating: 'poor',
                    message: 'makeItBetter',
                };
            }
            if (this.isCommon) {
                return {
                    rating: 'poor',
                    message: 'isCommon',
                };
            }
            if (this.hasPatterns) {
                return {
                    rating: 'fair',
                    message: 'easilyGuessable',
                };
            }
            if (this.surpasses) {
                return {
                    rating: 'excellent',
                    message: 'excellent',
                };
            }
            return {
                rating: 'good',
                message: 'meetsCriteria',
            };
        },
        currentRating() {
            return this.strength.rating;
        },
        currentRatingInfo() {
            return ratings[this.currentRating];
        },
        fillWidthClass() {
            const width = this.currentRatingInfo.percentage;
            return `w-${width}`;
        },
        hasPatterns() {
            return false;
        },
        isCommon() {
            return false;
        },
        surpasses() {
            return false;
        },
    },
    methods: {

    },
    created() {
    },
};
</script>

<style scoped>

.o-password-strength {
    &__strength {
        @apply
            font-semibold
            mt-2
            text-gray-800
            text-xs
        ;
    }

    &__rating {
        @apply
            font-bold
            mr-2
            text-xs
        ;
    }

    &__bar {
        @apply
            border
            border-gray-400
            border-solid
            h-2
            rounded
            w-full
        ;
    }

    &__fill {
        transition: 0.5s width ease-in-out;

        @apply
            h-full
            rounded
        ;
    }
}

</style>
