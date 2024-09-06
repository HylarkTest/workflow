<template>
    <Modal
        class="o-note-full-modal"
        containerClass="p-8 w-600p"
        v-bind="$attrs"
        :header="true"
        @closeModal="closeModal"
    >
        <template #header>
            {{ note.name }}
        </template>

        <div class="flex justify-end mb-2">
            <button
                class="button button-primary"
                type="button"
                @click="openEdit"
            >
                {{ $t('common.edit') }}
            </button>
        </div>
        <NoteFull
            :note="note"
        >
        </NoteFull>
    </Modal>
</template>

<script>

import NoteFull from './NoteFull.vue';

export default {
    name: 'NoteFullModal',
    components: {
        NoteFull,
    },
    mixins: [
    ],
    props: {
        note: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'closeModal',
        'openEdit',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        closeModal() {
            this.$emit('closeModal');
        },
        openEdit() {
            this.$emit('openEdit');
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-note-full-modal {

} */

</style>
