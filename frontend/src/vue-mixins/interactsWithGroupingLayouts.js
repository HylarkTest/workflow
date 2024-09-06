import GroupingHeader from '@/components/groupings/GroupingHeader.vue';

export default {
    components: {
        GroupingHeader,
    },
    props: {
        groupings: {
            type: Array,
            required: true,
        },
        useCase: {
            type: String,
            default: 'list',
        },
        groupingType: {
            type: [String, null],
            default: null,
        },
        viewType: {
            type: [String],
            required: true,
            validator(value) {
                // The value must match one of these types
                return ['SPREADSHEET', 'LINE', 'TILE', 'KANBAN', 'EMAILS'].includes(value);
            },
        },
    },
    computed: {
        showHeader() {
            return this.groupingType;
        },
    },
};
