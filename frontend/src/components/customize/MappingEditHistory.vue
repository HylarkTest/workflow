<template>
    <div class="o-mapping-edit-history">
        <HistoryList
            :globalId="mapping.id"
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
    name: 'MappingEditHistory',
    components: {
        HistoryList,
    },
    mixins: [
    ],
    props: {
        mapping: {
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

/*.o-mapping-edit-history {

} */

</style>
