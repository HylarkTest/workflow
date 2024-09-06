<template>
    <CustomizeFoundation class="o-customize-pipelines">

        <template #header>
            <i
                v-if="symbol"
                class="far mr-2"
                :class="symbol"
            >
            </i>
            Pipelines
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
                Add pipeline group
            </button>
        </template>

        <GroupList
            v-if="groups && groups.length"
            ref="list"
            :groups="groups"
            :groupType="groupType"
            itemDisplayComponent="StageDisplay"
            :hasUsesButton="true"
            :repository="repository"
        >
        </GroupList>

        <NoContentText
            v-else-if="!isLoading"
            class="mt-10"
            customHeaderPath="customizations.pipeline.noContent.header"
            customMessagePath="customizations.pipeline.noContent.description"
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

import interactsWithGroupCustomizations from '@/vue-mixins/customizations/interactsWithGroupCustomizations.js';
import interactsWithSupportWidget from '@/vue-mixins/support/interactsWithSupportWidget.js';

import TAG_GROUPS from '@/graphql/markers/queries/MarkerGroups.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import { createMarkerGroup, groupRepository } from '@/core/repositories/markerRepository.js';

export default {
    name: 'CustomizePipelines',
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
                types: ['PIPELINE'],
            },
            update: initializeConnections,
        },
    },
    data() {
        return {
            groupType: 'PIPELINE',
        };
    },
    computed: {
        groups() {
            return this.tagGroupsConnection?.markerGroups;
        },
        supportPropsObj() {
            return {
                sectionName: 'Pipelines',
                val: 'CUSTOMIZE_PIPELINES',
                contentQuery: 'Pipelines',
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

/*.o-customize-pipelines {

} */

</style>
