export const supportIcons = [
    {
        terms: ['calendar'],
        symbol: 'fa-regular fa-calendar-alt',
    },
    {
        terms: ['todo'],
        symbol: 'fa-regular fa-square-check',
    },
    {
        terms: ['microsoft', 'outlook'],
        symbol: 'fa-brands fa-microsoft',
    },
    {
        terms: ['google'],
        symbol: 'fa-brands fa-google',
    },
    {
        terms: ['emails'],
        symbol: 'fa-regular fa-envelope',
    },
    {
        terms: ['timekeeper'],
        symbol: 'fa-regular fa-hourglass-clock',
    },
    {
        terms: ['pinboard'],
        symbol: 'fa-regular fa-map-pin',
    },
    {
        terms: ['blueprint'],
        symbol: 'fa-regular fa-compass-drafting',
    },
    {
        terms: ['customize', 'customization'],
        symbol: 'fa-regular fa-sliders-simple',
    },
    {
        terms: ['faq'],
        symbol: 'fa-regular fa-comments-question-check',
    },
    {
        recentlyAdded: true,
        symbol: 'fa-regular fa-clock-rotate-left',
    },
    {
        isDefault: true,
        symbol: 'fa-regular fa-newspaper',
    },
];

export function getSupportKeyWordIcon(article, condition = null) {
    if (condition) {
        const match = supportIcons.find((iconObj) => {
            return iconObj[condition];
        });
        return match?.symbol || '';
    }

    const title = article.title.toLowerCase();
    const tags = article.tags?.length ? article.tags.map((tag) => tag.toLowerCase()) : null;

    const iconMatch = supportIcons.find((iconObj) => {
        const hasTerm = iconObj.terms?.some((term) => {
            return title.includes(term)
                || tags?.some((tag) => tag.includes(term));
        });
        return hasTerm;
    });

    const result = iconMatch || supportIcons.find((obj) => obj.isDefault);

    return result.symbol;
}
