import AssigneesPicker from '@/components/pickers/AssigneesPicker.vue';

import { isActiveBaseCollaborative } from '@/core/repositories/baseRepository.js';
import updateAssignees from '@/core/repositories/assigningRepository.js';

export default {
    components: {
        AssigneesPicker,
    },
    computed: {
        assigneeGroupsObject() {
            return {}; // Add in component
        },
        assigneeGroups: {
            get() {
                return this.assigneeGroupsObject.assigneeGroups || [];
            },
            set(assigneeGroups) {
                updateAssignees(this.assigneeGroupsObject, assigneeGroups);
            },
        },
        isCollaborativeBase() {
            return isActiveBaseCollaborative();
        },
        showAssignees() {
            return this.isCollaborativeBase;
        },
    },
};
