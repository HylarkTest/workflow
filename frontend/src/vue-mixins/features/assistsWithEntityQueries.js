export default {
    methods: {
        getRequestVariables(requestVariables) {
            const data = {
            };
            if (requestVariables.type) {
                data.type = requestVariables.type;
            }
            if (requestVariables.hasEmails) {
                data.hasEmails = requestVariables.hasEmails;
            }
            if (requestVariables.withFeatures) {
                data.withFeatures = requestVariables.withFeatures;
            }
            if (requestVariables.spaceId) {
                data.spaceId = requestVariables.spaceId;
            }
            if (requestVariables.mappingId) {
                data.mappingId = requestVariables.mappingId;
            }
            return data;
        },
    },
};
