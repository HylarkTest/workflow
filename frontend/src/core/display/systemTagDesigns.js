// Provides some designs for SYSTEM USED tags. Not user tags.
// Visual elements styled like tags that we want to add.

const pageMappingShape = {
    shape: 'rounded',
    weight: 'bold',
};

const pageTypeColor = {
    fillColor: 'bg-sky-200',
    textColor: 'text-sky-700',
};

const mappingTypeColor = {
    fillColor: 'bg-gold-200',
    textColor: 'text-gold-700',
};

export const pageMappingLarge = {
    size: 'lg',
    fillColor: 'bg-gold-200',
    textColor: 'text-gold-700',
};

export const pageTypeLarge = {
    ...pageMappingShape,
    ...pageTypeColor,
    size: 'lg',
};

export const mappingTypeLarge = {
    ...pageMappingShape,
    ...mappingTypeColor,
    size: 'lg',
};

export const mappingTypeSmall = {
    ...pageMappingShape,
    ...mappingTypeColor,
    size: 'sm',
};

export const pageTypeSmall = {
    ...pageMappingShape,
    ...pageTypeColor,
    size: 'sm',
};

export default { pageTypeLarge, mappingTypeLarge, pageTypeSmall };
