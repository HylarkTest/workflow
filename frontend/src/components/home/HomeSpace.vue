<template>
    <div class="o-home-space">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center">
                <ExpandCollapseButton
                    class="mr-3"
                    :isExpanded="isExpandOpen"
                    @toggleExpandCollapse="toggleExpand"
                >
                </ExpandCollapseButton>

                <h3 class="font-semibold uppercase text-cm-400 text-lg">
                    {{ space.name }}
                </h3>
            </div>
        </div>

        <div
            v-show="isExpandOpen"
            class="flex flex-wrap -m-2"
        >
            <template
                v-if="hasPages"
            >
                <div
                    v-for="page in spacePages"
                    :key="page.id"
                    class="p-2 w-full sm:w-1/2 md:w-1/3"
                >
                    <HomeSquare
                        :page="page"
                        :space="space"
                        @openPageEdit="openDataModal"
                    >
                    </HomeSquare>
                </div>
            </template>
            <template
                v-else-if="hasExistingPages"
            >
                You are not displaying any pages for this space
            </template>
            <template
                v-else
            >
                <AddPages
                    class="w-full m-2 py-4 px-3"
                    textSize="sm"
                    buttonSize="sm"
                >
                </AddPages>
            </template>
        </div>

        <DataEditModal
            v-if="selectedData"
            :page="selectedData.page"
            :defaultView="selectedData.selectedView"
            @closeModal="closeDataModal"
        >
        </DataEditModal>
    </div>
</template>

<script>

import HomeSquare from './HomeSquare.vue';
import AddPages from '@/components/customize/AddPages.vue';
import DataEditModal from '@/components/customize/DataEditModal.vue';
import ExpandCollapseButton from '@/components/buttons/ExpandCollapseButton.vue';

export default {
    name: 'HomeSpace',
    components: {
        AddPages,
        DataEditModal,
        ExpandCollapseButton,
        HomeSquare,
    },
    mixins: [
    ],
    props: {
        space: {
            type: Object,
            required: true,
        },
        test: Boolean,
    },
    emits: [
    ],
    data() {
        return {
            selectedData: null,
            pageBeingDeleted: null,
            isExpandOpen: true,
        };
    },
    computed: {
        spacePages() {
            return this.space.pages;
        },
        pagesLength() {
            return this.spacePages.length;
        },
        hasPages() {
            return !!this.pagesLength;
        },
        existingPages() {
            return this.space.existingPages;
        },
        existingPagesLength() {
            return this.existingPages.length;
        },
        hasExistingPages() {
            return !!this.existingPagesLength;
        },
    },
    methods: {
        closeDataModal() {
            this.selectedData = null;
        },
        openDataModal(event) {
            this.selectedData = event;
        },
        toggleExpand() {
            this.isExpandOpen = !this.isExpandOpen;
        },
    },
    created() {
    },
};
</script>

<style scoped>
.o-home-space {
    &__angle {
        @apply
            leading-4
            text-base
        ;
    }
}
</style>
