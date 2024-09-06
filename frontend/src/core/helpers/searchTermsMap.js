import { $t } from '@/i18n.js';

function titleToSearchString(title, key) {
    return $t(`${key}.${title}`);
}

function termsToSearchStrings(searchTerms) {
    return searchTerms.map((term) => {
        return $t(`searchTerms.${term}`);
    });
}

export default function transformToSearchStrings(searchTerms, mainTitle, key) {
    const title = titleToSearchString(mainTitle, key);
    const terms = termsToSearchStrings(searchTerms);

    return terms.concat(title);
}
