export default {
    data() {
        return {
            requiredFields: [], // Add in component
        };
    },
    computed: {
        canSave() {
            return this.hasRequiredFields && this.formHasChanged;
        },
        checkerForm() {
            return {}; // Define in component
        },
        checkerOriginal() {
            return {}; // Define in component
        },
        keysToCheck() {
            return []; // Define in component, all if empty
        },
        keysToOmit() {
            return []; // Define in component, none if empty
        },
        hasRequiredFields() {
            return this.getHasRequiredFields(this.checkerForm, this.requiredFields);
        },
        formHasChanged() {
            return this.getFormHasChanged(this.checkerForm, this.checkerOriginal, this.keysToCheck, this.keysToOmit);
        },
    },
    methods: {
        getFormHasChanged(form, original, validKeys, omittedKeys) {
            if (original) {
                let keys = _.keys(form.getData());

                if (omittedKeys.length) {
                    keys = _.difference(keys, omittedKeys);
                }

                if (validKeys.length) {
                    keys = _.intersection(keys, validKeys);
                }
                return _(keys).some((key) => {
                    const item = form[key];
                    if (!item) {
                        return original[key]; // Returns false if value also false
                    }
                    return !_.isEqual(item, original[key]);
                });
            }
            return true;
        },
        getHasRequiredFields(form, requiredFields) {
            return _(requiredFields).every((field) => {
                if (_.isString(field)) {
                    return form[field];
                }
                if (field.onlyRequiredIfPresent) {
                    return !_.has(form, field.fieldKey) || form[field.fieldKey];
                }
                return false;
            });
        },
    },
};
