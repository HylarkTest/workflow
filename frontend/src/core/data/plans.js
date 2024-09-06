import i18n from '@/i18n.js';

function featureText(key, variable = null) {
    return {
        path: `landing.pricing.plans.benefits.${key}`,
        args: variable,
    };
}

const plans = [
    {
        id: 'free',
        color: '',
        free: true,
        line: '',
        includes: 'landing.pricing.plans.free.includes',
        features: [
            {
                text: featureText('customizeData'),
            },
            {
                text: featureText('designTemplates', { number: '20+' }),
            },
            {
                text: featureText('views'),
            },
            {
                text: featureText('features'),
            },
            {
                text: featureText('calendar'),
            },
            {
                text: featureText('pages', { number: '5' }),
            },
            {
                text: featureText('privacy'),
            },

        ],
        button: 'landing.general.getStarted',
        fundamentals: {
            users: i18n.global.t('landing.pricing.full.sections.fundamentals.users.value'),
            records: i18n.global.t('landing.pricing.full.sections.values.upTo', { number: '500' }),
            storage: '1 GB',
            spaces: 1,
            pages: 8,
            log: i18n.global.tc('common.dates.monthsChoice', 1, { number: 1 }),
            dataTemplates: true,
        },
        customizations: {
            data: true,
            fieldTypes: true,
            views: true,
            designTemplates: i18n.global.t('landing.pricing.full.sections.values.templates', { number: '24' }),
            color: true,
            personalStyle: false,
            branded: false,
        },
        collaboration: {
            sharing: i18n.global.tc('landing.pricing.full.sections.values.invites', 3, { number: 3 }),
        },
        productivity: {
            calendar: true,
            email: true,
            timeKeeper: false,
            goals: false,
            analytics: i18n.global.t('landing.pricing.full.sections.values.basic'),
            main: true,
        },
        security: {
            '2fa': true,
            device: true,
        },
        support: {
            documentation: true,
            email: true,
            phone: false,
            priority: false,
        },
    },
    {
        id: 'pro',
        color: 'violet',
        price: {
            yearly: 50,
            monthly: 5,
        },
        line: '',
        includes: {
            path: 'landing.pricing.plans.includesPlus',
            args: { planName: i18n.global.t('landing.pricing.plans.free.title') },
        },
        bubble: true,
        features: [
            {
                text: featureText('items', { pagesNumber: '50', recordsNumber: '10 000' }),
            },
            {
                text: featureText('skins'),
            },
            {
                text: featureText('storage', { storageAmount: '8gb' }),
            },
            {
                text: featureText('activityRecord'),
            },
            {
                text: featureText('sharing'),
                comingSoon: true,
            },
            {
                text: featureText('advancedStatistics'),
                comingSoon: true,
            },
            {
                text: featureText('goals'),
                comingSoon: true,
            },
        ],
        button: 'landing.general.tryFree',
        fundamentals: {
            users: i18n.global.t('landing.pricing.full.sections.fundamentals.users.value'),
            records: i18n.global.t('landing.pricing.full.sections.values.upTo', { number: '10 000' }),
            storage: '8 GB',
            spaces: 50,
            pages: 100,
            log: i18n.global.t('landing.pricing.full.sections.values.unlimited'),
            dataTemplates: true,
        },
        customizations: {
            data: true,
            fieldTypes: true,
            views: true,
            designTemplates: i18n.global.t('landing.pricing.full.sections.values.templates', { number: '50+' }),
            color: true,
            personalStyle: true,
            branded: true,
        },
        productivity: {
            calendar: true,
            email: true,
            timeKeeper: true,
            goals: true,
            analytics: true,
            main: true,
        },
        collaboration: {
            sharing: i18n.global.t('landing.pricing.full.sections.values.unlimited'),
        },
        security: {
            '2fa': true,
            device: true,
        },
        support: {
            documentation: true,
            email: true,
            phone: true,
            priority: true,
        },
    },
];

const allFeatures = [
    {
        id: 'fundamentals',
        features: [
            {
                id: 'users',
            },
            {
                id: 'storage',
            },
            {
                id: 'spaces',
            },
            {
                id: 'pages',
            },
            {
                id: 'records',
            },
            {
                id: 'log',
            },
            {
                id: 'dataTemplates',
            },
        ],
    },
    {
        id: 'customizations',
        features: [
            {
                id: 'data',
            },
            {
                id: 'fieldTypes',
            },
            {
                id: 'views',
            },
            {
                id: 'designTemplates',
            },
            {
                id: 'color',
            },
            {
                id: 'personalStyle',
            },
            {
                id: 'branded',
            },
        ],
    },
    {
        id: 'productivity',
        features: [
            {
                id: 'main',
            },
            {
                id: 'calendar',
            },
            {
                id: 'email',
            },
            {
                id: 'goals',
                comingSoon: true,
            },
            {
                id: 'timeKeeper',
                comingSoon: true,
            },
            {
                id: 'analytics',
                comingSoon: true,
            },
        ],
    },
    {
        id: 'collaboration',
        features: [
            {
                id: 'sharing',
                comingSoon: true,
            },
        ],
    },
    {
        id: 'security',
        features: [
            {
                id: '2fa',
            },
            {
                id: 'device',
            },

        ],
    },
    {
        id: 'support',
        features: [
            {
                id: 'documentation',
            },
            {
                id: 'email',
            },
            {
                id: 'phone',
            },
            {
                id: 'priority',
            },

        ],
    },
];

export { plans, allFeatures };
