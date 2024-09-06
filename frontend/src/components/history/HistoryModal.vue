<template>
    <Modal
        containerClass="o-history-modal__modal py-8 px-6"
        positioning="TOP"
        v-bind="$attrs"
    >
        <HistoryList
            :showLoader="true"
            :showFilters="true"
            :pageType="pageType"
            :globalId="globalId"
            topStickyClass="top-0"
            showFullFirstPageInitially
        >
        </HistoryList>
    </Modal>
</template>

<script>

import HistoryList from './HistoryList.vue';

export default {
    name: 'HistoryModal',
    components: {
        HistoryList,
    },
    mixins: [
    ],
    props: {
        pageType: {
            type: String,
            default: '',
        },
        globalId: {
            type: String,
            default: '',
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

<style>

.o-history-modal {
    &__modal {
        max-width:  700px;
        width: 700px;
    }
}

</style>
