import currencies from '@/../currency_symbols.json';

export function getSymbol(code) {
    if (code && (_.isArray(code) ? code.length : true)) {
        return currencies[code.toUpperCase()];
    }
    return '';
}

export function formatCode(code) {
    return `${code} (${getSymbol(code)})`;
}

export function allCurrencies() {
    return _.map(currencies, (symbol, code) => ({ symbol, code }));
}
