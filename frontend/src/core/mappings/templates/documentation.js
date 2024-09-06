// Page requirements
export default {
    id: 'PAGE_ID',
    name: '', // Mandatory UNLESS subset page, camelCase key to be used with 'labels.'
    singularName: '', // If entity type page (UNLESS subset), camelCase key to be used with 'labels.'
    pageName: '', // If entity type page (including subset), camelCase key to be used with 'labels.'
    symbol: 'fa-person-circle-plus', // Use getPageNames()
    pageType: 'ENTITIES', // Use getPageNames()
    folder: 'Folder name', // Optional
    fields: [], // Mandatory if entity type page
    relationships: [], // Optional, and only if entity type page
    features: [], // Mandatory if entity type page
    markerGroups: [], // Optional, and only if entity type page
    examples: [], // Mandatory if entity type page
    views: [], // Mandatory if entity type page
    newFields: [], // Mandatory if entity type page
    subset: { // If an entities subset page
        mainId: 'MAIN_PAGE_ID', // The id to the main page of which this is a subset
        filter: { // The filter
            type: 'MARKER', // MARKER or FIELD
            id: 'CAREER_CONTACT_DESCRIPTOR_TAG_TEMP.referee', // Id of the tag or field
            comparator: 'IS', // IS or NOT
            val: true, // If a field, true or false, or the key of a select field
        },
    },
};
