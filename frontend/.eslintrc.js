module.exports = {
    root: true,
    env: {
        browser: true,
        node: true,
        es2022: true,
    },
    plugins: [
        'es',
    ],
    parser: 'vue-eslint-parser',
    parserOptions: {
        ecmaVersion: 2022,
    },
    extends: [
        'plugin:vue/vue3-recommended',
        '@vue/airbnb',
        'plugin:@intlify/vue-i18n/recommended',
    ],
    overrides: [
        {
            files: ['*.graphql', '*.gql'],
            // parser: '@graphql-eslint/eslint-plugin',
            // plugins: ['@graphql-eslint'],
            extends: 'plugin:@graphql-eslint/operations-recommended',
            parserOptions: {
                skipGraphQLConfig: true,
                operations: './src/**/*.gql',
                schema: ['./schema.json', './src/graphql/client/schema.graphql'],
            },
            rules: {
                '@graphql-eslint/naming-convention': ['error', {
                    FieldDefinition: { forbiddenPrefixes: ['Query', 'Mutation', 'Subscription'] },
                    allowLeadingUnderscore: true,
                }],
                '@graphql-eslint/selection-set-depth': ['error', { maxDepth: 12 }],
                '@graphql-eslint/known-directives': ['error', {
                    ignoreClientDirectives: ['client', 'connection', 'defer', 'export', 'nonreactive'],
                }],
            },
        },
    ],
    rules: {
        'arrow-body-style': 'off',
        'comma-dangle': ['error', {
            arrays: 'always-multiline',
            objects: 'always-multiline',
            imports: 'always-multiline',
            exports: 'always-multiline',
            functions: 'never',
        }],
        curly: ['error', 'all'],
        'function-paren-newline': 'off',
        'import/extensions': ['error', 'ignorePackages'],
        'import/no-unresolved': 'off',
        'import/order': ['error', {
            groups: [
                'builtin',
                'external',
                ['parent', 'sibling', 'internal'],
                'index',
            ],
        }],
        indent: ['error', 4, {
            // Template literals need to be ignored otherwise it complains about
            // the dynamic splitting for language files in i18n.js. Hopefully it
            // is a bug that will be fixed in a later version.
            // It is unlikely that a developer would be indenting properly and
            // then miss the template literal and it is also rare for template
            // literals to extend over multiple lines so it should be safe to
            // ignore them for now. If it becomes a problem then we need to
            // rethink things.
            ignoredNodes: ['TemplateLiteral'],
        }],
        'max-len': ['error', 120],
        'no-bitwise': 'off',
        'no-console': process.env.NODE_ENV === 'production' ? 'error' : 'warn',
        'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'warn',
        'no-restricted-syntax': 'off',
        'es/no-regexp-lookbehind-assertions': 'error',
        'no-underscore-dangle': 'off',
        'no-unused-vars': process.env.NODE_ENV === 'production' ? 'error' : 'warn',
        'prefer-destructuring': 'off',
        'spaced-comment': 'off',
        // This needs to be off otherwise it complains about the dynamic code
        // splitting for language files in i18n.js. Hopefully it is a bug that
        // will be fixed in a later version. This is a rare mistake for a
        // developer to make so it should be safe to turn it off for now. If it
        // becomes a problem then we need to rethink things.
        'template-curly-spacing': 'off',
        'vue/array-bracket-spacing': 'error',
        'vue/arrow-spacing': 'error',
        'vue/attribute-hyphenation': 'off',
        'vue/attributes-order': ['error', {
            order: [
                'CONDITIONALS',
                'LIST_RENDERING',
                'UNIQUE',
                'DEFINITION',
                'RENDER_MODIFIERS',
                'GLOBAL',
                'SLOT',
                'TWO_WAY_BINDING',
                'OTHER_DIRECTIVES',
                'OTHER_ATTR',
                'EVENTS',
                'CONTENT',
            ],
            alphabetical: false,
        }],
        'vue/block-spacing': 'error',
        'vue/brace-style': 'error',
        'vue/camelcase': 'error',
        'vue/comma-dangle': ['error', {
            arrays: 'always-multiline',
            objects: 'always-multiline',
            imports: 'always-multiline',
            exports: 'always-multiline',
            functions: 'never',
        }],
        'vue/component-name-in-template-casing': ['error', 'PascalCase', { registeredComponentsOnly: true }],
        'vue/eqeqeq': 'error',
        'vue/html-indent': ['error', 4],
        'vue/html-self-closing': 'off',
        'vue/key-spacing': 'error',
        'vue/match-component-file-name': 'error',
        'vue/max-len': ['error', { code: 120 }],
        'vue/multiline-html-element-content-newline': 'off',
        'vue/multi-word-component-names': 'off',
        'vue/no-boolean-default': 'error',
        'vue/order-in-components': ['warn', {
            order: [
                'el',
                'name',
                ['template', 'render'],
                'parent',
                'functional',
                ['delimiters', 'comments'],
                ['components', 'directives', 'filters'],
                'extends',
                'mixins',
                'inheritAttrs',
                'model',
                ['props', 'propsData'],
                'emits',
                'apollo',
                'fetch',
                'asyncData',
                'data',
                'computed',
                'methods',
                'watch',
                'LIFECYCLE_HOOKS',
                'head',
                'renderError',
            ],
        }],
        'vue/require-component-is': 'off',
        'vue/space-infix-ops': 'error',
        'vue/v-on-function-call': 'error',
        'vue/v-on-event-hyphenation': 'off',
        'vuejs-accessibility/alt-text': 'off', // Consider turning back on
        'vuejs-accessibility/anchor-has-content': [
            'error',
            {
                accessibleChildren: ['UseItem'],
            },
        ],
        'vuejs-accessibility/click-events-have-key-events': 'off', // Consider turning back on
        'vuejs-accessibility/form-control-has-label': 'off', // Consider turning back on
        'vuejs-accessibility/heading-has-content': 'off',
        'vuejs-accessibility/label-has-for': 'off', // Consider turning back on
        'vuejs-accessibility/mouse-events-have-key-events': 'off', // Consider turning back on
        '@intlify/vue-i18n/no-dynamic-keys': 'off',
        '@intlify/vue-i18n/no-missing-keys': 'error',
        // '@intlify/vue-i18n/no-raw-text': ['warn', {
        //     'ignorePattern': '^[-#:()&.]+$',
        // }],
        '@intlify/vue-i18n/no-raw-text': 'off',
        '@intlify/vue-i18n/no-unused-keys': ['error', {
            extensions: ['.js', '.vue'],
        }],
    },
    settings: {
        'vue-i18n': {
            localeDir: './src/locales/*.json',
        },
    },
    globals: {
        _: false,
        utils: false,
        Stripe: false,
        _paq: false,
    },
};
