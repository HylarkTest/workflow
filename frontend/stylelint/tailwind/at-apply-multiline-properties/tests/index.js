const stylelint = require('stylelint');
const test = require('tape');
const rule = require('..');

function assertEquality(processCss, context) {
    const testFn = (context.only) ? test.only : test;
    testFn(context.caseDescription, (t) => {
        t.plan(context.comparisonCount);
        processCss.then((comparisons) => {
            comparisons.forEach((comparison) => {
                t.equal(comparison.actual, comparison.expected, comparison.description);
            });
        });
    });
}

const testRule = stylelint.createRuleTester(assertEquality);

const { ruleName, messages } = rule;

testRule(rule, {
    ruleName,
    config: ['always'],
    accept: [
        {
            code: '@apply\nmb-8\n;',
        },
        {
            code: '@apply\n mb-8\n text-large\n;',
        },
        {
            code: '@import "something";',
        },
    ],
    reject: [
        {
            code: '@apply mb-8 text-large;',
            message: messages.expectedAfter(),
            line: 1,
            column: 6,
        },
        {
            code: '@apply\nmb-8\ntext-large px-8\n;',
            message: messages.expectedAfter(),
            line: 1,
            column: 6,
        },
    ],
});

testRule(rule, {
    ruleName,
    config: ['always-multi-properties'],
    accept: [
        {
            code: '@apply mb-8;',
        },
        {
            code: '@import "something";',
        },
    ],
    reject: [
        {
            code: '@apply mb-8 text-large;',
            message: messages.expectedAfter(),
            line: 1,
            column: 6,
        },
        {
            code: '@apply\n mb-8\n text-large;',
            message: messages.expectedAfter(),
            line: 1,
            column: 6,
        },
    ],
});
