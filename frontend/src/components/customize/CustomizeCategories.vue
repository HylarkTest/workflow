<template>
    <CustomizeFoundation class="o-customize-categories">
        <template #header>
            Categories
        </template>

        <template #besideHeader>
            <button
                class="button-primary button--sm"
                type="button"
                @click="openModal"
            >
                <i
                    class="fal fa-plus-circle mr-1"
                >
                </i>
                Add category group
            </button>
        </template>

        <GroupList
            v-if="categories && categories.length"
            ref="list"
            :groups="categories"
            groupType="CATEGORY"
            :hideDescription="true"
            :hideColor="true"
            :customEditTabs="['GENERAL', 'ITEMS']"
            :repository="repository"
            itemDisplayComponent="CategoryDisplay"
        >
        </GroupList>

        <NoContentText
            v-else-if="!isLoading"
            customIcon="fa-list-dropdown"
            customHeaderPath="customizations.category.none.header"
            customMessagePath="customizations.category.none.message"
        >
        </NoContentText>

        <Modal
            v-if="isModalOpen"
            containerClass="p-4 w-600p"
            @closeModal="closeModal"
        >
            <GroupNew
                groupType="CATEGORY"
                :hideDescription="true"
                @saveNewGroup="saveNewGroup"
            >
            </GroupNew>
        </Modal>

    </CustomizeFoundation>
</template>

<script>

import interactsWithGroupCustomizations from '@/vue-mixins/customizations/interactsWithGroupCustomizations.js';
import interactsWithSupportWidget from '@/vue-mixins/support/interactsWithSupportWidget.js';

import { createCategory, groupRepository } from '@/core/repositories/categoryRepository.js';
import CATEGORIES from '@/graphql/categories/queries/Categories.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

export default {
    name: 'CustomizeCategories',
    components: {
    },
    mixins: [
        interactsWithGroupCustomizations,
        interactsWithSupportWidget,
    ],
    props: {
    },
    apollo: {
        categoriesConnection: {
            query: CATEGORIES,
            variables: {
                type: 'TAG',
            },
            update: initializeConnections,
        },
    },
    data() {

    },
    computed: {
        isLoading() {
            return this.$apollo.loading;
        },
        categories() {
            return this.categoriesConnection?.categories || [];
        },
        supportPropsObj() {
            return {
                sectionName: 'Categories',
                val: 'CUSTOMIZE_CATEGORIES',
                contentQuery: 'Categories',
            };
        },
        groups() {
            return this.categories;
        },
    },
    methods: {
        async saveNewGroup(form) {
            await createCategory(form);
            this.closeModal();
        },
        openNewGroup() {
            this.$refs.list.openModal();
        },
    },
    created() {
        this.repository = groupRepository;
    },
};
</script>

<style scoped>

/*.o-customize-categories {

} */

</style>
