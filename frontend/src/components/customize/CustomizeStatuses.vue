<template>
    <CustomizeFoundation class="o-customize-statuses">

        <template #header>
            <i
                v-if="symbol"
                class="far mr-2"
                :class="symbol"
            >
            </i>
            Statuses
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
                Add status group
            </button>
        </template>

        <GroupList
            v-if="groups && groups.length"
            ref="list"
            :groups="groups"
            groupType="STATUS"
            itemDisplayComponent="StatusDisplay"
            :hasUsesButton="true"
            :repository="repository"
        >
        </GroupList>

        <NoContentText
            v-else-if="!isLoading"
            class="mt-10"
            customHeaderPath="customizations.status.noContent.header"
            customMessagePath="customizations.status.noContent.description"
            :customIcon="symbol"
        >
        </NoContentText>

        <Modal
            v-if="isModalOpen"
            containerClass="p-4 w-600p"
            @closeModal="closeModal"
        >
            <GroupNew
                groupType="STATUS"
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
    name: 'CustomizeStatuses',
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
            query() {
                return TAG_GROUPS;
            },
            variables: {
                types: ['STATUS'],
            },
            update: initializeConnections,
        },
    },
    data() {
        return {
            groupType: 'STATUS',
        };
    },
    computed: {
        groups() {
            return this.tagGroupsConnection?.markerGroups;
        },
        supportPropsObj() {
            return {
                sectionName: 'Statuses',
                val: 'CUSTOMIZE_STATUSES',
                contentQuery: 'Statuses',
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

/*.o-customize-statuses {

} */

</style>
