// All of the brand, user, and additional colors used in the site
import _ from 'lodash';
import { hslToHex, hexToRgb, hexToHsl } from '@/core/colorUtils.js';

function lightToDark(lightObj, darkObj) {
    const fullDark = {
        50: darkObj?.[50] || lightObj[950],
        100: darkObj?.[100] || lightObj[900],
        200: darkObj?.[200] || lightObj[800],
        300: darkObj?.[300] || lightObj[700],
        400: darkObj?.[400] || lightObj[600],
        500: darkObj?.[500] || lightObj[500],
        600: darkObj?.[600] || lightObj[400],
        700: darkObj?.[700] || lightObj[300],
        800: darkObj?.[800] || lightObj[200],
        900: darkObj?.[900] || lightObj[100],
        950: darkObj?.[950] || lightObj[50],
        main: darkObj?.main || lightObj.main,
    };
    if (darkObj?.[1000] || lightObj['00']) {
        fullDark[1000] = darkObj?.[1000] || lightObj['00'];
    }

    const zero = darkObj?.['00'] || lightObj['00'];

    if (zero) {
        fullDark['00'] = zero;
    }

    return fullDark;
}

export const accentColorsBasic = [
    {
        val: 'intenseBlue', // OLD: #381bf3
        complementary: 'gold',
        light: {
            50: '#fafaff',
            100: '#f0f0ff',
            200: '#dbdeff',
            300: '#b8b9ff',
            400: '#8a8aff',
            500: '#6257ff',
            600: '#341aff', // Main
            700: '#2f14e1',
            800: '#2811bb',
            900: '#221098',
            950: '#100665',
            main: '#351aff',
        },
    },
    {
        val: 'electricPurple', // OLD: #6e1bf3
        complementary: 'ochre',
        light: {
            50: '#fbfaff',
            100: '#f3f0ff',
            200: '#e2dbff',
            300: '#c8b8ff',
            400: '#a98aff',
            500: '#8c57ff',
            600: '#751fff', // Main
            700: '#6413dd',
            800: '#5311b6',
            900: '#441094',
            950: '#270660',
            main: '#751fff',
        },
    },
    {
        val: 'electricViolet', // OLD: #a41bf3
        complementary: 'goldTips',
        light: {
            50: '#fdfaff',
            100: '#f8ebff',
            200: '#efd6ff',
            300: '#e4b8ff',
            400: '#d085ff',
            500: '#bf52ff',
            600: '#a41bf3', // Main
            700: '#8b18c9',
            800: '#7018a0',
            900: '#5c1480',
            950: '#3d025a',
            main: '#a41bf3',
        },
    },
    {
        val: 'fuchsia', // OLD: #da1bf3
        complementary: 'lime',
        light: {
            50: '#fefaff',
            100: '#fdf0fe',
            200: '#f8d3fd',
            300: '#f5abfc',
            400: '#f175fa',
            500: '#e63ff8',
            600: '#da1bf3', // Main
            700: '#a506b7',
            800: '#860891',
            900: '#6d0b74',
            950: '#430047',
            main: '#da1bf3',
        },
    },
    {
        val: 'magenta', // OLD: #f31bd6
        complementary: 'chateauGreen',
        light: {
            50: '#fffafe',
            100: '#fef0fd',
            200: '#fdd3f8',
            300: '#fcabef',
            400: '#fa75e2',
            500: '#f83fdc',
            600: '#f31bd6', // Main
            700: '#bc06a1',
            800: '#96087e',
            900: '#790c63',
            950: '#4d003e',
            main: '#f31bd6',
        },
    },
    {
        val: 'amaranth', // OLD: #f31b58
        complementary: 'turquoiseGreen',
        light: {
            50: '#fffafb',
            100: '#ffebf1',
            200: '#fed7e3',
            300: '#feaac4',
            400: '#fd77a4',
            500: '#ff1f71', // Main
            600: '#db0f64',
            700: '#b50853',
            800: '#96084a',
            900: '#7b0a42',
            950: '#3d011d',
            main: '#ff1f71',
        },
    },
    {
        val: 'brightRed', // OLD: #f3261b
        complementary: 'brightTurquoise',
        light: {
            50: '#fffafa',
            100: '#feeceb',
            200: '#fdd0ce',
            300: '#fcaaa6',
            400: '#fb7670',
            500: '#f9473e',
            600: '#f3261b', // Bright red
            700: '#ba1108',
            800: '#951009',
            900: '#7b150f',
            950: '#360502',
            main: '#f3261b',
        },
    },
    {
        val: 'blazeOrange', // OLD: #f36e1b
        complementary: 'lochmara',
        light: {
            50: '#fffaf5',
            100: '#fef1e2',
            200: '#fbdab6',
            300: '#f7bb82',
            400: '#f4934e',
            500: '#f36e1b', // Main
            600: '#d04d11',
            700: '#a83810',
            800: '#852e14',
            900: '#682512',
            950: '#310f07',
            main: '#f36e1b',
        },
    },
    {
        val: 'ochre', // OLD: #6e1bf3
        complementary: 'ribbonBlue',
        light: {
            50: '#fefcf0',
            100: '#fcf3cf',
            200: '#f9e594',
            300: '#f5d15b',
            400: '#f3bc30',
            500: '#eda01d', // Main
            600: '#d2770f',
            700: '#a44f0e',
            800: '#823d12',
            900: '#693111',
            950: '#381705',
            main: '#eda01d',
        },
    },
    {
        val: 'goldTips', // OLD: #d9c10b
        complementary: 'intenseBlue',
        light: {
            50: '#fefdf6',
            100: '#f9f5d7',
            200: '#f3eaa5',
            300: '#ebda6b',
            400: '#e2c93c',
            500: '#dbc024', // Main
            600: '#c59f07',
            700: '#957209',
            800: '#7b5c0f',
            900: '#694f12',
            950: '#372806',
            main: '#dbc024',
        },
    },
    {
        val: 'lime', // OLD: #6caa09
        complementary: 'electricPurple',
        light: {
            50: '#fbfff0',
            100: '#f0fcd4',
            200: '#e0faa8',
            300: '#c6f36d',
            400: '#abe73c',
            500: '#8fd416',
            600: '#6caa09', // Main
            700: '#46700a',
            800: '#39580e',
            900: '#2f480f',
            950: '#132103',
            main: '#6caa09',
        },
    },
    {
        val: 'chateauGreen', // OLD: #09aa47
        complementary: 'electricViolet',
        light: {
            50: '#fafefc',
            100: '#e7feef',
            200: '#c2fad5',
            300: '#8bf4b1',
            400: '#4fe388',
            500: '#1dd363',
            600: '#09aa47', // Main
            700: '#0a7b37',
            800: '#0d5e2d',
            900: '#0c4b26',
            950: '#012310',
            main: '#09aa47',
        },
    },
    {
        val: 'turquoiseGreen', // OLD: #09aa97
        complementary: 'fuchsia',
        light: {
            50: '#fbfefd',
            100: '#d4fcf3',
            200: '#a1f7e4',
            300: '#66ebd3',
            400: '#34d5bd',
            500: '#14c2ab',
            600: '#09aa97', // Main
            700: '#0a6b62',
            800: '#0c554e',
            900: '#0e433e',
            950: '#012220',
            main: '#09aa97',
        },
    },
    {
        val: 'brightTurquoise', // OLD: #099ab0
        complementary: 'magenta',
        light: {
            50: '#faffff',
            100: '#defcfc',
            200: '#baf5f7',
            300: '#7eebf1',
            400: '#3cd7e2',
            500: '#14c6d7',
            600: '#0998ae', // Main
            700: '#0d6c7d',
            800: '#135563',
            900: '#124754',
            950: '#062932',
            main: '#0998ae',
        },
    },
    {
        val: 'lochmara', // OLD: #0e9bf2
        complementary: 'amaranth',
        light: {
            50: '#fafdff',
            100: '#ecf5fe',
            200: '#c5e5fc',
            300: '#88cdfb',
            400: '#46b3f6',
            500: '#1299ed', // Main
            600: '#0174c6',
            700: '#015492',
            800: '#064675',
            900: '#0b3b60',
            950: '#07223c',
            main: '#1299ed',
        },
    },
    {
        val: 'ribbonBlue', // OLD: #1b6af3
        complementary: 'brightRed',
        light: {
            50: '#fafcff',
            100: '#e5eeff',
            200: '#c7dcff',
            300: '#99c5ff',
            400: '#66a8ff',
            500: '#3e8bfe',
            600: '#1b6af3', // Main
            700: '#1a63ea',
            800: '#1954c2',
            900: '#1c519b',
            950: '#183763',
            main: '#1b6af3',
        },
    },
    {
        val: 'steel', // OLD: #747c8b
        complementary: 'blazeOrange',
        light: {
            50: '#f9fafb',
            100: '#f0f3f4',
            200: '#e1e7ea',
            300: '#ced5da',
            400: '#b6c0c8',
            500: '#a3acb8',
            600: '#8d96a5', // Main
            700: '#6f7785',
            800: '#595f69',
            900: '#4a4f55',
            950: '#282a2f',
            main: '#8d96a5',
        },
    },
    {
        val: 'hemp', // OLD: #8b7474
        complementary: 'gold',
        light: {
            50: '#fafafa',
            100: '#f6f4f4',
            200: '#ebe5e5',
            300: '#d9cecf',
            400: '#bdadae',
            500: '#a59293',
            600: '#8b7474', // Main
            700: '#6c5b5a',
            800: '#594b4a',
            900: '#4a403f',
            950: '#272020',
            main: '#8b7474',
        },
    },
];

export const accentColors = accentColorsBasic.map((color) => {
    return {
        ...color,
        dark: lightToDark(color.light, color.dark),
    };
});

const supportColorsBasic = [
    {
        val: 'azure',
        light: {
            50: '#fafbff',
            100: '#ebefff',
            200: '#ccd5ff',
            300: '#a8b4ff',
            400: '#8084ff',
            500: '#615cff',
            600: '#4229ff', // Main
            700: '#351cd9',
            800: '#2b1aa8',
            900: '#271d81',
            950: '#171047',
            main: '#4229ff',
        },
    },
    {
        val: 'gold',
        light: {
            50: '#fefef0',
            100: '#fefccd',
            200: '#fef795',
            300: '#fde94e',
            400: '#fdd71c',
            500: '#f5c505',
            600: '#cc9200', // Main
            700: '#a46904',
            800: '#88520c',
            900: '#754410',
            950: '#422205',
            main: '#cc9200',
        },
    },
    {
        val: 'turquoise',
        light: {
            50: '#f7fdfb',
            100: '#d6f5ee',
            200: '#a9eadc',
            300: '#73decc', // Main
            400: '#40bfae',
            500: '#27a596',
            600: '#1d877c',
            700: '#1b6962',
            800: '#1b5550',
            900: '#1b4643',
            950: '#092a29',
            main: '#73decc',
        },
    },
    {
        val: 'violet',
        light: {
            50: '#fcfaff',
            100: '#f3ebff',
            200: '#e7d7fe',
            300: '#d6b9fe',
            400: '#ba86fd',
            500: '#9142ff', // Main
            600: '#791fef',
            700: '#681bc5',
            800: '#571a9e',
            900: '#48167e',
            950: '#2c0359',
            main: '#9142ff',
        },
    },
    {
        val: 'sky',
        light: {
            50: '#f5faff',
            100: '#e1f0ff',
            200: '#c2e1fe',
            300: '#95cffe',
            400: '#5db0fd',
            500: '#4294ff', // Main
            600: '#0a60f5',
            700: '#124fd3',
            800: '#1541a8',
            900: '#173982',
            950: '#11214b',
            main: '#4294ff',
        },
    },
    {
        val: 'rose',
        light: {
            50: '#fffafb',
            100: '#fff0f3',
            200: '#fdd8e0', // Main
            300: '#fbb2c2',
            400: '#f77e9c',
            500: '#f45781',
            600: '#d31d56',
            700: '#b01146',
            800: '#921140',
            900: '#78123b',
            950: '#3d051b',
            main: '#fdd8e0',
        },
    },
    {
        val: 'peach',
        light: {
            50: '#fef6f6',
            100: '#fee8e6',
            200: '#fed0cd',
            300: '#fcb2ab',
            400: '#fc8d83', // Main
            500: '#f34030',
            600: '#d72d1d',
            700: '#b02417',
            800: '#8d2016',
            900: '#772018',
            950: '#370a06',
            main: '#fc8d83',
        },
    },
    {
        val: 'emerald',
        light: {
            50: '#f2fdf7',
            100: '#defcee',
            200: '#bbf7da',
            300: '#86eebc',
            400: '#4ade99',
            500: '#1fdb83', // Main
            600: '#10a25e',
            700: '#117e4b',
            800: '#12633d',
            900: '#115035',
            950: '#042a1a',
            main: '#1fdb83',
        },
    },
];

const supportColors = supportColorsBasic.map((color) => {
    return {
        ...color,
        dark: lightToDark(color.light, color.dark),
    };
});

const grayScaleBasic = {
    val: 'gray',
    dark: {
        '00': '#000000',
        1000: '#ffffff',
    },
    light: {
        50: '#fcfcfd',
        100: '#f3f5f7',
        200: '#dbe0e6',
        300: '#b6c0cd',
        400: '#8d9cb0',
        500: '#6a7e95',
        600: '#516176', // Main
        700: '#414f62',
        800: '#394451',
        900: '#333c47',
        950: '#22272f',
        '00': '#ffffff',
        1000: '#000000',
        main: '#516176',
    },
};

export const grayScale = {
    ...grayScaleBasic,
    dark: lightToDark(grayScaleBasic.light, grayScaleBasic.dark),
};

// The colours for the marker and list color pickers

const start = '#cd323a';
const hslStart = hexToHsl(start);
const steps = _.range(0, 360, 360 / 20);
const startingColors = steps.map((step) => {
    return {
        ...hslStart,
        h: (hslStart.h + step) % 360,
    };
}).concat([hexToHsl('#747C8B'), hexToHsl('#8B7474')]);

const lowestLightness = 13;
const highestLightness = 100;
const lightnessDifference = (highestLightness - lowestLightness) / 2;
const lightnessSteps = 6;
const desaturationFactor = 0.6;

const range = _.range(
    lowestLightness + (lightnessDifference / 2),
    highestLightness - (lightnessDifference / 2),
    (lightnessDifference / lightnessSteps)
);

const brightColors = startingColors.flatMap((color) => {
    return range.map((lightness) => {
        return {
            ...color,
            l: lightness,
        };
    });
});
const regularColors = startingColors.flatMap((color) => {
    return range.map((lightness) => {
        return {
            ...color,
            s: color.s * desaturationFactor,
            l: lightness,
        };
    });
});

export const extraList = {
    light: {
        bright: brightColors.map((color) => {
            return {
                val: hslToHex(color),
                hex: hslToHex({
                    ...color,
                    l: color.l - (lightnessDifference / 2),
                }),
            };
        }),
        regular: regularColors.map((color) => {
            return {
                val: hslToHex(color),
                hex: hslToHex({
                    ...color,
                    l: color.l - (lightnessDifference / 2),
                }),
            };
        }),
    },
    dark: {
        bright: brightColors.map((color) => {
            return {
                val: hslToHex(color),
                hex: hslToHex({
                    ...color,
                    l: color.l + (lightnessDifference / 2),
                }),
            };
        }),
        regular: regularColors.map((color) => {
            return {
                val: hslToHex(color),
                hex: hslToHex({
                    ...color,
                    l: color.l + (lightnessDifference / 2),
                }),
            };
        }),
    },
};

// End colors for pickers

export function buildColorObj(color, index, type) {
    let hsl = color;
    let hex = color;
    if (_.isString(color)) {
        hsl = hexToHsl(color);
    } else {
        hex = hslToHex(color);
    }
    const light = index && type ? extraList.light[type][index].hex : hslToHex({
        ...hsl,
        l: hsl.l - (lightnessDifference / 2),
    });
    const dark = index && type ? extraList.dark[type][index].hex : hslToHex({
        ...hsl,
        l: hsl.l + (lightnessDifference / 2),
    });

    return {
        val: hex,
        light: {
            100: dark,
            600: light,
        },
        dark: {
            100: light,
            600: dark,
        },
    };
}

export const extraColors = brightColors.map((color, index) => buildColorObj(color, index, 'bright'))
    .concat(regularColors.map((color, index) => buildColorObj(color, index, 'regular')));

['light', 'dark'].forEach((mode) => {
    const modeObj = extraList[mode];
    ['bright', 'regular'].forEach((type) => {
        modeObj[type] = _.chunk(modeObj[type], lightnessSteps);
    });
});

export function randomExtraColor() {
    const length = extraColors.length;
    const index = _.random(0, (length - 1));
    return extraColors[index].val;
}

export const allColors = _.concat(accentColors, supportColors, [grayScale]);

export default {
    accentColors,
    grayScale,
    allColors,
    extraColors,
    extraList,
    randomExtraColor,
};

export function getColorObj(colorObj, colorMode) {
    const lightObj = colorObj.light;
    if (colorMode === 'light') {
        return lightObj;
    }
    return lightToDark(lightObj, colorObj.dark);
}

export const defaultAccentColor = accentColors[0].val;

const percentages = ['03', '05', '08', '10', '15', '20', '30', '40', '50', '60', '70', '80', '90'];

export function createAccentClasses(accent, colorMode = 'light') {
    const modeScale = colorMode === 'LIGHT'
        ? grayScale.light
        : lightToDark(grayScale.light, grayScale.dark);

    const colorInfo = _.find(accentColors, ['val', accent]) || accentColors[0];

    const modeLowerCase = colorMode.toLowerCase();

    const intensities = colorInfo[modeLowerCase];

    const complementaryInfo = _.find(accentColors, ['val', colorInfo.complementary])
        || _.find(supportColors, ['val', colorInfo.complementary]);

    const complementaryIntensities = complementaryInfo[modeLowerCase];

    const cssVars = _(intensities)
        .flatMap((color, intensity) => {
            const { r, g, b } = hexToRgb(color);
            return [
                `--hl-primary-color-${intensity}: ${color};`,
                ...percentages.map((percentage) => {
                    return `--hl-primary-color-${intensity}-${percentage}:
                        rgba(${r}, ${g}, ${b}, ${(percentage / 100).toString()});`;
                }),
            ];
        })
        .concat(_.flatMap(complementaryIntensities, (color, intensity) => {
            const { r, g, b } = hexToRgb(color);
            return [
                `--hl-secondary-color-${intensity}: ${color};`,
                ...percentages.map((percentage) => {
                    return `--hl-secondary-color-${intensity}-${percentage}:
                        rgba(${r}, ${g}, ${b}, ${(percentage / 100).toString()});`;
                }),
            ];
        }))
        .concat(_.flatMap(modeScale, (color, intensity) => {
            const { r, g, b } = hexToRgb(color);
            return [
                `--hl-cm-color-${intensity}: ${color};`,
                ...percentages.map((percentage) => {
                    return `--hl-cm-color-${intensity}-${percentage}:
                                rgba(${r}, ${g}, ${b}, ${(percentage / 100).toString()});`;
                }),
            ];
        }))
        .concat(_.flatMap(supportColors, (supportColor) => {
            const name = supportColor.val;
            const colors = supportColor[modeLowerCase];

            return _.flatMap(colors, (color, intensity) => {
                // Remove this when all colors are filled in
                if (!color) {
                    return [];
                }
                const { r, g, b } = hexToRgb(color);
                return [
                    `--hl-color-${name}-${intensity}: ${color};`,
                    ...percentages.map((percentage) => {
                        return `--hl-color-${name}-${intensity}-${percentage}:
                        rgba(${r}, ${g}, ${b}, ${(percentage / 100).toString()});`;
                    }),
                ];
            });
        }))
        .join('\r\n');

    return `* {
        ${cssVars}
    }`;
}
