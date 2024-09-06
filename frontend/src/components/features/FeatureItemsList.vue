<template>
    <div class="o-feature-items-list">
        <GroupingBase
            v-if="hasGroupings"
            :groupings="itemGroupings"
            :groupingType="currentGroup"
            :displayedList="displayedList"
            :listGroupingKey="listGroupingKey"
            :viewType="viewType"
        >
            <template
                #itemsSlot="{ source, grouping }"
            >
                <LoadMore
                    :hasNext="hasMore(grouping)"
                    @nextPage="showMore"
                >
                    <slot
                        name="itemsSlot"
                        :items="source.items"
                    >
                    </slot>
                </LoadMore>
            </template>
        </GroupingBase>
        <slot
            v-if="hasNoContent"
            name="noContentSlot"
        >
        </slot>
    </div>
</template>

<script>
import LoadMore from '@/components/data/LoadMore.vue';
import GroupingBase from '@/components/views/GroupingBase.vue';

export default {
    name: 'FeatureItemsList',
    components: {
        LoadMore,
        GroupingBase,
    },
    props: {
        allItems: {
            type: [Array, null],
            required: true,
        },
        itemGroupings: {
            type: [Array, null],
            required: true,
        },
        currentGroup: {
            type: [String, null],
            default: null,
        },
        displayedList: {
            type: [Object, null],
            default: null,
        },
        listGroupingKey: {
            type: String,
            required: true,
        },
        viewType: {
            type: String,
            required: true,
        },
        hasMoreFunction: {
            type: Function,
            required: true,
        },
        isLoading: Boolean,
    },
    emits: [
        'showMore',
    ],
    computed: {
        hasGroupings() {
            return this.itemGroupings?.length;
        },
        hasNoContent() {
            return !this.isLoading && !this.allItems?.length;
        },
    },
    methods: {
        showMore(grouping) {
            this.$emit('showMore', grouping);
        },
    },
    created() {
        this.hasMore = (grouping) => this.hasMoreFunction(grouping);
    },
};
</script>

<style>
/* .o-feature-items-list {
} */
</style>
