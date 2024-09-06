// May not use, provides some standard style keys used to style data and headers or labels
// CHECK AGAIN

export const textUsingOptions = {
    textSize: {
        xxs: 'text-xxs',
        xs: 'text-xs',
        sm: 'text-sm',
        md: 'text-base',
        lg: 'text-lg',
        xl: 'text-xl',
        '2xl': 'text-2xl',
        '3xl': 'text-3xl',
        '4xl': 'text-4xl',
    },
    textWeight: {
        bold: 'font-semibold',
    },
    textColor: {
        light: 'text-cm-600',
        brand: 'text-azure-600',
    },
    textItalics: {
        italic: 'italic',
    },
    textUnderline: {
        under: 'underline',
    },
    textTrim: {
        hyphen: 'u-hyphen',
        ellipsis: 'u-ellipsis',
    },
    textAlign: {
        center: 'text-center',
        end: 'text-right',
    },
    textCase: {
        uppercase: 'uppercase',
    },
};

// Possibly textSettingOptions should just be an array of the options
// and the actual classes should be taken from textUsingOptions,
// but for now continue with only textSettingOptions until it stops
// being feasible.

export const textSettingOptions = {
    textSize: {
        xxs: 'text-xxs',
        xs: 'text-xs',
        sm: '',
        md: 'text-base',
        lg: 'text-lg',
        xl: 'text-xl',
        '2xl': 'text-2xl',
        '3xl': 'text-3xl',
        '4xl': 'text-4xl',
    },
    textWeight: {
        regular: '',
        bold: 'font-semibold',
    },
    textColor: {
        light: 'text-cm-600',
        dark: 'text-cm-900',
        brand: 'text-azure-600',
    },
    textTrim: {
        wrap: '',
        ellipsis: 'u-ellipsis',
        hyphen: 'u-hyphen',
    },
    textItalics: {
        regular: '',
        italic: 'italic',
    },
    textUnderline: {
        regular: '',
        under: 'underline',
    },
    textAlign: {
        start: '',
        center: 'text-center',
        end: 'text-right',
    },
    textCase: {
        regular: '',
        uppercase: 'uppercase',
    },

};

export const graphicConstants = {
    xl: {
        circle: 'h-32 w-32',
        square: 'h-32 w-32',
        vRectangle: 'h-32 w-20',
        hRectangle: 'h-20 w-32',
        font: 'text-6xl',
    },
    lg: {
        circle: 'h-20 w-20',
        square: 'h-20 w-20',
        vRectangle: 'h-20 w-12',
        hRectangle: 'h-12 w-20',
        font: 'text-2xl',
    },
    md: {
        circle: 'h-12 w-12',
        square: 'h-12 w-12',
        vRectangle: 'h-12 w-8',
        hRectangle: 'h-8 w-12',
        font: 'text-lg',
    },
    sm: {
        circle: 'h-8 w-8',
        square: 'h-8 w-8',
        vRectangle: 'h-8 w-6',
        hRectangle: 'h-6 w-8',
        font: 'text-base',
    },
    xs: {
        circle: 'h-6 w-6',
        square: 'h-6 w-6',
        vRectangle: 'h-6 w-4',
        hRectangle: 'h-4 w-6',
        font: 'text-sm',
    },
};

export const miniGraphicConstants = {
    xl: {
        circle: 'h-12 w-12',
        square: 'h-12 w-12',
        vRectangle: 'h-12 w-8',
        hRectangle: 'h-8 w-12',
    },
    lg: {
        circle: 'h-8 w-8',
        square: 'h-8 w-8',
        vRectangle: 'h-8 w-5',
        hRectangle: 'h-5 w-8',
    },
    md: {
        circle: 'h-6 w-6',
        square: 'h-6 w-6',
        vRectangle: 'h-6 w-4',
        hRectangle: 'h-4 w-6',
    },
    sm: {
        circle: 'h-4 w-4',
        square: 'h-4 w-4',
        vRectangle: 'h-4 w-3',
        hRectangle: 'h-3 w-4',
    },
    xs: {
        circle: 'h-2 w-2',
        square: 'h-2 w-2',
        vRectangle: 'h-2 w-1',
        hRectangle: 'h-1 w-2',
    },
};

export const graphicSettingOptions = {
    shape: [
        'circle',
        'square',
        'vRectangle',
        'hRectangle',
    ],
    size: [
        'xs',
        'sm',
        'md',
        'lg',
        'xl',
    ],
};

export default {};
