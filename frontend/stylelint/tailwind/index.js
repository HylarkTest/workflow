const stylelint = require('stylelint');

const rules = [
    require('./at-apply-multiline-properties'),
    require('./at-apply-properties-alphabetical-order'),
];

module.exports = rules.map((rule) => {
    return stylelint.createPlugin(rule.ruleName, rule);
});
