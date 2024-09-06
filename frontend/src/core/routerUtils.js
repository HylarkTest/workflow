// eslint-disable-next-line import/prefer-default-export
import { h } from 'vue';
import { RouterView } from 'vue-router';

export function matchedMeta(route, key) {
    const groupedMeta = Object.assign(
        {},
        ...route.matched.map(({ meta }) => meta)
    );

    return groupedMeta && groupedMeta[key];
}

export const ChildWrapperComponent = {
    name: 'ChildWrapperComponent',
    render: () => h(RouterView),
};
