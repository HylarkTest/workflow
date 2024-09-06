<template>
    <div class="o-space-page-list">
        <div
            v-if="folder.folder"
            class="flex items-center text-cm-500 text-sm mb-2"
        >
            <i
                class="fal fa-folder mr-2"
            >
            </i>

            <h4 class="uppercase font-semibold">
                {{ getFolderName(folder.folder) }}
            </h4>
        </div>

        <div class="-m-2 flex flex-wrap items-stretch">
            <div
                v-for="page in folder.pages"
                :key="page.id"
                class="p-2 w-full md:w-1/2 lg:w-1/3 xl:1/4"
            >
                <PageCard
                    cardClass="h-full"
                    :page="page"
                    :isBeingDeleted="isPageBeingDeleted(page)"
                    @openPageEdit="$emit('openPageEdit', $event)"
                >
                </PageCard>
            </div>
        </div>
    </div>
</template>

<script>

import PageCard from './PageCard.vue';

import providesSpaceFolderHelpers from '@/vue-mixins/providesSpaceFolderHelpers.js';

export default {
    name: 'SpacePageList',
    components: {
        PageCard,
    },
    mixins: [
        providesSpaceFolderHelpers,
    ],
    props: {
        folder: {
            type: Object,
            required: true,
        },
        pageBeingDeleted: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'openPageEdit',
    ],
    data() {
        return {

        };
    },
    computed: {
    },
    methods: {
        isPageBeingDeleted(page) {
            return page.id === this.pageBeingDeleted?.id;
        },
    },
    created() {
    },
};
</script>

<style scoped>
/* .o-space-page-list {

} */
</style>
