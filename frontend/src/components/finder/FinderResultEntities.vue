<template>
    <div class="o-finder-result-entities">
        <FinderResultPage
            :highlightedFields="highlightedFields"
            :node="node"
        >
        </FinderResultPage>
    </div>
</template>

<script>

import FinderResultPage from './FinderResultPage.vue';

export default {
    name: 'FinderResultEntities',
    components: {
        FinderResultPage,
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

    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.o-finder-result-entities {

} */

</style>
