const path = require('path');

// Load .env files
// const { loadEnv } = require('vue-cli-plugin-apollo/utils/load-env');
//
// const env = loadEnv([
//     path.resolve(__dirname, '.env'),
//     path.resolve(__dirname, '.env.local'),
// ]);

module.exports = {
    client: {
        // service: env.VITE_APOLLO_ENGINE_SERVICE,
        includes: ['src/**/*.{js,jsx,ts,tsx,vue,gql}'],
    },
    service: {
        // name: env.VITE_APOLLO_ENGINE_SERVICE,
        localSchemaFile: path.resolve(__dirname, './schema.graphql'),
    },
    engine: {
        endpoint: import.meta.env.APOLLO_ENGINE_API_ENDPOINT,
        // apiKey: env.VITE_APOLLO_ENGINE_KEY,
    },
};
