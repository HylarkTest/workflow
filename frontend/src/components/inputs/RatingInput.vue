<template>
    <div
        v-blur="closeEditRating"
        class="c-rating-input"
    >
        <div
            v-if="version === 1"
            class="flex"
        >
            <button
                v-for="star in denominator"
                :key="star"
                :class="{ 'pointer-events-none': disabled }"
                type="button"
                @mouseover="changeHover(star, true)"
                @mouseleave="changeHover(star, false)"
                @click="emitRating(star)"
            >
                <i
                    class="fa-star"
                    :class="iconClass(star)"
                >
                </i>
            </button>
        </div>

        <div
            v-if="version === 2"
            ref="rating"
            @click="toggleEditRating"
        >
            <span
                class="text-xl"
                :class="modelValue ? 'font-bold text-primary-600' : 'font-semibold text-cm-400'"
            >
                {{ modelValue }}
            </span>
            <span class="text-sm text-cm-400 font-semibold">
                / {{ maxRating }}
            </span>
        </div>

        <PopupBasic
            v-if="showEditRating"
            :activator="$refs.rating"
            :right="true"
            nudgeRightProp="0.375rem"
        >
            <div class="flex flex-col items-center">
                <button
                    v-for="rating in denominator"
                    :key="rating"
                    class="px-3 py-1 hover:bg-cm-100 block text-sm w-full"
                    type="button"
                    @click="emitRating(rating)"
                >
                    {{ rating }}
                </button>
            </div>
        </PopupBasic>
    </div>
</template>

<script>
export default {
    name: 'RatingInput',
    props: {
        version: {
            type: Number,
            default: 1,
        },
        maxRating: {
            type: Number,
            default: 5,
        },
        disabled: Boolean,
        modelValue: {
            type: Number,
            default: 0,
        },
    },
    emits: [
        'update:modelValue',
    ],
    data() {
        return {
            hoveredStar: null,
            showEditRating: false,
        };
    },
    computed: {
        denominator() {
            const max = this.maxRating + 1;
            return _.range(1, max);
        },
    },
    methods: {
        changeHover(star, state) {
            if (!this.disabled) {
                this.hoveredStar = state ? star : null;
            }
        },
        emitRating(star) {
            if (!this.disabled) {
                this.$emit('update:modelValue', star);
            }
        },
        toggleEditRating() {
            if (!this.disabled) {
                this.showEditRating = !this.showEditRating;
            }
        },
        closeEditRating() {
            this.showEditRating = false;
        },
        iconClass(val) {
            if (!this.disabled && val <= this.hoveredStar) {
                return 'fas text-primary-600 opacity-40';
            }
            if (val <= this.modelValue) {
                return 'fas text-primary-600';
            }
            return 'far text-cm-300';
        },
    },
};
</script>

<style scoped>

/*.c-rating-input {

} */

</style>
