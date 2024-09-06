<template>
    <div class="o-page-edit-lists">
        <div
            v-for="list in lists"
            :key="list.id"
            class="mb-1"
        >
            <CheckHolder
                v-model="listsForm.lists"
                :val="list.id"
            >
                {{ list.name }}
            </CheckHolder>
        </div>
    </div>
</template>

<script>

import interactsWithFeatureListLoading from '@/vue-mixins/features/interactsWithFeatureListLoading.js';

import { setListsOnPage } from '@/core/repositories/pageRepository.js';

import initializeConnections from '@/http/apollo/initializeConnections.js';

export default {
    name: 'PageEditLists',
    components: {

    },
    mixins: [
        interactsWithFeatureListLoading,
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
    },
    apollo: {
        lists: {
            query() {
                return this.getListQuery(this.page.type);
            },
            update(data) {
                return _.getFirstKey(initializeConnections(data));
            },
            variables() {
                return { spaceIds: this.page.space.id ? [this.page.space.id] : null };
            },
        },
    },
    data() {
        return {
            listsForm: this.$apolloForm(() => {
                return {
                    page: this.page,
                    lists: this.page.lists || [],
                };
            }),
        };
    },
    computed: {
        selectedLists() {
            return this.listsForm.lists;
        },
    },
    methods: {
        async saveLists() {
            await setListsOnPage(this.listsForm.page, this.listsForm.lists);
            this.$debouncedSaveFeedback();
        },
    },
    watch: {
        'listsForm.lists': {
            handler() {
                this.saveLists();
            },
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-page-edit-lists {

} */

</style>
