import { $t } from '@/i18n.js';

// const allRecordOptions = [
//     {
//         val: 'DISSOCIATE_RECORD',
//         condition: '',
//     },
//     {
//         val: 'MAKE_ENTITY_PAGE',
//     },
//     {
//         val: 'DELETE',
//     },
// ];

export const fullRecordOptions = ['DUPLICATE', 'MAKE_ENTITY_PAGE', 'DELETE'];
export const pageCreateBehaviors = ['ADD_RECORD'];

export function getModalHeaders(name) {
    return {
        header: $t('customizations.pages.newPageModal.header', { name }),
        description: $t('customizations.pages.newPageModal.descriptionRecord', { name }),
    };
}

export function getDefaultPageName(name) {
    return `Page for "${name}"`;
}
