import { activeBase } from '@/core/repositories/baseRepository.js';

export default async function redirectBaseRoute(to) {
    if (!to.params?.baseId) {
        const base = activeBase();
        if (base) {
            return {
                ...to,
                params: {
                    ...to.params,
                    baseId: base.id,
                },
            };
        }
    }

    return to;
}
