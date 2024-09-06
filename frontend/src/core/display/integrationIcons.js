// Icons for integrations

function isMicrosoft(val) {
    const terms = ['MICROSOFT', 'OUTLOOK'];
    return terms.includes(val);
}

function isApple(val) {
    return val === 'APPLE';
}

function isGoogle(val) {
    return val === 'GOOGLE';
}

export function getIntegrationIcon(val) {
    if (isApple(val)) {
        return 'fab fa-apple';
    }
    if (isMicrosoft(val)) {
        return 'fab fa-microsoft';
    }
    if (isGoogle(val)) {
        return 'fab fa-google';
    }
    return 'fal fa-server';
}

export default {};
