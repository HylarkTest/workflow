import { $translationRaw, $translationExists } from '@/i18n.js';
import { setDocumentTitle } from '@/core/utils.js';

export default function updateRouterTitle(to) {
    const titlePath = to.meta.title || `routing.titles.${to.name}`;
    if ($translationExists(titlePath)) {
        setDocumentTitle($translationRaw(titlePath));
    }
}
