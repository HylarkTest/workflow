<template>
    <div class="c-group-edit-modal">
        <Modal
            containerClass="w-3/4"
            :containerStyle="{ height: '80vh' }"
            @closeModal="$emit('closeModal')"
        >
            <GroupEdit
                :group="group"
                :groupType="groupType"
                :defaultTab="defaultTab"
                :customTabs="customTabs"
                :hideColor="hideColor"
                :hideDescription="hideDescription"
                :itemDisplayComponent="itemDisplayComponent"
                :repository="repository"
            >
            </GroupEdit>
        </Modal>
    </div>
</template>

<script>

import GroupEdit from '@/components/customize/GroupEdit.vue';

export default {
    name: 'GroupEditModal',
    components: {
        GroupEdit,
    },
    mixins: [
    ],
    props: {
        group: {
            type: Object,
            required: true,
        },
        groupType: {
            type: String,
            required: true,
        },
        defaultTab: {
            type: String,
            default: '',
        },
        customTabs: {
            type: [Array, null],
            default: null,
        },
        hideColor: Boolean,
        hideDescription: Boolean,
        itemDisplayComponent: {
            type: String,
            required: true,
        },
        repository: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'closeModal',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>
/* .c-group-edit-modal {

} */
</style>
