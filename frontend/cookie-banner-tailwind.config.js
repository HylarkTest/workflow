const config = require('./tailwind.config');

module.exports = {
    ...config,
    content: {
        files: [
            './src/components/access/CookieBanner.vue',
            './src/components/access/CookiePanel.vue',
            './src/components/inputs/ToggleButton.vue',
        ],
    },
    corePlugins: {
        ...(config.corePlugins || {}),
        preflight: false,
    },
    safelist: ['body'],
};
