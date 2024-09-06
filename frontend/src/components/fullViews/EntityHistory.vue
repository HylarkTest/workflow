<template>
    <div class="o-entity-history mt-6">
        <HistoryList
            :globalId="item.id"
            :showLoader="true"
            showFullFirstPageInitially
            :hasOwnContainer="true"
        >
        </HistoryList>
    </div>
</template>

<script>

import HistoryList from '@/components/history/HistoryList.vue';

export default {
    name: 'EntityHistory',
    components: {
        HistoryList,
    },
    mixins: [
    ],
    props: {
        item: {
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

/*.o-entity-history {

} */

</style>
