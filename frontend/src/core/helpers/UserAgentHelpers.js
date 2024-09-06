const userAgent = navigator.userAgent.toLowerCase();

export function isWindows() {
    return ~userAgent.indexOf('windows');
}

export function isMac() {
    return ~userAgent.indexOf('mac');
}
