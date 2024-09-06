import { simpleMappingRequest } from '@/http/apollo/buildMappingRequests.js';
import { initializeItem } from '@/core/repositories/itemRepository.js';

import interactsWithRouterTitles from '@/vue-mixins/interactsWithRouterTitles.js';

export default {
    mixins: [
        interactsWithRouterTitles,
    ],
    apollo: {
    },
    created() {
        this.$apollo.addSmartQuery('fullItem', {
            query() {
                return simpleMappingRequest(this.mapping, 'ONE', true);
            },
            variables() {
                return { id: this.item.id };
            },
            skip() {
                return !this.mapping || !this.item?.id;
            },
            update: (data) => {
                if (data.items.item) {
                    return initializeItem(data.items.item);
                }
                return null;
            },
        });
    },
};
