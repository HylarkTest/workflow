const phases = {
    ACTIVE: {
        color: 'violet',
    },
    WAITING_TO_START: {
        color: 'gold',
    },
    COMPLETED: {
        color: 'emerald',
    },
    OVERDUE: {
        color: 'peach',
    },
    NO_STATUS: {
        color: 'gray',
    },

};

export function getPhaseInfo(phase) {
    return phases[phase];
}

export default { getPhaseInfo };
