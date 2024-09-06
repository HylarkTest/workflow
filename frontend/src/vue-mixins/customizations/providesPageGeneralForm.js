import { removeTypename } from '@/core/helpers/apolloHelpers.js';

export default {
    props: {
        page: {
            type: [Object, null],
            default: null,
        },
    },
    data() {
        return {
            pageForm: this.$apolloForm(() => {
                const data = {
                    name: this.page?.name || '',
                    description: this.page?.description || '',
                    folder: this.page?.folder || null,
                    symbol: this.page?.symbol || null,
                    newData: removeTypename(this.page?.newData || null),
                    image: this.page?.image || null,
                };

                if (!this.page) {
                    data.type = null;
                    data.mapping = null;
                    data.filter = null;
                    data.templateRefs = [];
                } else {
                    data.id = this.page.id;
                    if (this.page.type === 'ENTITIES') {
                        const pageFilter = this.page.markerFilters?.[0] || this.page.fieldFilters?.[0];
                        const filterObj = pageFilter ? {
                            by: pageFilter.fieldId ? 'FIELD' : 'MARKER',
                            fieldId: pageFilter.fieldId || null,

                            match: pageFilter.operator,
                            matchValue: pageFilter.markerId || pageFilter.match,
                            context: pageFilter.context,
                        } : null;
                        data.filter = filterObj;
                    }
                }

                return data;
            }),
        };
    },
};
