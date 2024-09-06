<template>
    <LayoutPage
        class="o-history-page"
        :headerProps="headerProps"
    >
        <div class="flex flex-col flex-1 h-full min-h-full">
            <HistoryList
                class="flex-1 h-full"
                :showFilters="true"
                :pageType="specificPageType"
                :styleWithBg="true"
                showFullFirstPageInitially
            >
            </HistoryList>
        </div>
    </LayoutPage>
</template>

<script>

import HistoryList from '@/components/history/HistoryList.vue';
import LayoutPage from '@/components/layout/LayoutPage.vue';

export default {
    name: 'HistoryPage',
    components: {
        HistoryList,
        LayoutPage,
    },
    mixins: [
    ],
    props: {
        pageType: {
            type: String,
            default: '',
        },
        mappingId: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            headerProps: {
                name: this.$t('links.history'),
                iconProp: 'far fa-list-timeline',
            },
        };
    },
    computed: {
        specificPageType() {
            return this.mappingId ? `${this.pageType}:${this.mappingId}` : this.pageType;
        },
        sortDirection() {
            return _.lowerCase(this.filters.sortOrder.direction);
        },
    },
    methods: {
        orderedActions(actions) {
            return _.orderBy(actions, 'createdAt', this.sortDirection);
        },
        isLastInGroup(activities, index) {
            return index === (activities.length - 1);
        },
    },
    created() {
    },
};
</script>

<style scoped>
.o-history-page {
    @apply
        text-sm
    ;
}
</style>
