const testRule = require('stylelint-test-rule-tape');
const rule = require('..');

const { ruleName, messages } = rule;

testRule(rule, {
    ruleName,
    config: [true],
    accept: [
        {
            code: '@apply mb-8;',
        },
        {
            code: '@apply mb-8 mb-8;',
        },
        {
            code: '@apply mb-8 text-large;',
        },
        {
            code: '@apply mb-8 -text-large;',
        },
    ],
    reject: [
        {
            code: '@apply text-large mb-8',
            message: messages.expectedAfter(),
            line: 1,
            column: 6,
        },
    ],
});
