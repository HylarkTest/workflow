import _ from 'lodash';
import config from '@/core/config.js';

export function getInitials(text, getLast = false) {
    const words = _.words(text);
    let includedWords;
    if (getLast) {
        includedWords = [words[0], (words.length >= 2 ? words.slice(-1)[0] : '')];
    } else {
        includedWords = [words[0], (words[1] || '')];
    }
    const allInitials = includedWords.map((item) => {
        return _.split(item, '')[0];
    }).join('');
    return allInitials;
}

export function stripTags(text) {
    return text.replace(/(<([^>]+)>)/ig, '');
}

export function isEvenNumber(number) {
    return number % 2 === 0;
}

export function isOddNumber() {
    return !isEvenNumber();
}

export function isValueFilled(val) {
    return val || _.isBoolean(val) || _.isNumber(val);
}

export function areSomeValuesFilled(arr) {
    return _.some(arr, isValueFilled);
}

export function arrRemoveIndex(arr, index) {
    return [...arr.slice(0, index), ...arr.slice(index + 1)];
}

export function arrReplaceIndex(arr, index, item) {
    return [...arr.slice(0, index), item, ...arr.slice(index + 1)];
}

export function arrReplaceId(arr, id, item, key = 'id') {
    const index = _(arr).findIndex([key, id]);
    if (~index) {
        return arrReplaceIndex(arr, index, item);
    }
    return arr.slice();
}

export function arrReplaceOrPushId(arr, id, item, key = 'id') {
    const index = _(arr).findIndex([key, id]);
    if (~index) {
        return arrReplaceIndex(arr, index, item);
    }
    return [...arr, item];
}

// When arr includes the items directly
// Does not modify original array, set value as new array
// Use when emitting new value to parent to not modify a prop
export function arrRemove(arr, item) {
    const index = arr.indexOf(item);
    if (~index) {
        return arrRemoveIndex(arr, index);
    }
    return arr.slice();
}

// When arr includes objects and finds the id
// Does not modify original array, set value as new array
// Use when emitting new value to parent to not modify a prop
export function arrRemoveId(arr, id, key = 'id') {
    const index = _(arr).findIndex([key, id]);
    if (~index) {
        return arrRemoveIndex(arr, index);
    }
    return arr.slice();
}

// When arr includes objects and finds the id
// Modifies the original array
// Use when there are no props involved
export function arrRemoveIdModify(arr, id, key = 'id') {
    const index = _(arr).findIndex([key, id]);
    if (~index) {
        arr.splice(index, 1);
    } else {
        arr.splice();
    }
}

export function mergeAndKeep(secondary, prime) {
    return _.assign(secondary, prime);
}

export function cloneFields(fields, source) {
    const target = {};
    Object.keys(fields).forEach((key) => {
        const sourceValue = source && source[key];
        const fieldValue = fields[key];
        if (_.isPlainObject(sourceValue) && _.isPlainObject(fieldValue) && !_.isEmpty(fieldValue)) {
            target[key] = cloneFields(fieldValue, sourceValue);
        } else {
            target[key] = sourceValue || fieldValue;
        }
    });
    return target;
}

export function randomNumber(min = 0, max = 1000, digits = 3) {
    // Max and min inclusive
    return (Math.random() * (max - min + 1) + min).toFixed(digits);
}

export function randomInt(min = 0, max = 1000) {
    return parseInt(randomNumber(min, max, 0), 10);
}

export function environment(env) {
    const currentEnv = config('app.env');
    if (env) {
        return currentEnv === env;
    }
    return currentEnv;
}

export const isProduction = environment('production');

export const pascalCase = _.flow([_.camelCase, _.upperFirst]);

export const upperSnake = _.flow([_.snakeCase, _.toUpper]);

export const firstKey = _.flow([_.keys, _.first]);

export function getFirstKey(object) {
    return object[firstKey(object)];
}

export function report(error) {
    if (!isProduction) {
        // eslint-disable-next-line no-console
        console.error(error);
    }
}

export function instantiate(obj, className, observer = null) {
    const instance = Object.create(className.prototype);
    _.forEach(obj, (value, key) => {
        instance[key] = value;
    });
    if (observer && _.isFunction(instance.watch)) {
        instance.watch(observer);
    }
    return instance;
}

// dec2hex :: Integer -> String
// i.e. 0-255 -> '00'-'ff'
function dec2hex(dec) {
    return dec.toString(16).padStart(2, '0');
}

// generateId :: Integer -> String
export function generateId(len) {
    const arr = new Uint8Array((len || 40) / 2);
    window.crypto.getRandomValues(arr);
    return Array.from(arr, dec2hex).join('');
}

export function obfuscate(obj, exclude = []) {
    const redacted = _.isArray(obj) ? [] : {};

    _.forEach(obj, (value, key) => {
        if (_.some(exclude, (pattern) => pattern.test(key))) {
            redacted[key] = value;
        } else {
            switch (typeof value) {
            case 'string':
                redacted[key] = `[string of length: ${value.length}]`;
                break;
            case 'object':
                redacted[key] = obfuscate(value);
                break;
            default:
                redacted[key] = `[${typeof value}]`;
                break;
            }
        }
    });

    return redacted;
}

export function getStrAroundToken(str, token) {
    const tokenIndex = str.indexOf(token);
    return [str.substring(0, tokenIndex), str.substring(tokenIndex + token.length)];
}

function replaceParens(text, paren, tag) {
    return text.replace(new RegExp(`${paren}(.+?)${paren}`, 'g'), `<${tag}>$1</${tag}>`);
}

function parseBold(text) {
    return replaceParens(text, '\\*\\*', 'strong');
}

function parseItalic(text) {
    return replaceParens(text, '__', 'em');
}

function parseLink(text) {
    return text.replace(/\[(.+?)\]\((.+?)\)/g, '<a href="$2">$1</a>');
}

export const parseMarkdown = _.flow([stripTags, parseBold, parseItalic, parseLink]);

// Changes the direction of the relationship type
// e.g. 'ONE_TO_MANY' -> 'MANY_TO_ONE'
export function reverseRelationshipType(type) {
    return type.split('_').reverse().join('_');
}

export function setDocumentTitle(title) {
    document.title = title || config('app.name');
}

export function decodeGlobalId(id) {
    return atob(id).split(':');
}

let calledFunctions = Promise.resolve();
// Returns a function that will wait for previous calls to the same function to
// complete before executing
export function awaitCall(fn) {
    return (...args) => {
        calledFunctions = calledFunctions.then(() => fn.apply(this, args));
        return calledFunctions;
    };
}

export function sleep(milliseconds) {
    return new Promise((resolve) => {
        setTimeout(resolve, milliseconds);
    });
}

// Retry a function a number of times with an optional delay between each attempt
// `times` can either be a number of attempts or an array of delays between each
// attempt.
export async function retry(times, callback, sleepMilliseconds, attempts = 0) {
    let backoff = [];
    let remainingTimes = times;

    if (_.isArray(times)) {
        backoff = times;
        remainingTimes = times.length + 1;
    }

    // eslint-disable-next-line no-param-reassign
    attempts += 1;
    remainingTimes -= 1;

    try {
        return await callback(attempts);
    } catch (e) {
        if (remainingTimes < 1) {
            throw e;
        }

        const sleepTime = backoff[attempts - 1] ?? sleepMilliseconds;

        if (sleepTime) {
            await sleep(sleepTime);
        }

        return retry(
            _.isArray(times) ? backoff.slice(attempts) : remainingTimes,
            callback,
            sleepMilliseconds,
            attempts
        );
    }
}

export function isRemString(value) {
    return _.isString(value) && value.endsWith('rem');
}

export function remToPx(rem) {
    if (isRemString(rem)) {
        const remValue = parseFloat(rem.slice(0, -3));
        const fontSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
        return remValue * fontSize;
    }

    return null;
}

export function getSectionForAssignees(str) {
    // Find the position of the last occurrence of '!!'
    // The index returned is the first ! of the !! set reading left to right
    const lastExclamationIndex = str.lastIndexOf('!!');

    // If '!!' is not found, return an empty string
    if (!~lastExclamationIndex) {
        return '';
    }

    // Check that there isn't an exclamation mark just before
    // as that means it's probably excitement and not assigning
    if (str.charAt(lastExclamationIndex - 1) === '!') {
        return '';
    }

    // Grab string section between string end and !! inclusive
    const relevant = str.substring(lastExclamationIndex).trim();

    // Find the position of the last space in the string
    const lastSpaceIndex = relevant.lastIndexOf(' ');
    const end = ~lastSpaceIndex ? lastSpaceIndex : (relevant.length - 1);

    // It might seem a bit odd to remove one and add one, but that preserves
    // the intention. We remove one to get the index. We add one because we want substring to
    // be inclusive. Individually, each step is correct. I think...

    // Extract the substring between the last space and '!!' inclusive
    const section = relevant.substring(0, (end + 1));

    // Check if the section after '!!' contains any spaces
    if (section.includes(' ')) {
        return '';
    }

    return section;
}
