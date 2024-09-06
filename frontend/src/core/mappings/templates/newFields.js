function getNewDefaults(fields) {
    const list = ['SYSTEM_NAME', 'DESCRIPTION', 'IMAGE'];
    const fieldsIds = _.map(fields, 'id');
    return _.intersection(list, fieldsIds);
}

const specificNewFields = {
    // Page ids with specific values for a page
};

export function getNewFields(pageId, pageDetails) {
    let newFields = specificNewFields[pageId];
    if (!newFields) {
        newFields = getNewDefaults(pageDetails.fields);
    }

    return newFields;
}

export default {
    getNewFields,
};
