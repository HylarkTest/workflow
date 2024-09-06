<template>
    <YesAndNo
        class="c-when-merge"
        :yeses="yeses"
        :nos="nos"
        pathKey="mergeBlueprint"
    >
        <template
            #header
        >
            When to merge?
        </template>

        <template
            #yesHeader
        >
            Merge when...
        </template>

        <template
            #noHeader
        >
            Do not merge if...
        </template>
    </YesAndNo>
</template>

<script>

import YesAndNo from '@/components/display/YesAndNo.vue';

const yeses = [
    'appearBoth',
    'context',
    'similar',
];

const nos = [
    'distinct',
    'neverBoth',
];

export default {
    name: 'WhenMerge',
    components: {
        YesAndNo,
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
        this.yeses = yeses;
        this.nos = nos;
    },
};
</script>

<style scoped>

/*.c-when-merge {

} */

</style>
