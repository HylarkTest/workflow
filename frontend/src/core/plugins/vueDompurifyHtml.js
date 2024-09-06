import VueDOMPurifyHTML from 'vue-dompurify-html';

const allowedTags = [
    'a',
    'br',
    'code',
    'div',
    'img',
    'p',
    'pre',
    'span',
    'h1',
    'h2',
    'h3',
    'h4',
    'h5',
    'h6',
    'ul',
    'ol',
    'li',
    'em',
    'i',
    'strong',
    'b',
    'u',
    'table',
    'thead',
    'tbody',
    'tfoot',
    'tr',
    'th',
    'td',
    'button-el',
];

const allowedAttributes = [
    'alt',
    'class',
    'height',
    'href',
    'src',
    'style',
    'target',
    'title',
    'width',
    'align',
    'display',
    'text-align',
    'padding',
    'margin',
    'vertical-align',
    'border-spacing',
    'border-collapse',
    'margin-inline-start',
    'margin-inline-end',
    'colspan',
    'table-layout',
    'cellpadding',
    'cellspacing',
    'border',
    'role',
    'tabindex',
];

const DOMPurifyConfigurations = {
    default: {
        ALLOWED_TAGS: allowedTags,
        ALLOWED_ATTR: allowedAttributes,
        ALLOW_UNKNOWN_PROTOCOLS: false,
    },
    namedConfigurations: {
        svg: {
            USE_PROFILES: { svg: true, svgFilters: true },
        },
        embed: {
            ADD_TAGS: ['iframe'],
            ADD_ATTR: ['allow', 'allowfullscreen', 'frameborder', 'scrolling', 'src'],
        },
    },
    hooks: {
        uponSanitizeElement: (node, data) => {
            if (data.tagName === 'iframe') {
                const src = node.getAttribute('src') || '';
                if (!(/^(http:\/\/|https:\/\/|\/\/)(www.youtube.com\/embed\/|player.vimeo.com\/video\/)/).test(src)) {
                    node.parentNode.removeChild(node);
                }
            }
        },
    },
};

export default function installDomPurify(app) {
    app.use(VueDOMPurifyHTML, DOMPurifyConfigurations);
}
