<template>
    <CustomizeFoundation
        class="o-customize-tags"
    >
        <template #header>
            <i
                v-if="symbol"
                class="far mr-2"
                :class="symbol"
            >
            </i>
            Tags
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
                Add tag group
            </button>
        </template>

        <GroupList
            v-if="groups && groups.length"
            ref="list"
            :groups="groups"
            :groupType="groupType"
            itemDisplayComponent="TagDisplay"
            :hasUsesButton="true"
            :repository="repository"
        >
        </GroupList>

        <NoContentText
            v-else-if="!isLoading"
            class="mt-10"
            customHeaderPath="customizations.tag.noContent.header"
            customMessagePath="customizations.tag.noContent.description"
            :customIcon="symbol"
        >
        </NoContentText>

        <Modal
            v-if="isModalOpen"
            containerClass="p-4 w-600p"
            @closeModal="closeModal"
        >
            <GroupNew
                :groupType="groupType"
                @saveNewGroup="saveNewGroup"
            >
            </GroupNew>
        </Modal>
    </CustomizeFoundation>
</template>

<script>

import TAG_GROUPS from '@/graphql/markers/queries/MarkerGroups.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

import interactsWithGroupCustomizations from '@/vue-mixins/customizations/interactsWithGroupCustomizations.js';
import interactsWithSupportWidget from '@/vue-mixins/support/interactsWithSupportWidget.js';

import { createMarkerGroup, groupRepository } from '@/core/repositories/markerRepository.js';

export default {
    name: 'CustomizeTags',
    components: {
    },
    mixins: [
        interactsWithGroupCustomizations,
        interactsWithSupportWidget,
    ],
    props: {

    },
    apollo: {
        tagGroupsConnection: {
            query: TAG_GROUPS,
            variables: {
                types: ['TAG'],
            },
            update: initializeConnections,
        },
    },
    data() {
        return {
            groupType: 'TAG',
        };
    },
    computed: {
        // isLoading() {
        //     return this.$apollo.loading;
        // },
        groups() {
            return this.tagGroupsConnection?.markerGroups;
        },
        supportPropsObj() {
            return {
                sectionName: 'Tags',
                val: 'CUSTOMIZE_TAGS',
                contentQuery: 'Tags',
                relevantTopics: ['markers'],
            };
        },
    },
    methods: {
        async saveNewGroup(form) {
            this.closeModal();
            await createMarkerGroup(form);
        },
    },
    created() {
        this.repository = groupRepository;
    },
};
</script>

<style scoped>

/*.o-customize-tags {

} */

</style>
