<template>
    <div class="c-group-foundation">
        <div class="mb-8">
            <div class="flex justify-between">
                <h3 class="text-lg font-semibold">
                    {{ group.name }}
                </h3>

                <div class="flex">
                    <button
                        v-if="hasUsesButton"
                        class="button-primary--border button--sm"
                        type="button"
                        title="Assign use"
                        @click="openEditModal('USES')"
                    >
                        Assign use
                    </button>

                    <button
                        class="button-primary button--sm ml-2"
                        type="button"
                        :title="$t('common.edit')"
                        @click="openEditModal(null)"
                    >
                        <i
                            class="fal fa-pencil-alt"
                        >
                        </i>
                    </button>
                </div>
            </div>

            <div
                v-if="group.usedBy"
                class="mb-8"
            >
                <span class="text-sm uppercase text-cm-500 font-semibold">Used by</span>
            </div>
        </div>

        <GroupItemsMain
            ref="groupMain"
            :group="group"
            :groupType="groupType"
            :hideColor="hideColor"
            :itemDisplayComponent="itemDisplayComponent"
            :repository="repository"
        >
        </GroupItemsMain>

        <GroupEditModal
            v-if="isModalOpen"
            :group="group"
            :groupType="groupType"
            :defaultTab="selectedTab"
            :customTabs="customEditTabs"
            :itemDisplayComponent="itemDisplayComponent"
            :repository="repository"
            :hideColor="hideColor"
            :hideDescription="hideDescription"
            @closeModal="closeEditModal"
        >
        </GroupEditModal>
    </div>
</template>

<script>

import GroupItemsMain from './GroupItemsMain.vue';
import GroupEditModal from './GroupEditModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    name: 'GroupFoundation',
    components: {
        GroupItemsMain,
        GroupEditModal,
    },
    mixins: [
        interactsWithModal,
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
        hideColor: Boolean,
        hideDescription: Boolean,
        hasUsesButton: Boolean,
        itemDisplayComponent: {
            type: String,
            required: true,
        },
        customEditTabs: {
            type: [Array, null],
            default: null,
        },
        repository: {
            type: Object,
            required: true,
        },
    },
    emits: [
    ],
    data() {
        return {
            selectedTab: '',
        };
    },
    computed: {

    },
    methods: {
        openEditModal(tab) {
            if (tab) {
                this.selectedTab = tab;
            }
            this.openModal();
        },
        closeEditModal() {
            this.clearSelected();
            this.closeModal();
        },
        clearSelected() {
            this.selectedTab = '';
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-group-foundation {

} */

</style>
