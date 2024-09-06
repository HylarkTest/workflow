const HomePage = () => import(
    '@/components/home/HomePage.vue'
);

const HistoryPage = () => import(
    '@/components/history/HistoryPage.vue'
);

const NotificationsPage = () => import(
    '@/components/notifications/NotificationsPage.vue'
);

const DataPage = () => import(
    '@/components/dataManagement/DataPage.vue'
);

const mainRoutes = [
    {
        path: '/home',
        // path: '/accountId/center',
        name: 'home',
        component: HomePage,
        meta: { mw: ['auth'], baseScoped: true },
        beforeEnter(to) {
            if (to.query.firstTime || to.query.hasPersonalBasePrompt) {
                window.firstArrival = !!to.query.firstTime;
                window.hasPersonalBasePrompt = !!to.query.hasPersonalBasePrompt;
                return {
                    name: to.name,
                    query: _.omit(to.query, ['firstTime', 'hasPersonalBasePrompt']),
                };
            }
            return null;
        },
    },
    {
        path: '/history/:pageType?/:mappingId?',
        name: 'history',
        props: true,
        component: HistoryPage,
        meta: { mw: ['auth'], baseScoped: true },
    },
    {
        path: '/notifications',
        name: 'notifications',
        component: NotificationsPage,
        meta: { mw: ['auth'], baseScoped: true },
    },
    {
        path: '/data/:tab?',
        name: 'dataManagement',
        component: DataPage,
        props: true,
        meta: { mw: ['auth'], baseScoped: true },
    },
];

export default mainRoutes;
