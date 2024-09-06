<template>
    <ButtonEl
        class="c-tile-item bg-primary-100"
        @click="openFull"
    >
        <TemplateImage
            class="mb-2"
            :dataValue="info.image"
            :container="{}"
        >
        </TemplateImage>

        <div class="font-semibold">
            {{ info.name }}
        </div>

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
    </ButtonEl>
</template>

<script>

import interactsWithViewsItem from '@/vue-mixins/views/interactsWithViewsItem.js';

export default {
    name: 'TileItem',
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

.c-tile-item {
    height: 200px;
    width: 200px;

    @apply
        flex
        flex-col
        items-center
        px-6
        py-2
        rounded-xl
    ;
}

</style>
