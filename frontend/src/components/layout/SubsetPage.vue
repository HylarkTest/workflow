<template>
    <div
        v-if="!$apollo.loading"
        class="c-subset-page"
    >
        <component
            :is="pageTypeComponent"
            :page="page"
            :isSubsetPage="true"
            :subsetHeaderProps="subsetHeaderProps"
        >
        </component>
    </div>
</template>

<script>

import PAGE from '@/graphql/pages/queries/Page.gql';

import NotesPage from '@/components/notes/NotesPage.vue';
import TodosPage from '@/components/todos/TodosPage.vue';
import DocumentsPage from '@/components/documents/DocumentsPage.vue';
import PinboardPage from '@/components/pinboard/PinboardPage.vue';
import LinksPage from '@/components/links/LinksPage.vue';
import TimekeeperPage from '@/components/timekeeper/TimekeeperPage.vue';
import CalendarPage from '@/components/events/CalendarPage.vue';
import LayoutHeader from '@/components/layout/LayoutHeader.vue';
import { pageQueryHandler } from '@/http/exceptionHandler.js';

export default {
    name: 'SubsetPage',
    components: {
        NotesPage,
        TodosPage,
        CalendarPage,
        DocumentsPage,
        PinboardPage,
        LinksPage,
        TimekeeperPage,
        LayoutHeader,
    },
    mixins: [
    ],
    props: {
        pageId: {
            type: String,
            required: true,
        },
    },
    apollo: {
        page: {
            query: PAGE,
            variables() {
                return { id: this.pageId };
            },
            fetchPolicy: 'cache-first',
            error: pageQueryHandler,
        },
    },
    data() {
        return {
        };
    },
    computed: {
        pageType() {
            return this.page?.type;
        },
        pageTypeComponent() {
            const type = this.pageType;
            if (type === 'EVENTS') {
                return 'CalendarPage';
            }
            return `${_.pascalCase(this.pageType)}Page`;
        },
        subsetHeaderProps() {
            return {
                page: this.page,
            };
        },
    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>
/*.c-subset-page {
}*/
</style>
