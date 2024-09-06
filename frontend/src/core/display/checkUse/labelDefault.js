export const labelStyle = () => {
    return {
        weight: 'bold',
        size: 'xxs',
        color: 'light',
        case: 'uppercase',
    };
};

export const labelDefault = () => {
    return {
        on: 'contentOnly',
        style: labelStyle(),
    };
};

export const fullLabelDefault = () => {
    return {
        style: labelStyle(),
    };
};

export default {};
