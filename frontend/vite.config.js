import path from 'path';
import { defineConfig, loadEnv, splitVendorChunkPlugin } from 'vite';
import VueDevTools from 'vite-plugin-vue-devtools';
import resolve from 'vite-plugin-resolve';
import vue from '@vitejs/plugin-vue';
import graphql from '@rollup/plugin-graphql';
import stylelint from 'vite-plugin-stylelint';
import notifier from 'node-notifier';

import { sentryVitePlugin } from '@sentry/vite-plugin';

import asyncEslint from './vite/vite-plugin-eslint/index.mjs';

const RELEASE = process.env.RELEASE || '';

const build = process.env.HYLARK_BUILD || 'main';
const isMainBuild = build === 'main';
const environment = process.env.NODE_ENV || 'development';
const isProduction = environment === 'production';

function notifySuccess() {
    const title = '✅ Build successful';
    const message = 'Compiled without problems';

    return {
        name: 'notify',
        buildEnd() {
            notifier.notify({ title, message, icon: false });
        },
    };
}

export default defineConfig((configEnv) => {
    const env = loadEnv(configEnv.mode, process.cwd(), '');
    const isHMR = configEnv.command === 'serve';

    const proxyOptions = {
        target: env.DEV_PROXY || 'http://app.hylark.test',
        changeOrigin: true,
    };
    const assetRx = '^.*\\.(ico|png|jpg|jpeg|gif|svg|woff|woff2|ttf|eot)$';
    const proxyUrls = [
        '/graphql',
        '/switch-base',
        '/csrf-cookie',
        '/preferences',
        '/bootstrap',
        '/register',
        '/auth/check',
        '/logout',
        '/font-awesome-query',
        '/billing',
        '/user',
        '/page-wizard',
        '/api',
        '/account',
        '/latest_release.txt',
        '/integrate',
        '/css',
        '/store',
        '/login-history',
        '/base',
        '/image-search',
        '/member-invite',
        '/forgot-password',
        '/broadcasting/auth',
    ];
    const proxy = {
        [assetRx]: proxyOptions,
        '/login': {
            ...proxyOptions,
            // bypass if it is a get request
            bypass: (req) => {
                if (req.method === 'GET' && req.url.match(/^\/login(\?.*)*$/)) {
                    return req.originalUrl;
                }
                return null;
            },
        },
    };
    proxyUrls.forEach((url) => {
        proxy[url] = proxyOptions;
    });

    const getMessagesFile = {
        main: 'getMessages-MAIN.js',
        templates: 'getMessages-TEMPLATES.js',
        'cookie-banner': 'getMessages-COOKIES.js',
    }[build];

    return {
        plugins: [
            vue(),
            graphql(),
            isMainBuild ? splitVendorChunkPlugin() : null,
            resolve({
                getMessages: `
                    import getMessages from '@/locales/${getMessagesFile}';
                    export { getMessages as default }
                `,
                RELEASE: `
                    const RELEASE = '${RELEASE}';
                    export { RELEASE as default };
                `,
            }),
            isHMR && stylelint({ fix: env.LINT_AUTOFIX === 'true' }),
            isHMR && asyncEslint({
                failOnWarning: true,
                fix: env.LINT_AUTOFIX === 'true',
                async: env.LINT_ASYNC === 'true',
                workers: parseInt(env.LINT_WORKERS || '4', 10),
                shouldNotify: env.LINT_NOTIFY === 'true',
            }),
            notifySuccess(),
            env.SENTRY_AUTH_TOKEN && RELEASE ? sentryVitePlugin({
                authToken: env.SENTRY_AUTH_TOKEN,
                release: { name: RELEASE },
                sourcemaps: {
                    ignore: '../public/assets/locale-*',
                    filesToDeleteAfterUpload: '../public/assets/*.js.map',
                },
                org: 'nal',
                project: 'hylark-vue',
            }) : null,
            ...(env.DEVTOOLS_SEVEN === 'true' ? [VueDevTools()] : []),
        ],
        resolve: {
            alias: {
                '@': path.resolve(__dirname, './src'),
            },
        },
        server: {
            host: env.DEV_HOST || 'dev.hylark.test',
            port: 8080,
            proxy,
        },
        build: {
            sourcemap: ['staging', 'production'].includes(process.env.NODE_ENV),
            outDir: '../public',
            ...(isMainBuild ? {} : { assetsDir: `../public/${build}` }),
            rollupOptions: {
                input: path.resolve(__dirname, isMainBuild ? 'index.html' : `${build}-index.html`),
                output: isMainBuild ? {
                    manualChunks(id) {
                        if (id.includes('lodash')) {
                            return 'lodash';
                        }
                        if (id.includes('.gql')) {
                            return 'gql';
                        }
                        return null;
                    },
                } : {
                    entryFileNames: (chunkInfo) => {
                        return chunkInfo.name === `${build}-index` ? `${build}/app.js` : `${build}/[name].js`;
                    },
                    chunkFileNames: `${build}/[name].js`,
                    assetFileNames: (chunkInfo) => {
                        return chunkInfo.name === `${build}-index.css` ? `${build}/app.css` : `${build}/[name].[ext]`;
                    },
                },
                external: [
                    /^.*?\.(ico|png|jpg|jpeg|gif|svg|woff|woff2|ttf|eot)/,
                ],
            },
        },
        base: '/',
    };
});
