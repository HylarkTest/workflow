<template>
    <div class="o-teams-pages">
        <PagesMain
            sectionType="teams"
            :pages="pages"
            :possibleCreators="possibleCreators"
            :possibleUpdaters="possibleUpdaters"
            :parentName="teamMappings && teamMappings.name"
            :parentImage="teamMappings && teamMappings.logo"
        >
        </PagesMain>
    </div>
</template>

<script>

import TEAM_MAPPINGS from '@/graphql/teams/queries/TeamMappings.gql';
import TEAM_MEMBERS from '@/graphql/teams/queries/TeamMembers.gql';
import PagesMain from '@/components/settings/PagesMain.vue';

export default {

    name: 'TeamsPages',
    components: {
        PagesMain,
    },
    props: {
        teamId: {
            type: String,
            required: true,
        },
    },
    apollo: {
        teamMappings: {
            query: TEAM_MAPPINGS,
            variables() {
                return {
                    id: this.teamId,
                };
            },
        },
        teamMembers: {
            query: TEAM_MEMBERS,
            variables() {
                return {
                    id: this.teamId,
                };
            },
        },
    },
    data() {
        return {

        };
    },
    computed: {
        pages() {
            return _.map(this.teamMappings?.pages?.edges, 'node');
        },
        members() {
            return _.map(this.teamMembers?.members?.edges, 'node');
        },
        possibleCreators() {
            // TODO: Add other invited creators
            return this.members;
        },
        possibleUpdaters() {
            // TODO: Add other invited updaters
            return this.members;
        },
    },
    methods: {

    },
    watch: {
        '$route.name': function onNameChange(name, oldName) {
            if (name === 'settings.teams.pages'
                || (name.startsWith('settings.teams.pages.edit') && oldName === 'settings.teams.pages.create')) {
                this.$apollo.queries.teamMappings.refetch();
            }
        },
    },
    created() {
    },
};
</script>

<!-- <style scoped>
.o-teams-pages {

}
</style> -->
