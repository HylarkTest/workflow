const config = require('./tailwind.config');

module.exports = {
    ...config,
    content: {
        files: [
            './src/TemplatesApp.vue',
            './src/components/access/UseItem.vue',
            './src/components/assets/ButtonEl.vue',
            './src/core/mappings/templates/uses.js',
        ],
    },
    corePlugins: {
        ...(config.corePlugins || {}),
        preflight: false,
    },
    safelist: ['body'],
};
