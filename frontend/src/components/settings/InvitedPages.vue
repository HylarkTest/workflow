<template>
    <div class="o-invited-pages">
        <PagesMain
            v-model:filters="filters"
            sectionType="invited"
            :pages="pages"
            :possibleCreators="possibleCreators"
            :possibleUpdaters="possibleUpdaters"
            :parentName="inviterName"
            :parentImage="inviterImage"
            cannotCreate
        >
        </PagesMain>
    </div>
</template>

<script>

import filtersPages from '@/vue-mixins/settings/filtersPages.js';

import PagesMain from '@/components/settings/PagesMain.vue';

export default {

    name: 'InvitedPages',
    components: {
        PagesMain,
    },
    mixins: [
        filtersPages,
    ],
    props: {
        inviterId: {
            type: String,
            required: true,
        },
    },
    apollo: {
        // invited: {
        //     query: null,
        //     variables() {
        //         return {
        //             id: this.inviterId,
        //             ...this.pageFilters,
        //         };
        //     },
        // },
    },
    data() {
        return {
            invited: null, // to be replacted with apollo query
        };
    },
    computed: {
        pages() {
            return _.map(this.invited?.pages?.edges || [], 'node');
        },
        owner() {
            return this.invited?.owner;
        },
        users() {
            // TODO: Add other invited users
            // return this.invited?.owner ? [this.invited.owner] : [];
            return [];
        },
        possibleCreators() {
            return this.users;
        },
        possibleUpdaters() {
            return this.users;
        },
        inviterName() {
            return this.owner?.name;
        },
        inviterImage() {
            return this.owner?.logo || this.owner?.avatar;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<!-- <style scoped>
.o-invited-pages {

}
</style> -->
