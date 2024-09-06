import UPDATE_ASSIGNEES from '@/graphql/bases/UpdateAssignees.gql';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';

export default function updateAssignees(node, assigneeGroups) {
    return baseApolloClient().mutate({
        mutation: UPDATE_ASSIGNEES,
        variables: {
            input: {
                assignableId: node.id,
                assigneeGroups: assigneeGroups.map((assigneeInfo) => ({
                    groupId: assigneeInfo.groupId,
                    assignees: _.map(assigneeInfo.assignees, 'id'),
                })),
            },
        },
    });
}
