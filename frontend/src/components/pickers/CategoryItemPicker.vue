<template>
    <component
        v-if="!$apollo.loading"
        :is="dropdownComponent"
        v-model:searchQuery="searchQuery"
        class="c-category-item-picker w-full"
        :popupProps="{ maxHeightProp: '7.5rem' }"
        displayRule="name"
        :options="categoryItems"
        placeholder="Add a value"
        isSearchable
        v-bind="$attrs"
    >
        <template
            #additional
        >
            <SettingsButton
                @showSettings="openModal"
            >
            </SettingsButton>

            <GroupEditModal
                v-if="isModalOpen"
                :group="category"
                :groupType="'CATEGORY'"
                :customTabs="['GENERAL', 'ITEMS']"
                itemDisplayComponent="CategoryDisplay"
                :repository="repository"
                :hideColor="true"
                :hideDescription="true"
                @closeModal="closeModal"
            >
            </GroupEditModal>
        </template>
    </component>
</template>

<script>

import SettingsButton from '@/components/buttons/SettingsButton.vue';
import GroupEditModal from '@/components/customize/GroupEditModal.vue';

import CATEGORY from '@/graphql/categories/queries/Category.gql';

import { groupRepository } from '@/core/repositories/categoryRepository.js';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    name: 'CategoryItemPicker',
    components: {
        GroupEditModal,
        SettingsButton,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        categoryId: {
            type: [Object, String],
            required: true,
        },
        dropdownComponent: {
            type: String,
            default: 'DropdownBox',
        },
    },
    apollo: {
        category: {
            query: CATEGORY,
            variables() {
                return {
                    id: this.categoryId,
                };
            },
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
            searchQuery: '',
            repository: groupRepository,
        };
    },
    computed: {
        categoryItems() {
            return this.category.items;
        },
    },
    methods: {

    },
    created() {
    },
};
</script>

<style scoped>

/* .c-category-item-picker {

} */

</style>
