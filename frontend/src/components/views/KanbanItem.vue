<template>
    <ButtonEl
        class="c-kanban-item bg-primary-100"
        @click="openFull"
    >
        <TemplateImage
            class="mr-2"
            :dataValue="info.image"
            :container="{}"
        >
        </TemplateImage>

        <div class="font-semibold">
            {{ info.name }}
        </div>
    </ButtonEl>

    <Modal
        v-if="isModalOpen"
        containerClass="p-4 w-10/12"
        @closeModal="closeModal"
    >
        <FullItem
            :fullItem="viewingItem"
            :page="page"
            @closeModal="closeModal"
        >
        </FullItem>
    </Modal>
</template>

<script>

import interactsWithViewsItem from '@/vue-mixins/views/interactsWithViewsItem.js';

export default {
    name: 'KanbanItem',
    components: {

    },
    mixins: [
        interactsWithViewsItem,
    ],
    props: {
    },
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

.c-kanban-item {
    @apply
        flex
        mb-1
        px-6
        py-2
        rounded-xl
    ;
}

</style>
