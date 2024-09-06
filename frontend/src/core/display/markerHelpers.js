export function getMarkerComponent(type) {
    if (type === 'STATUS') {
        return 'StatusDisplay';
    }
    if (type === 'TAG') {
        return 'TagDisplay';
    }
    return 'StageDisplay';
}

export const notSingleExport = false;
