export default {
    methods: {
        findSectionInForm(designObject, iteratee) {
            if (_.has(designObject, 'containers')) {
                for (const container of designObject.containers) {
                    const section = this.findSectionInForm(container, iteratee);
                    if (section) {
                        return section;
                    }
                }
            }
            if (_.has(designObject, 'rows')) {
                for (const row of designObject.rows) {
                    const section = this.findSectionInForm(row, iteratee);
                    if (section) {
                        return section;
                    }
                }
            }
            if (_.has(designObject, 'id')) {
                return _.iteratee(iteratee)(designObject) ? designObject : null;
            }
            return null;
        },
    },
};
