import FeedbackPage from '@/components/feedback/FeedbackPage.vue';

const RegistrationPage = () => import(
    '@/components/access/RegistrationPage.vue'
);

const LoginPage = () => import(
    '@/components/access/LoginPage.vue'
);

const OtpPage = () => import(
    '@/components/access/OtpPage.vue'
);

const AuthenticationPage = () => import(
    '@/components/access/AuthenticationPage.vue'
);

const PasswordReset = () => import(
    '@/components/access/PasswordReset.vue'
);

const PasswordSet = () => import(
    '@/components/access/PasswordSet.vue'
);

const registrationKeys = [
    {
        path: '/signup',
        name: 'register.initial',
    },
    {
        path: '/signup/start',
        name: 'register.start',
    },
    {
        path: '/signup/uses',
        name: 'register.uses',
    },
    {
        path: '/signup/refine',
        name: 'register.refine',
    },
    // {
    //     path: '/signup/templates',
    //     name: 'register.templates',
    // },
    {
        path: '/signup/spaces',
        name: 'register.spaces',
    },
    {
        path: '/signup/confirm',
        name: 'register.confirm',
    },
];

const registrationRoutes = registrationKeys.map((val) => {
    return {
        path: val.path,
        name: val.name,
        component: RegistrationPage,
        meta: {
            noNav: true,
            access: true,
            mw: val.name !== 'register.initial' ? ['auth', 'remember:savedRegistrationPage,server'] : ['guest'],
        },
    };
});

const accessRoutes = [
    ...registrationRoutes,
    {
        path: '/',
        redirect: { name: 'access.login' },
        meta: { noNav: true, access: true, mw: ['guest'] },
    },
    {
        path: '/login',
        name: 'access.login',
        component: LoginPage,
        meta: { noNav: true, access: true, mw: ['guest'] },
    },
    {
        path: '/access/reset',
        name: 'access.passwordReset',
        component: PasswordReset,
        meta: { noNav: true, access: true },
    },
    {
        path: '/access/set',
        name: 'access.passwordSet',
        component: PasswordSet,
        meta: { noNav: true, access: true },
    },
    {
        path: '/access/otp',
        name: 'access.otp',
        component: OtpPage,
        meta: { noNav: true, access: true },
    },
    {
        path: '/access/authentication',
        name: 'access.authentication',
        component: AuthenticationPage,
        meta: { noNav: true, access: true },
    },
    {
        path: '/activate',
        name: 'access.activate',
        component: FeedbackPage,
        props: (to) => {
            if (to.query.verified) {
                return { messagePath: 'access.activated' };
            }
            if (to.query['already-verified']) {
                return { messagePath: 'access.alreadyActivated' };
            }
            return {
                toUrl: '/email/verification-notification',
                linkPath: 'common.resend',
                messagePath: 'access.notActivated',
            };
        },
        meta: { noNav: true },
    },
    {
        path: '/activation-sent',
        name: 'access.activationSent',
        component: FeedbackPage,
        props: {
            messagePath: 'access.activationEmailSent',
        },
        meta: { noNav: true },
    },
    {
        path: '/invite-sent',
        name: 'access.inviteSent',
        component: FeedbackPage,
        props: {
            messagePath: 'access.inviteEmailSent',
        },
        meta: { noNav: true, access: true },
    },
];

export default accessRoutes;
