// Describes which components and used to display which field types

export const lineTypes = [
    'TITLE',
    'FIRST_NAME',
    'LAST_NAME',
    'NAME',
    'LINE',
    'EMAIL',
    'PHONE',
    'PARAGRAPH',
    'ADDRESS',
    'MULTI',
    'BOOLEAN',
    'NUMBER',
    'PHONE',
    'URL',
];

export function getTemplateComponent(type) {
    const name = _(type).split('_').map(_.capitalize).join('');
    return `Template${name}`;
}

export function getTemplateEditComponent(type) {
    const name = _(type).split('_').map(_.capitalize).join('');
    return `Template${name}Edit`;
}

export default { getTemplateComponent, lineTypes };
