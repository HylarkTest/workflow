export default {
    props: {
        mapping: {
            type: [Object, null],
            default: null,
        },
    },
    data() {
        return {
            blueprintForm: this.$apolloForm(() => {
                const data = {
                    name: this.mapping?.name || '',
                    singularName: this.mapping?.singularName || '',
                    description: this.mapping?.description || '',
                };

                if (!this.mapping) {
                    data.type = null;
                    data.fields = null;
                    data.features = [];
                } else {
                    data.id = this.mapping.id;
                }

                return data;
            }),
        };
    },
};
