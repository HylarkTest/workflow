export default {
    methods: {
        // To structure the dataMap
        flattenContainers(design) {
            return _.flattenDeep(this.getContainers(design));
        },
        getContainers(designObject) {
            if (_.has(designObject, 'containers')) {
                return _.map(designObject.containers, this.getContainers);
            }
            if (_.has(designObject, 'rows')) {
                return _.map(designObject.rows, this.getContainers);
            }
            return [designObject];
        },
    },
};
