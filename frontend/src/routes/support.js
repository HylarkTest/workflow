const SupportBuild = () => import(
    '@/components/supportSite/SupportBuild.vue'
);
const SupportHome = () => import(
    '@/components/supportSite/SupportHome.vue'
);
const SupportArticle = () => import(
    '@/components/supportSite/SupportArticle.vue'
);
const SupportFolder = () => import(
    '@/components/supportSite/SupportFolder.vue'
);

const ErrorPage = () => import(
    '@/components/errors/ErrorPage.vue'
);

const supportRoutes = [
    {
        path: '/support',
        name: 'support',
        component: SupportBuild,
        redirect: { name: 'support.home' },
        meta: { support: true, noNav: true, mw: ['auth'] },
        props: true,
        children: [
            {
                path: '/support/home',
                name: 'support.home',
                component: SupportHome,
            },
            {
                path: '/support/article/:friendlyUrl',
                name: 'support.article',
                component: SupportArticle,
                props: true,
            },
            {
                path: '/support/folder/:id',
                name: 'support.folder',
                component: SupportFolder,
                props: true,
            },
            {
                path: '/support/:pathMatch(.*)*',
                name: 'support.not-found',
                component: ErrorPage,
                props: () => (
                    {
                        status: '404',
                        errorButtonUrl: '/support/home',
                        errorButtonTextPath: 'support.goHome',
                    }
                ),
            },
        ],
    },
];

export default supportRoutes;
