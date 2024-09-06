import { has } from 'lodash';
import { switchToBase } from '@/core/repositories/baseRepository.js';
import { isMissingError } from '@/http/checkResponse.js';

export default async function switchToScopedBase(to) {
    if (has(to, 'params.baseId') && to.params.baseId) {
        const routeBaseId = to.params.baseId;
        try {
            await switchToBase(routeBaseId, false);
        } catch (e) {
            if (isMissingError(e)) {
                return { name: 'not-found' };
            }
        }
    }

    return to;
}
