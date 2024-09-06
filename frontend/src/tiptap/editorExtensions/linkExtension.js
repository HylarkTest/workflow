import BaseHyperlink from '@tiptap/extension-link';

function formatHref(href) {
    let formattedHref = href;
    if (href.length) {
        if (href.startsWith('//')) {
            formattedHref = `https:${href}`;
        } else if (!href.startsWith('http')) {
            formattedHref = `https://${href}`;
        }
    }
    return formattedHref;
}

export default BaseHyperlink.extend({
    addOptions() {
        return {
            openOnClick: true,
            protocols: ['http', 'https', 'ftp', 'mailto'],
            HTMLAttributes: {
                class: 'tiptap-link',
            },
        };
    },
    addAttributes() {
        return {
            href: {
                renderHTML: (attributes) => {
                    return {
                        href: formatHref(attributes.href),
                    };
                },
            },
        };
    },
});
