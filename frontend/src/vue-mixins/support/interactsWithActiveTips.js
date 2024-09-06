import GET_ACTIVE_TIPS from '@/graphql/client/GetActiveTips.gql';

export default {
    apollo: {
        activeTips: {
            query: GET_ACTIVE_TIPS,
            client: 'defaultClient',
        },
    },
    methods: {
        getTip(tipVal) {
            return this.activeTips?.find((tip) => tip.val === tipVal);
        },
        isTipActive(tipVal) {
            const tip = this.getTip(tipVal);
            return tip?.active;
        },
    },
};
