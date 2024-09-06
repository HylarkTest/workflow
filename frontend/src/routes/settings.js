// import { ChildWrapperComponent } from '@/core/routerUtils.js';

const SettingsPage = () => import(
    '@/components/settings/SettingsPage.vue'
);

const SettingsPreferences = () => import(
    '@/components/settings/SettingsPreferences.vue'
);
const SettingsNotifications = () => import(
    '@/components/settings/SettingsNotifications.vue'
);
const SettingsGeneral = () => import(
    /* webpackChunkName: "settings" */ '@/components/settings/SettingsGeneral.vue'
);
const SettingsAccount = () => import(
    '@/components/settings/SettingsAccount.vue'
);
const SettingsPlans = () => import(
    '@/components/settings/SettingsPlans.vue'
);
const SettingsIntegrations = () => import(
    '@/components/settings/SettingsIntegrations.vue'
);
const SettingsProfile = () => import(
    /* webpackChunkName: "settings" */ '@/components/settings/SettingsProfile.vue'
);
const SettingsPeople = () => import(
    /* webpackChunkName: "settings" */ '@/components/settings/SettingsPeople.vue'
);

// const InvitedPages = () => import(
//     '@/components/settings/InvitedPages.vue'
// );
// const InvitedData = () => import(
//     '@/components/settings/InvitedData.vue'
// );
// const InvitedPage = () => import(
//     '@/components/settings/InvitedPage.vue'
// );
// const InvitedPageCreate = () => import(
//     '@/components/settings/InvitedPageCreate.vue'
// );

const settingsRoutes = [
    {
        path: '/settings',
        name: 'settings',
        component: SettingsPage,
        redirect: { name: 'settings.account' },
        meta: { noNav: true, mw: ['auth'] },
        props: true,
        children: [
            {
                path: 'account',
                component: SettingsAccount,
                name: 'settings.account',
            },
            {
                path: 'preferences',
                component: SettingsPreferences,
                name: 'settings.preferences',
            },
            {
                path: 'notifications',
                component: SettingsNotifications,
                name: 'settings.notifications',
            },
        ],
    },
    {
        path: '/settings',
        name: 'settings.base',
        component: SettingsPage,
        redirect: { name: 'settings.account' },
        meta: { noNav: true, mw: ['auth'], baseScoped: true },
        props: true,
        children: [
            {
                path: 'general',
                component: SettingsGeneral,
                name: 'settings.general',
                // meta: { mw: ['role:admin'] },
            },
            {
                path: 'plans',
                component: SettingsPlans,
                name: 'settings.plans',
                meta: { mw: ['role:owner'] },
            },
            {
                path: 'integrations',
                component: SettingsIntegrations,
                name: 'settings.integrations',
            },
            {
                path: 'people',
                component: SettingsPeople,
                name: 'settings.people',
                meta: { mw: ['role:admin'] },
            },
            {
                path: 'profile',
                component: SettingsProfile,
                name: 'settings.profile',
            },
        ],
    },
];

export default settingsRoutes;
