import { $t } from '@/i18n.js';

export function getRelationship(name, toId, inverse, relationshipType) {
    return {
        name: $t(`labels.${name}`),
        type: relationshipType,
        to: toId,
        inverseName: $t(`labels.${inverse}`),
    };
}

export function getValidRelationships() {
}
