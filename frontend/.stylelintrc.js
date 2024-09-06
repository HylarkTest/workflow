module.exports = {
    root: true,
    plugins: [
        'stylelint-no-unsupported-browser-features',
        'stylelint-order',
        './stylelint/tailwind',
    ],
    extends: [
        'stylelint-config-recommended-vue',
    ],
    rules: {
        'at-rule-empty-line-before': null,
        'at-rule-no-unknown': [
            true,
            {
                ignoreAtRules: [
                    'apply',
                    'else',
                    'if',
                    'include',
                    'layer',
                    'mixin',
                    'responsive',
                    'screen',
                    'tailwind',
                    'variants',
                    'config',
                ],
            },
        ],
        'block-closing-brace-empty-line-before': null,
        'declaration-empty-line-before': null,
        indentation: 4,
        'no-descending-specificity': null,
        'no-empty-first-line': null,
        'order/properties-alphabetical-order': true,
        'order/properties-order': [],
        'order/order': [
            {
                type: 'at-rule',
                name: 'apply',
            },
        ],
        'plugin/no-unsupported-browser-features': [true, {
            severity: 'warning',
            ignore: ['multicolumn'],
            browsers: [
                '> 5%',
                'last 2 firefox versions',
                'last 2 chrome versions',
                'last 2 safari versions',
                'last 2 edge versions',
            ],
        }],
        'selector-class-pattern': null,
        'tailwind/at-apply-multiline-properties': 'always-multi-properties',
        'tailwind/at-apply-properties-alphabetical-order': true,
        'value-keyword-case': null,
    },
};
