function buildSelector(actionType) {
    switch (true) {
    case actionType === 'UPDATE':
        return 'sky-600';
    case actionType.startsWith('CHANGE'):
        return 'sky-400';
    case actionType === 'CREATE':
        return 'emerald-600';
    case actionType.startsWith('ADD'):
        return 'emerald-400';
    case actionType === 'DELETE':
        return 'peach-600';
    case actionType.startsWith('REMOVE'):
        return 'peach-400';
    case actionType === 'RESTORE':
        return 'violet-600';
    case actionType.startsWith('RESTORE'):
        return 'violet-400';
    default:
        return 'sky-600';
    }
}
export default {
    methods: {
        historyBgColor(actionType) {
            return `bg-${buildSelector(actionType)}`;
        },
        historyBorderColor(actionType) {
            return `border-${buildSelector(actionType)}`;
        },
    },
};
