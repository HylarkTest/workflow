<template>
    <div
        class="c-displayer-assignees inline-flex"
        :class="displayClasses"
    >
        <AssigneesPicker
            v-model:assigneeGroups="assigneeGroups"
        >
        </AssigneesPicker>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import AssigneesPicker from '@/components/pickers/AssigneesPicker.vue';
import updateAssignees from '@/core/repositories/assigningRepository.js';

export default {
    name: 'DisplayerAssignees',
    components: {
        AssigneesPicker,
    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'ASSIGNEES',
        };
    },
    computed: {
        assigneeGroups: {
            get() {
                return this.dataValue || [];
            },
            set(assigneeGroups) {
                updateAssignees(this.item, assigneeGroups);
            },
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-assignees {

} */

</style>
