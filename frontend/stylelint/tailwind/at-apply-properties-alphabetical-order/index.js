const stylelint = require('stylelint');
const report = require('stylelint/lib/utils/report');
const _ = require('lodash');

const ruleName = 'tailwind/at-apply-properties-alphabetical-order';

const messages = stylelint.utils.ruleMessages(ruleName, {
    expectedAfter: () => 'Expected @apply parameters to be in alphabetical order',
});

const rule = function ruleFactory(actual, options, context = {}) {
    return function ruleCheck(root, result) {
        const validOptions = stylelint.utils.validateOptions(
            result,
            ruleName,
            {
                actual,
                possible: _.isBoolean,
            }
        );

        if (!validOptions) {
            return;
        }

        root.walkAtRules((atRule) => {
            if (atRule.name !== 'apply') {
                return;
            }

            const params = atRule.params.split('\n');

            const sorted = _.sortBy(params, (param) => param.replace(/\s+-?/, ''));
            if (!_.isEqual(params, sorted)) {
                if (context.fix) {
                    if (sorted.length) {
                        sorted[0] = _.trim(sorted[0]);
                    }
                    atRule.params = sorted.join('\n');
                } else {
                    report({
                        message: messages.expectedAfter(),
                        node: atRule,
                        index: atRule.name.length,
                        result,
                        ruleName,
                    });
                }
            }
        });
    };
};

rule.ruleName = ruleName;
rule.messages = messages;
module.exports = rule;
