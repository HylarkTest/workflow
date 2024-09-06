<template>
    <Modal
        class="o-data-edit-modal"
        containerClass="w-3/4"
        :containerStyle="{ height: '80vh' }"
        v-bind="$attrs"
        @closeModal="$emit('closeModal')"
    >
        <DataEdit
            :page="page"
            :defaultView="defaultView"
            :defaultTab="defaultTab"
            @closeModal="$emit('closeModal')"
        >
        </DataEdit>
    </Modal>
</template>

<script>

import DataEdit from '@/components/customize/DataEdit.vue';

export default {
    name: 'DataEditModal',
    components: {
        DataEdit,
    },
    mixins: [
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
        defaultView: {
            type: String,
            default: 'PAGE',
            validator(val) {
                return ['PAGE', 'MAPPING', 'FEATURE'].includes(val);
            },
        },
        defaultTab: {
            type: String,
            default: '',
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

/*.o-data-edit-modal {

} */

</style>
