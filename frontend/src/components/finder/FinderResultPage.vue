<template>
    <div class="c-finder-result-page">
        <TrimmedParagraph
            v-if="description"
            class="o-finder-result__description"
            :paragraph="description"
        >
        </TrimmedParagraph>
    </div>
</template>

<script>

import TrimmedParagraph from '@/components/display/TrimmedParagraph.vue';

export default {
    name: 'FinderResultsPage',
    components: {
        TrimmedParagraph,
    },
    mixins: [
    ],
    props: {
        highlightedFields: {
            type: Array,
            required: true,
        },
        node: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        description() {
            return this.alreadyShowsDescription ? null : this.node.description;
        },
        omittedPaths() {
            return this.highlightedFields?.map((field) => {
                return field.path;
            });
        },
        alreadyShowsDescription() {
            return this.omittedPaths?.includes('description');
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-finder-result-page {

} */

</style>
