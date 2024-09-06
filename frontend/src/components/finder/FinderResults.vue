<template>
    <div class="o-finder-results">
        <div
            v-for="result in results"
            :key="result.id"
            class="o-finder-results__result"
        >
            <FinderResult
                :result="result"
                @closeModal="$emit('closeModal')"
            >
            </FinderResult>

        </div>
    </div>
</template>

<script>

import FinderResult from './FinderResult.vue';

export default {
    name: 'FinderResults',
    components: {
        FinderResult,
    },
    mixins: [
    ],
    props: {
        results: {
            type: Array,
            required: true,
        },
    },
    emits: [
        'closeModal',
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
};
</script>

<style scoped>

.o-finder-results {
    &__result {
        @apply
            odd:bg-primary-100
            px-2
            py-2
            rounded-lg
        ;
    }
}

</style>
