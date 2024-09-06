<template>
    <div class="o-emails-listing min-w-0">
        <div
            class="sticky z-cover bg-cm-00 rounded-t-xl"
            :class="topHeaderClass"
        >
            <EmailHeader
                ref="header"
                :list="displayedList"
                :filtersObj="filtersObj"
                :isSideMinimized="isSideMinimized"
                :suggestedEmailAddresses="suggestedEmailAddresses"
                :emailAddressesForAssociation="emailAddressesForAssociation"
                :lastUsedIntegration="lastUsedIntegration"
                @minimizeSide="$emit('minimizeSide', $event)"
            >
                <template #headerButtonOption>
                    <slot name="headerButtonOption">

                    </slot>
                </template>
            </EmailHeader>
        </div>

        <EmailsMain
            v-if="!noEmails && nothingLoading"
            :emails="groups"
            :node="node"
            :filtersObj="filtersObj"
            :displayedList="displayedList"
            :suggestedEmailAddresses="suggestedEmailAddresses"
            :lastUsedIntegration="lastUsedIntegration"
            @loadMoreEmails="loadMore"
        >
        </EmailsMain>

        <LoaderFetch
            v-if="!nothingLoading"
            class="py-10"
            :isFull="true"
            :sphereSize="40"
        >
        </LoaderFetch>

        <NoContentText
            v-if="noEmails"
            class="my-8"
            :customHeaderPath="customHeaderPath"
            customIcon="fa-envelope-dot"
        >
            <template
                v-if="hasRequestFilter"
                #graphic
            >
                <BirdImage
                    class="h-20"
                    whichBird="MagnifyingGlassBird_72dpi.png"
                >
                </BirdImage>
            </template>
        </NoContentText>
    </div>
</template>

<script>

import EmailsMain from './EmailsMain.vue';
import EmailHeader from '@/components/emails/EmailHeader.vue';

import EMAILS from '@/graphql/mail/queries/Emails.gql';
import GROUPED_EMAILS from '@/graphql/mail/queries/GroupedEmails.gql';

import { initializeEmails } from '@/core/repositories/emailRepository.js';

import Mailbox from '@/core/models/Mailbox.js';
import interactsWithApolloQueries from '@/vue-mixins/interactsWithApolloQueries.js';
import handlesListAndGroupedItems from '@/vue-mixins/features/handlesListAndGroupedItems.js';

export default {
    name: 'EmailsListing',
    components: {
        EmailsMain,
        EmailHeader,
    },
    mixins: [
        interactsWithApolloQueries,
        handlesListAndGroupedItems,
    ],
    props: {
        displayedList: {
            type: [Mailbox, null],
            required: true,
        },
        filtersObj: {
            type: Object,
            required: true,
        },
        isSideMinimized: Boolean,
        lastUsedIntegration: {
            type: [String, null],
            default: '',
        },
        suggestedEmailAddresses: {
            type: [Array, null],
            default: null,
        },
        emailAddressesForAssociation: {
            type: [Array, null],
            default: null,
        },
        topHeaderClass: {
            type: String,
            default: 'nav-spacing--sticky',
        },
        node: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'minimizeSide',
    ],
    apollo: {
        emails: {
            query: EMAILS,
            skip() {
                const sourceId = this.filtersObj.source
                    ? this.filtersObj.source
                    : this.displayedList?.account.id;
                return !sourceId;
            },
            variables() {
                const sourceId = this.filtersObj.source
                    ? this.filtersObj.source
                    : this.displayedList?.account.id;
                const variables = {
                    mailboxId: this.displayedList?.id,
                    sourceId,
                };
                if (this.filtersObj.freeText) {
                    variables.search = this.filtersObj.freeText;
                }
                if (this.node) {
                    variables.forNode = this.node.id;
                }
                return variables;
            },
            fetchPolicy: 'network-only',
            // debounce: 300,
            update: initializeEmails,
            pollInterval: 300_000, // Fetch emails again every 5 minutes
        },
        groupedEmails: {
            query: GROUPED_EMAILS,
            skip() {
                const sourceId = this.filtersObj.source
                    ? this.filtersObj.source
                    : this.displayedList?.account.id;
                return !!sourceId;
            },
            variables() {
                const variables = {};
                if (this.filtersObj.freeText) {
                    variables.search = this.filtersObj.freeText;
                }
                if (this.node) {
                    variables.forNode = this.node.id;
                }
                return variables;
            },
            fetchPolicy: 'network-only',
            // debounce: 300,
            update: initializeEmails,
            pollInterval: 300_000, // Fetch emails again every 5 minutes
        },
    },
    data() {
        return {
            headerHeight: null,
        };
    },
    computed: {
        groups() {
            return this.getGroupings(this.emails, this.groupedEmails);
        },
        doGroupsHaveEmails() {
            return this.groups?.some((group) => group.items.length);
        },
        groupPointer() {
            const sourceId = this.filtersObj.source
                ? this.filtersObj.source
                : this.displayedList?.account.id;
            return sourceId ? null : this.groupedEmails?.groups.length;
        },

        // About loading

        // When you first load the page it is loading the emails query for the first time
        // and it skips the groupedEmails query.
        // So the $isLoadingQueriesFirstTime is true meaning it skips checking if
        // queries are loading from change.

        // When the page finishes loading emails the $isLoadingQueriesFirstTime method returns
        // false so it then checks $isLoadingQueriesFromChange which would also return false
        // as nothing is loading.

        // When you type in the search box it now skips the emails query and loads
        // groupedEmails for the first time. So again $isLoadingQueriesFirstTime returns
        // true and so it doesn’t need to check $isLoadingQueriesFromChange.
        // And then, same as before when groupedEmails finishes loading
        // $isLoadingQueriesFirstTime is returns false and so it checks
        // $isLoadingQueriesFromChange which also returns false

        nothingLoading() {
            return !this.isLoadingEmails && !this.isLoadingGroups;
        },
        isLoadingEmails() {
            return this.$isLoadingQueriesFirstTime(['emails'])
                || this.$isLoadingQueriesFromChange(['emails']);
        },
        isLoadingGroups() {
            return this.$isLoadingQueriesFirstTime(['groupedEmails'])
                || this.$isLoadingQueriesFromChange(['groupedEmails']);
        },
        loadingEmails() {
            return this.$isLoadingQueriesFirstTime(['emails', 'groupedEmails']);
        },
        noEmails() {
            return !this.loadingEmails
                && (this.groupPointer
                    ? !this.doGroupsHaveEmails
                    : !this.emails?.length);
        },
        hasFilters() {
            return !!this.filtersObj.freeText;
        },
        hasRequestFilter() {
            return this.hasFilters && !!this.displayedList?.total;
        },
        customHeaderPath() {
            if (this.hasRequestFilter) {
                return 'common.noFilterMatches';
            }
            if (this.node) {
                if (this.filtersObj.filter === 'all') {
                    return ['emails.noContent.empty.headerNodeAll', {
                        nodeName: this.node.name,
                    }];
                }
                return ['emails.noContent.empty.headerNode', {
                    mailboxName: this.displayedList?.name,
                    nodeName: this.node.name,
                }];
            }
            return ['emails.noContent.empty.header', { mailboxName: this.displayedList?.name }];
        },
    },
    methods: {
        loadMore({ grouping }) {
            const pageInfo = this.getConnection(grouping.items).pageInfo;
            const variables = {
                after: pageInfo.endCursor,
            };
            if (grouping.header?.group?.id) {
                variables.includeGroups = [grouping.header.group.id];
                return this.$apollo.queries.groupedEmails.fetchMore({ variables });
            }
            return this.$apollo.queries.emails.fetchMore({ variables });
        },
    },
    created() {
    },
    mounted() {
    },
};
</script>

<style scoped>

.o-emails-listing {
    @apply
        flex
        flex-col
    ;
}

</style>
