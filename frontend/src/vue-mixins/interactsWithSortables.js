const options = [
    {
        value: 'MATCH',
        namePath: 'labels.relevance',
    },
    {
        value: 'CREATED_AT',
        namePath: 'labels.createdAt',
        direction: 'DESC',
    },
    {
        value: 'UPDATED_AT',
        namePath: 'labels.updatedAt',
        direction: 'DESC',
    },
    {
        value: 'MANUAL',
        namePath: 'labels.manual',
    },
    {
        value: 'NAME',
        namePath: 'labels.name',
        direction: 'ASC',
    },
    {
        value: 'DUE_BY',
        namePath: 'labels.dueDate',
        direction: 'ASC',
    },
    {
        value: 'DATE',
        namePath: 'labels.date',
        direction: 'DESC',
    },
    {
        value: 'PRIORITY',
        namePath: 'labels.priority',
        direction: 'DESC',
    },
    {
        value: 'FAVORITES',
        namePath: 'labels.favorites',
        direction: 'ASC',
    },
    {
        value: 'EXTENSION',
        namePath: 'labels.extension',
        direction: 'ASC',
    },
    {
        value: 'SIZE',
        namePath: 'labels.size',
        direction: 'DESC',
    },
];

export default {
    methods: {
        startingSortOrder(value) {
            const obj = this.findSortable(value);
            return _.clone(obj);
        },
        findSortable(value) {
            return _.find(options, { value });
        },
        validSortables(values) {
            return values.map((val) => {
                return this.findSortable(val);
            });
        },
        getBasicSortables() {
            return this.validSortables(['NAME', 'CREATED_AT', 'UPDATED_AT']);
        },
    },
};
