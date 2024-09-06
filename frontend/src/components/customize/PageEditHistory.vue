<template>
    <div class="o-page-edit-history">
        <HistoryList
            :globalId="page.id"
            :showLoader="true"
            :hasOwnContainer="true"
            showFullFirstPageInitially
        >
        </HistoryList>
    </div>
</template>

<script>

import HistoryList from '@/components/history/HistoryList.vue';

export default {
    name: 'PageEditHistory',
    components: {
        HistoryList,
    },
    mixins: [
    ],
    props: {
        page: {
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

/*.o-page-edit-history {

} */

</style>
