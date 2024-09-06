import {
    getAuthenticatedUser,
    loadUserAndBases,
} from '@/core/auth.js';

const roleHierarchy = [
    'OWNER',
    'ADMIN',
    'MEMBER',
];

export default async function roleMiddleware(to, from, options) {
    await loadUserAndBases();
    const role = options[0].toUpperCase();
    const roleIndex = roleHierarchy.indexOf(role);
    const baseId = to.params.baseId;
    const user = getAuthenticatedUser();
    const foundBase = user.value.allBases().find((base) => base.id === baseId);
    const userRoleIndex = roleHierarchy.indexOf(foundBase.pivot.role);
    if (userRoleIndex > roleIndex) {
        return { name: 'home' };
    }
    return to;
}
