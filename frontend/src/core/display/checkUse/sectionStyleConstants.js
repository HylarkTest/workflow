export const sectionSetting = {
    1: {
    },
    2: {
        headerBorderPlacement: 'b',
        headerBorderColor: 'gray-400',
        headerTextColor: 'brandDark',
    },
    3: {
        headerBgColor: 'grayLight',
    },
    4: {
        headerTextColor: 'grayLight',
        fullBorderPlacement: 'all',
        fullBorderColor: 'gray-400',
    },
    5: {
        contentBgColor: 'grayLight',
    },
    6: {
        headerBorderPlacement: 'y',
        headerBorderColor: 'gray-400',
        headerTextColor: 'grayLight',
    },
};

export const sectionDefault = () => {
    return {
        headerTextWeight: 'bold',
        headerTextColor: 'grayDark',
    };
};

export const sectionUsing = {
    fullBorderPlacement: {
        all: 'border border-solid',
    },
    fullBorderColor: {
        'gray-400': 'border-cm-400',
    },
    headerBgColor: {
        grayLight: 'bg-cm-200',
    },
    headerTextWeight: {
        bold: 'font-semibold',
    },
    headerTextSize: {
        lg: 'text-lg',
    },
    headerBorderPlacement: {
        y: 'border-b border-t border-solid',
        b: 'border-b border-solid',
    },
    headerBorderColor: {
        'gray-400': 'border-cm-400',
    },
    h4HeaderTextColor: {
        grayDark: 'text-cm-600',
        grayLight: 'text-cm-500',
        brandDark: 'text-azure-500',
        brandLight: 'text-azure-400',
    },
    h3HeaderTextColor: {
        grayDark: 'text-cm-800',
        grayLight: 'text-cm-700',
        brandDark: 'text-azure-700',
        brandLight: 'text-azure-600',
    },
    contentBgColor: {
        grayLight: 'bg-cm-200',
    },
};

export const specialStyles = {
    fullBorderPlacement: {
        all: 'p-3',
    },
};

// Numbers rather than pixels to allow addition/comparison
export const sectionWidthAdjustments = {
    fullBorderPlacement: {
        all: 54,
    },
};

export const subWidthAdjustments = {
    fullBorderPlacement: {
        all: 2,
    },
};

export default {};
