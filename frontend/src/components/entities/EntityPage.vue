<template>
    <LayoutPage
        class="o-entity-page"
        :isLoading="$isLoading"
        :isMaxFullScreen="true"
        conditionalDirective="if"
    >
        <div class="mt-6">
            <RouterLink
                v-if="pageId"
                :to="backToListRoute"
                class="button--sm button-primary--medium flex w-fit u-hyphen"
                type="button"
            >
                {{ $t('navigation.backToPage', { pageName }) }}
            </RouterLink>
            <div class="bg-cm-00 rounded-xl min-h-0 flex-1 flex flex-col mt-5">
                <FullView
                    class="flex-1 min-h-0"
                    :useRouter="true"
                    :item="{ id: itemId }"
                    :page="page"
                    allowRouterTitle
                    @entityDeleted="actionOnDelete"
                >
                </FullView>
            </div>
        </div>
    </LayoutPage>
</template>

<script>

import LayoutPage from '@/components/layout/LayoutPage.vue';

import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';

import PAGE from '@/graphql/pages/queries/Page.gql';
import { pageQueryHandler } from '@/http/exceptionHandler.js';

export default {
    name: 'EntityPage',
    components: {
        LayoutPage,
    },
    mixins: [
        interactsWithApolloQueries,
    ],
    props: {
    },
    apollo: {
        page: {
            query: PAGE,
            variables() {
                return { id: this.pageId };
            },
            skip() {
                return !this.pageId;
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
        itemId() {
            return this.$route.params.itemId;
        },
        pageId() {
            return this.$route.params.pageId;
        },
        pageName() {
            return this.page?.name;
        },
        backToListRoute() {
            return {
                name: 'page',
                params: { pageId: this.pageId },
            };
        },
    },
    methods: {
        actionOnDelete() {
            const destination = this.pageId ? this.backToListRoute : { name: 'home' };
            this.$router.push(destination);
        },
    },
    created() {

    },
};
</script>

<style scoped>
.o-entity-page {
    /*@apply
        px-6
        py-4
    ;*/
}
</style>
