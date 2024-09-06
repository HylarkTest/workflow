import findsSectionInDesignForm from '@/vue-mixins/findsSectionInDesignForm.js';

export default {
    mixins: [
        findsSectionInDesignForm,
    ],
    methods: {
        setNameOnFirst(designItem, pageType) {
            // Returns the designItem with the default value put in
            // pageType is PERSON or ITEM
            const nameField = (pageType === 'ITEM') ? 'name' : '{firstName} {lastName}';
            const firstContainer = this.findFirst(designItem);
            if (firstContainer) {
                firstContainer.data = nameField;
                firstContainer.category = 'FIELDS';
            }
            return designItem;
        },
        findFirst(designItem) {
            return this.findSectionInForm(designItem, ['subType', 'MAIN']);
        },
    },
};
