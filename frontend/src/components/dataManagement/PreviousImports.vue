<template>
    <div class="o-previous-imports">

        <LoaderFetch
            v-if="isLoading"
            class="my-10"
            :isFull="true"
            :sphereSize="50"
        >
        </LoaderFetch>

        <NoContentText
            v-if="showNoContent"
            class="mt-8"
            customIcon="fa-file-import"
            customHeaderPath="imports.history.none.header"
            customMessagePath="imports.history.none.message"
        >
        </NoContentText>

        <div
            v-if="importHistoryLength && !isLoading"
        >
            <LoadMore
                :hasNext="hasNextPage"
                @nextPage="loadMore"
            >
                <PreviousImport
                    v-for="dataImport in imports"
                    :key="dataImport.id"
                    :dataImport="dataImport"
                    class="o-previous-imports__import"
                >
                </PreviousImport>
            </LoadMore>
        </div>
    </div>
</template>

<script>

import PreviousImport from './PreviousImport.vue';
import LoadMore from '@/components/data/LoadMore.vue';

import IMPORTS from '@/graphql/imports/Imports.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';

export default {
    name: 'PreviousImports',
    components: {
        PreviousImport,
        LoadMore,
    },
    mixins: [
        interactsWithApolloQueries,
    ],
    props: {

    },
    apollo: {
        imports: {
            query: IMPORTS,
            update: (imports) => initializeConnections(imports).imports,
            pollInterval: 10_000,
        },
    },
    data() {
        return {
        };
    },
    computed: {
        showNoContent() {
            return !this.importHistoryLength && !this.isLoading;
        },
        importPageInfo() {
            return this.imports?.__ImportConnection.pageInfo;
        },
        hasNextPage() {
            return this.importPageInfo?.hasNextPage;
        },
        importHistoryLength() {
            return this.imports?.length;
        },
        isLoading() {
            return this.$isLoading;
        },
    },
    methods: {
        loadMore() {
            this.$apollo.queries.imports.fetchMore({
                variables: {
                    after: this.importPageInfo?.endCursor,
                },
            });
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-previous-imports {
    &__import {
        @apply
            border-b-2
            border-secondary-400
            border-solid
            last:border-none
            py-4
        ;
    }
}

</style>
