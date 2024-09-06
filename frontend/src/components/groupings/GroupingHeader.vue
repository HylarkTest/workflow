<template>
    <div
        class="c-grouping-header"
        @click="$emit('toggleGroupingOpenState', !isHeaderGroupOpen)"
    >
        <div
            v-if="groupingType === 'PRIORITY'"
        >
            <PriorityFlag
                :priority="formatPriority(grouping.header.val)"
                :hideClear="true"
                @click.stop
            >
            </PriorityFlag>
        </div>
        <div
            v-else-if="groupingType === 'FAVORITES'"
        >
            <FavoriteButton
                class="relative"
                :isFavorite="grouping.header.val === '1'"
            ></FavoriteButton>
        </div>
        <div
            v-else-if="groupingType === 'LIST'"
            class="flex"
        >
            <h4 class="font-bold text-2xl">
                {{ list.name }}
            </h4>
            <p
                class="ml-2 uppercase text-cm-400 font-semibold text-sm"
            >
                {{ list.space.name }}
            </p>
        </div>
        <div
            v-else-if="groupingType === 'EXTENSION'"
            class="font-bold text-2xl"
        >
            {{ groupingVal }}
        </div>

        <div
            v-else-if="hasDateFieldVal"
            class="font-semibold text-xl"
        >
            {{ formattedDate }}
        </div>

        <FieldGroupingHeader
            v-else-if="isFieldGrouping && groupingVal"
            :fieldId="groupingFieldId"
            :header="fieldHeader"
            :mapping="mapping"
        ></FieldGroupingHeader>

        <div
            v-else-if="isMarkerHeader"
        >
            <component
                v-if="hasHeaderLength"
                :is="markerComponent"
                :item="marker"
                size="sm"
            >
            </component>

            <div
                v-else
                v-t="'common.none'"
                class="font-semibold"
            >
            </div>
        </div>
        <div
            v-else
            v-t="'common.unset'"
            class="flex justify-between font-bold text-2xl text-gray-400"
        >
        </div>

        <div class="flex items-center text-cm-600">
            <div
                v-if="!hideCount"
                class="font-semibold"
            >
                {{ count }}
            </div>
            <div
                v-if="completedCount"
                class="font-semibold flex items-center ml-3"
            >
                <i class="far fa-check-circle text-primary-600 mr-1">
                </i>
                {{ completedCount }}
            </div>

            <ExpandCollapseButton
                class="ml-3"
                :class="{ 'opacity-0': !count }"
                :isExpanded="isHeaderGroupOpen"
            >
            </ExpandCollapseButton>
        </div>
    </div>
</template>

<script>

import ExpandCollapseButton from '@/components/buttons/ExpandCollapseButton.vue';
import FavoriteButton from '@/components/buttons/FavoriteButton.vue';
import PriorityFlag from '@/components/assets/PriorityFlag.vue';
import StageDisplay from '@/components/customize/StageDisplay.vue';
import StatusDisplay from '@/components/customize/StatusDisplay.vue';
import TagDisplay from '@/components/customize/TagDisplay.vue';
import FieldGroupingHeader from '@/components/groupings/FieldGroupingHeader.vue';

const tagStyle = {
    shape: 'rounded',
    size: 'sm',
    fillColor: 'brandIntense',
    textColor: 'white',
    weight: 'bold',
};

export default {
    name: 'GroupingHeader',
    components: {
        ExpandCollapseButton,
        FavoriteButton,
        PriorityFlag,
        StageDisplay,
        StatusDisplay,
        TagDisplay,
        FieldGroupingHeader,
    },
    mixins: [
    ],
    props: {
        grouping: {
            type: Object,
            required: true,
        },
        groupingType: {
            type: [String, null],
            default: null,
        },
        isHeaderGroupOpen: Boolean,
        hasCollapseOption: Boolean,
        hideCount: Boolean,
        mapping: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'toggleGroupingOpenState',
    ],
    data() {
        return {
        };
    },
    computed: {
        groupingGroup() {
            return this.grouping.header.group;
        },
        groupingVal() {
            return this.grouping.header.val;
        },
        isMarkerHeader() {
            return this.groupingType.startsWith('marker:');
        },
        marker() {
            if (!this.isMarkerHeader) {
                return null;
            }
            return this.groupingGroup;
        },
        list() {
            if (this.groupingType !== 'LIST') {
                return null;
            }
            return this.groupingGroup;
        },
        hasHeaderLength() {
            return this.groupingVal?.length;
        },
        items() {
            return this.grouping.items;
        },
        countItems() {
            return this.items.filter((item) => {
                return !item.completedAt;
            });
        },
        count() {
            return this.grouping.header?.count || this.countItems.length;
        },
        completedItems() {
            return this.items.filter((item) => {
                return item.completedAt;
            });
        },
        completedCount() {
            return this.completedItems?.length;
        },
        markerComponent() {
            if (!this.isMarkerHeader) {
                return null;
            }
            const type = this.marker.group.type;
            if (type === 'STATUS') {
                return 'StatusDisplay';
            }
            if (type === 'TAG') {
                return 'TagDisplay';
            }
            return 'StageDisplay';
        },
        // expandCollapseIcon() {
        //     return this.isHeaderGroupOpen ? 'fa-angle-up' : 'fa-angle-down';
        // },
        // expandCollapseTitle() {
        //     return this.isHeaderGroupOpen ? this.$t('common.collapseGroup') : this.$t('common.expandGroup');
        // },
        showExpandCollapseToggle() {
            return this.hasCollapseOption;
        },
        hasDateFieldVal() {
            return this.groupingField?.val === 'DATE';
        },
        formattedDate() {
            if (!this.hasDateFieldVal) {
                return null;
            }
            const date = this.$dayjs(this.groupingVal);
            if (this.$dayjs(date).isValid()) {
                return date.format('LL');
            }
            return 'No date';
        },
        isFieldGrouping() {
            return this.groupingType.includes('field');
        },
        groupingFieldId() {
            if (!this.isFieldGrouping) {
                return null;
            }
            return this.groupingType.split(':')[1];
        },
        mappingFields() {
            if (!this.mapping) {
                return [];
            }
            return this.mapping.fields;
        },
        groupingField() {
            if (!this.mappingFields.length) {
                return null;
            }
            return this.mappingFields.find((field) => {
                return field.id === this.groupingFieldId;
            });
        },
        fieldHeader() {
            return this.groupingVal || this.$t('common.unset');
        },
    },
    methods: {
        formatPriority(priority) {
            return priority ? parseInt(priority, 10) : 0;
        },
    },
    created() {
        this.tagStyle = tagStyle;
    },
};
</script>

<style scoped>

.c-grouping-header {

    &:hover {
        @apply
            cursor-pointer
        ;

        .c-grouping-header__collapse {
            @apply
                bg-primary-400
                text-primary-950
            ;
        }
    }

    @apply
        flex
        items-center
        justify-between
    ;

    &__angle {
        @apply
            leading-4
            text-base
        ;
    }
}

</style>
