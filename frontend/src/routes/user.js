const EntitiesPage = () => import(
    '@/components/entities/EntitiesPage.vue'
);

const SubsetPage = () => import(
    '@/components/layout/SubsetPage.vue'
);

const EntityPage = () => import(
    '@/components/entities/EntityPage.vue'
);

const CustomizeAll = () => import(
    '@/components/customize/CustomizeAll.vue'
);

const NewBasePage = () => import(
    '@/components/bases/NewBasePage.vue'
);

const userRoutes = [
    {
        path: '/page/:pageId',
        component: EntitiesPage,
        name: 'page',
        props: true,
        meta: { mw: ['auth'], baseScoped: true, pageParams: ['pageId'] },
    },
    {
        path: '/feature/:pageId/:listId?',
        component: SubsetPage,
        name: 'feature',
        props: true,
        meta: { mw: ['auth'], baseScoped: true, pageParams: ['pageId', 'listId'] },
    },
    {
        path: '/view/:pageId/:itemId/:tab?',
        component: EntityPage,
        name: 'entityPage',
        props: true,
        meta: { mw: ['auth'], baseScoped: true, pageParams: ['pageId', 'itemId'] },
    },
    {
        path: '/record/:itemId/:tab?',
        component: EntityPage,
        name: 'recordPage',
        props: true,
        meta: { mw: ['auth'], baseScoped: true, pageParams: ['itemId'] },
    },
    {
        path: '/customize/:tab?',
        component: CustomizeAll,
        name: 'customizePage',
        props: true,
        meta: { mw: ['auth'], baseScoped: true },
    },
    {
        path: '/newBase',
        name: 'newBase',
        component: NewBasePage,
        meta: { noNav: true, mw: ['auth'] },
        props: true,
    },
];

export default userRoutes;
