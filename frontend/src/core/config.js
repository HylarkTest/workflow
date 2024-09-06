import _, { get, partial } from 'lodash';

export function parseEnv(value, def = null) {
    if (_.isUndefined(value)) {
        return def;
    }
    switch (value.toLowerCase()) {
    case 'true':
    case '(true)':
        return true;
    case 'false':
    case '(false)':
        return false;
    case 'empty':
    case '(empty)':
        return '';
    case 'null':
    case '(null)':
        return null;
    default:
        return value.replace(/^\s*(['"])(.*)\1\s*$/, '$1');
    }
}

const env = import.meta.env;

const config = {
    app: {
        env: parseEnv(env.MODE, 'production'),
        domain: parseEnv(env.VITE_DOMAIN),
        name: 'Hylark',
        'landing-domain': parseEnv(env.VITE_LANDING_DOMAIN, parseEnv(env.VITE_DOMAIN)),
        'api-url': parseEnv(env.VITE_API_URL),
        'landing-url': parseEnv(env.VITE_LANDING_URL),
        'cors-proxy-url': parseEnv(env.VITE_CORS_PROXY_URL, ''),
    },
    session: {
        lifetime: parseEnv(env.VITE_SESSION_LIFETIME, 120),
    },
    debug: {
        alertOnHttpError: parseEnv(env.VITE_ALERT_ON_HTTP_ERROR, false),
        logApolloErrors: parseEnv(env.VITE_LOG_APOLLO_ERRORS, false),
    },
    graphql: {
        http: parseEnv(env.VITE_GRAPHQL_HTTP, '/graphql'),
        file: parseEnv(env.VITE_FILES_ROOT, '/graphql'),
    },
    locale: {
        lang: parseEnv(env.VITE_I18N_LOCALE, 'en'),
        fallback: parseEnv(env.VITE_I18N_FALLBACK_LOCALE, 'en'),
    },
    pusher: {
        'app-key': parseEnv(env.VITE_PUSHER_APP_KEY),
        auth: {
            subscriptions: '/graphql/subscriptions/auth',
            broadcasts: '/broadcasting/auth',
        },
        cluster: parseEnv(env.VITE_PUSHER_CLUSTER, 'eu'),
        host: parseEnv(env.VITE_PUSHER_HOST),
        port: parseEnv(env.VITE_PUSHER_PORT),
        'force-tls': parseEnv(env.VITE_PUSHER_FORCE_TLS, false),
        'disable-stats': parseEnv(env.VITE_PUSHER_DISABLE_STATS, true),
    },
    sentry: {
        dsn: parseEnv(env.VITE_SENTRY_DSN),
        report: parseEnv(env.VITE_REPORT_ERRORS),
        traceSampleRate: parseEnv(env.VITE_TRACE_SAMPLE_RATE),
    },
    matomo: {
        host: parseEnv(env.VITE_MATOMO_HOST, 'https://hylark.matomo.cloud'),
        siteId: parseEnv(env.VITE_MATOMO_SITE_ID, 1),
    },
};

export default partial(get, config);
