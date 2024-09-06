<template>
    <div
        class="c-displayer-rating inline-flex flex-col"
    >
        <div class="relative">
            <ClearButton
                v-if="showClear"
                @click="saveRating(null)"
            >
            </ClearButton>

            <RatingInput
                :version="combo"
                :modelValue="moddedValue"
                :maxRating="maxRating"
                :disabled="!isModifiable"
                @update:modelValue="saveRating($event, maxRating)"
            >
            </RatingInput>
        </div>
    </div>
</template>

<script>

import ClearButton from '@/components/buttons/ClearButton.vue';
import RatingInput from '@/components/inputs/RatingInput.vue';

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import interactsWithDisplayersSelfEdit from '@/vue-mixins/displayers/interactsWithDisplayersSelfEdit.js';

export default {
    name: 'DisplayerRating',
    components: {
        RatingInput,
        ClearButton,
    },
    mixins: [
        interactsWithDisplayers,
        interactsWithDisplayersSelfEdit,
    ],
    props: {

    },
    emits: [
        'saveField',
        'update:dataValue',
    ],
    data() {
        return {
            typeKey: 'RATING',
            maxRating: 5,
        };
    },
    computed: {
        moddedValue() {
            return !this.modelFieldValue ? 0 : this.modelFieldValue.stars;
        },
        showClear() {
            return this.modelFieldValue && this.isModifiable;
        },
    },
    methods: {
        saveRating(rating) {
            this.saveValue({ stars: rating, max: this.maxRating });
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-rating {

} */

</style>
