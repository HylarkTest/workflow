export const colorGrid = [
    {
        start: 0,
        end: 6,
        color: 'azure',
    },
    {
        start: 6,
        end: 12,
        color: 'turquoise',
    },
    {
        start: 12,
        end: 18,
        color: 'gold',
    },
    {
        start: 18,
        end: 24,
        color: 'violet',
    },
    {
        start: 24,
        end: 30,
        color: 'emerald',
    },
    {
        start: 30,
        end: 42,
        color: 'sky',
    },
    {
        start: 42,
        end: 56,
        color: 'rose',
    },
];

export function getCharNumber(char) {
    if (_.isString(char)) {
        const lowercase = char.toLowerCase();
        return lowercase.charCodeAt(0) - 96;
    }
    return 0;
}

function getColorNumber(firstNumber, secondNumber) {
    return firstNumber + secondNumber;
}

export function getColorName(firstChar, secondChar, defaultCondition) {
    const firstNumber = getCharNumber(firstChar);
    const secondNumber = getCharNumber(secondChar);

    const colorNumber = getColorNumber(firstNumber, secondNumber);

    if (defaultCondition) {
        return 'primary';
    }
    if (colorNumber < 0 || colorNumber > 54) {
        return 'peach';
    }
    const rangeObj = colorGrid.find((item) => {
        return _.inRange(colorNumber, item.start, item.end);
    });

    return rangeObj?.color || 'peach';
}
