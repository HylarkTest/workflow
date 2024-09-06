import { parseMarkdown } from '@/core/utils.js';

function updateComponent(el, binding) {
    const { oldValue, value } = binding;
    if (!value || oldValue === value) {
        return;
    }

    // eslint-disable-next-line no-param-reassign
    el.innerHTML = parseMarkdown(value);
}

const markdownTextDirective = {
    mounted: updateComponent,
    updated: updateComponent,
};

const plugin = {
    install(app) {
        app.directive('md-text', markdownTextDirective);
    },
};

export default plugin;
