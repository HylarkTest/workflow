export default {
    methods: {
        isFieldRequired(field) {
            return field.options?.rules?.required;
        },
    },
};
