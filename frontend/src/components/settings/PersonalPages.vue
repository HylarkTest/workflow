<template>
    <div class="o-personal-pages">
        <PagesMain
            sectionType="personal"
            :pages="pages"
            :possibleCreators="possibleCreators"
            :possibleUpdaters="possibleUpdaters"
            :parentName="parentName"
            :parentImage="parentImage"
        >
        </PagesMain>
    </div>
</template>

<script>

import SPACE from '@/graphql/spaces/queries/Space.gql';
import USER from '@/graphql/Me.gql';
import PagesMain from '@/components/settings/PagesMain.vue';

export default {

    name: 'PersonalPages',
    components: {
        PagesMain,
    },
    props: {
        spaceId: {
            type: String,
            required: true,
        },
    },
    apollo: {
        space: {
            query: SPACE,
            variables() {
                return {
                    id: this.spaceId,
                };
            },
        },
        user: USER,
    },
    data() {
        return {};
    },
    computed: {
        possibleCreators() {
            // TODO: Add other invited creators
            return this.user ? [this.user] : [];
        },
        possibleUpdaters() {
            // TODO: Add other invited updaters
            return this.user ? [this.user] : [];
        },
        pages() {
            return _.map(this.space?.pages?.edges, 'node');
        },
        parentImage() {
            return this.loadingSpace ? '' : (this.space?.logo || this.user?.avatar);
        },
        parentName() {
            return this.space?.name;
        },
        loadingSpace() {
            return this.$apollo.queries.space.loading;
        },
    },
    methods: {

    },
    watch: {
        '$route.name': function onNameChange(name, oldName) {
            if (name === 'settings.personal.pages'
                || (name === 'settings.personal.pages.edit' && oldName === 'settings.personal.pages.create')) {
                this.$apollo.queries.space.refetch();
            }
        },
    },
};
</script>

<!-- <style scoped>
.o-personal-pages {

}
</style> -->
