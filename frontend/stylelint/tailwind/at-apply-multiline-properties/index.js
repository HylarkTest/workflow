const stylelint = require('stylelint');
const isStandardSyntaxAtRule = require('stylelint/lib/utils/isStandardSyntaxAtRule');
const report = require('stylelint/lib/utils/report');

const ruleName = 'tailwind/at-apply-multiline-properties';

const messages = stylelint.utils.ruleMessages(ruleName, {
    expectedAfter: () => 'Expected @apply parameters to be on separate lines',
});

const rule = function ruleFactory(actual) {
    return function ruleCheck(root, result) {
        const validOptions = stylelint.utils.validateOptions(
            result,
            ruleName,
            {
                actual,
                possible: ['always', 'always-multi-properties'],
            }
        );

        if (!validOptions) {
            return;
        }

        root.walkAtRules((atRule) => {
            if (!isStandardSyntaxAtRule(atRule)) {
                return;
            }

            if (atRule.name !== 'apply') {
                return;
            }

            if (!atRule.raws.between.includes('\n') && atRule.raws.afterName.includes('\n')) {
                report({
                    message: messages.expectedAfter(),
                    node: atRule,
                    index: atRule.name.length,
                    result,
                    ruleName,
                });
                return;
            }

            if (actual === 'always-multi-properties' && !atRule.params.includes(' ')) {
                return;
            }

            if (atRule.params.match(/[^\s] +[^\s]/) || !atRule.raws.afterName.includes('\n')) {
                report({
                    message: messages.expectedAfter(),
                    node: atRule,
                    index: atRule.name.length,
                    result,
                    ruleName,
                });
            }
        });
    };
};

rule.ruleName = ruleName;
rule.messages = messages;
module.exports = rule;
