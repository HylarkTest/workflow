<template>
    <div
        class="o-emails-main flex flex-1 min-h-0"
        :class="{ 'p-4': hasGroupings }"
    >
        <GroupingBase
            v-show="!onEmailThread"
            ref="side"
            class="o-emails-main__side"
            :class="sideClasses"
            :groupingType="groupingType"
            :groupings="emails"
            viewType="EMAILS"
            :hideCount="true"
        >
            <template
                #listSlot="{ grouping, isOpen }"
            >
                <LoadMore
                    :ref="(el) => loadMores.push(el)"
                    :hasNext="groupingHasMore(grouping)"
                    @nextPage="isOpen && loadMore(grouping)"
                >
                    <div
                        v-for="item in grouping.items"
                        :key="item.id"
                        class="mt-2 first:mt-0"
                    >
                        <EmailItem
                            :item="item"
                            :isInMailbox="!!displayedList"
                            :selectedThreadId="selectedThreadId"
                            @click="selectThread(item)"
                        >
                        </EmailItem>
                    </div>
                </LoadMore>
            </template>
        </GroupingBase>

        <div
            v-if="threadAvailable"
            class="flex-1 min-w-0"
        >
            <LoaderFetch
                v-if="isLoadingEmail"
                class="py-10 flex justify-center"
                :sphereSize="40"
                bgColorClass="bg-secondary-200"
            >
            </LoaderFetch>

            <EmailThread
                v-else-if="canSeeThread || onEmailThread"
                :email="selectedEmail"
                :hasBack="onEmailThread"
                :emailBoxStyle="emailBoxStyle"
                @respondToEmail="respondToEmail"
                @resetEmailInView="resetEmailInView"
                @returnToList="returnToList"
            >
            </EmailThread>
        </div>

        <SideDialog
            :sideOpen="isEmailOpen"
            @closeSide="closeEmail"
        >
            <EmailWrite
                :mailbox="displayedList"
                :email="emailWriteObj.email"
                :action="emailWriteObj.action"
                :node="node"
                :suggestedEmailAddresses="suggestedEmailAddresses"
                :lastUsedIntegration="lastUsedIntegration"
                @closeEmail="closeEmail"
            >
            </EmailWrite>
        </SideDialog>
    </div>
</template>

<script>

import EmailItem from './EmailItem.vue';
import EmailThread from './EmailThread.vue';
import EmailWrite from './EmailWrite.vue';
import LoadMore from '@/components/data/LoadMore.vue';
import SideDialog from '@/components/dialogs/SideDialog.vue';
import GroupingBase from '@/components/views/GroupingBase.vue';

import listensToScrollandResizeEvents from '@/vue-mixins/listensToScrollAndResizeEvents.js';

import EMAIL from '@/graphql/mail/queries/Email.gql';
import EMAILS from '@/graphql/mail/queries/Emails.gql';
import { createEmailFromObject } from '@/core/repositories/emailRepository.js';

import Mailbox from '@/core/models/Mailbox.js';
import { checkAndHandleMissingError } from '@/http/exceptionHandler.js';
import handlesListAndGroupedItems from '@/vue-mixins/features/handlesListAndGroupedItems.js';

export default {
    name: 'EmailsMain',
    components: {
        LoadMore,
        EmailItem,
        EmailThread,
        EmailWrite,
        SideDialog,
        GroupingBase,
    },
    mixins: [
        listensToScrollandResizeEvents,
        handlesListAndGroupedItems,
    ],
    props: {
        emails: {
            type: Array,
            required: true,
        },
        displayedList: {
            type: [Mailbox, null],
            required: true,
        },
        mainBgColor: {
            type: String,
            default: 'primary',
        },
        emailBoxStyle: {
            type: String,
            default: 'plain',
            validator(value) {
                return ['plain', 'border'].includes(value);
            },
        },
        lastUsedIntegration: {
            type: [String, null],
            default: '',
        },
        suggestedEmailAddresses: {
            type: [Array, null],
            default: null,
        },
        forceResponsive: Boolean,
        filtersObj: {
            type: [Object, null],
            default: null,
        },
        node: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'loadMoreEmails',
        'respondToEmail',
    ],
    apollo: {
        selectedEmail: {
            query: EMAIL,
            variables() {
                if (!this.selectedThreadId) {
                    /*
                     * TODO: Fix this hack, possibly when moving to the officially supported Vue Apollo
                     * Because of the way the watchers are set up, this method is run before the
                     * skip method can be checked.
                     * When the displayedList prop changes it triggers the watcher for this method
                     * then the watcher which sets the `selectedThreadId` data to null, which
                     * then triggers the `skip` method.
                     * To get around this, we implement a pseudo skip here by returning the previous
                     * variables when the `selectedThreadId` is null, this will tell Apollo to not
                     * make a new request until the `selectedThreadId` is null.
                     * This could probably be fixed in the `SmartQuery` class, but it isn't clear
                     * how to do that, and requires more thought.
                     */
                    return JSON.parse(this.$apollo.queries.selectedEmail.previousVariablesJson);
                }
                return {
                    mailboxId: this.displayedList?.id
                        || this.selectedEmailItem.mailbox.id,
                    sourceId: this.displayedList?.account.id
                        || this.selectedEmailItem.account.id,
                    emailId: this.selectedThreadId,
                };
            },
            skip() {
                return !this.selectedThreadId;
            },
            update: ({ email }) => createEmailFromObject(email),
            error(error) {
                checkAndHandleMissingError(error, false);
                const client = this.$apollo.getClient();
                client.refetchQueries({ include: [EMAILS] });
                return false;
            },
            fetchPolicy: 'network-only',
        },
    },
    data() {
        return {
            selectedThreadId: null,
            responsiveDisplay: true,
            showThreadResp: false,
            isEmailOpen: false,
            emailWriteObj: null,
            loadMores: [],
        };
    },
    computed: {
        groupingsLength() {
            return this.emails.length;
        },
        hasGroupings() {
            return this.groupingType;
        },
        inResponsiveMode() {
            return this.forceResponsive || this.hasFilters;
        },
        hasFilters() {
            return this.filtersObj.filter === 'all' || !!(this.filtersObj.freeText);
        },
        groupingType() {
            if (!this.emails?.[0].header.val) {
                return null;
            }
            return 'EXTENSION';
        },
        isLoadingEmail() {
            return this.$apollo.queries.selectedEmail.loading;
        },
        firstEmailsList() {
            return this.emails[0].items;
        },
        hasEmails() {
            return this.firstEmailsList?.length;
        },
        threadAvailable() {
            // Does the part with the actual email thread show
            return this.showThreadResp // When in Responsive mode
                || !this.responsiveDisplay; // Outside of responsive mode
        },
        canSeeThread() {
            return this.selectedEmail
                && this.selectedThreadId
                && this.showThreadResp;
        },
        onEmailThread() {
            return this.selectedThreadId
                && this.showThreadResp
                && this.responsiveDisplay;
        },
        sideClasses() {
            return { 'o-emails-main__side--resp': this.responsiveDisplay };
        },
        selectedEmailItem() {
            return _.find(this.firstEmailsList, { id: this.selectedThreadId });
        },
    },
    methods: {
        returnToList() {
            this.selectedThreadId = null;
        },
        loadMore(grouping) {
            this.$emit('loadMoreEmails', { grouping });
        },
        groupingHasMore(grouping) {
            return this.getConnection(grouping.items)?.pageInfo?.hasNextPage;
        },
        resetEmailInView() {
            const index = _.findIndex(this.firstEmailsList, { id: this.selectedThreadId });
            const nextEmailIndex = index + 1;
            const nextEmail = this.firstEmailsList[nextEmailIndex];
            if (nextEmail && !this.responsiveDisplay) {
                this.selectedThreadId = nextEmail.id;
                this.$refs.side.scrollTo(0, 0);
            } else {
                this.selectedThreadId = null;
            }
        },
        selectThread(thread) {
            this.selectedThreadId = thread.id;
            if (this.responsiveDisplay) {
                this.showThreadResp = true;
            }
        },
        onResize() {
            if (!this.inResponsiveMode) {
                const el = this.$el;
                const elWidth = el.offsetWidth;
                if (elWidth < 700) {
                    this.responsiveDisplay = true;
                    this.showThreadResp = false;
                } else {
                    this.responsiveDisplay = false;
                    this.showThreadResp = true;
                }
            }
        },
        closeEmail() {
            this.isEmailOpen = false;
            this.emailWriteObj = null;
        },
        openEmail(emailObj) {
            this.emailWriteObj = emailObj;
            this.isEmailOpen = true;
        },
        composeEmail() {
            this.openEmail({ action: 'COMPOSE' });
        },
        respondToEmail(emailObj) {
            this.openEmail(emailObj);
        },
    },
    watch: {
        emails: {
            immediate: true,
            handler(emails) {
                if (!this.selectedThreadId
                        && emails.length
                        && !this.responsiveDisplay) {
                    this.selectedThreadId = this.firstEmailsList[0]?.id;
                }
            },
        },
        async responsiveDisplay(val) {
            if (!val) {
                this.selectedThreadId = this.firstEmailsList[0]?.id;
            } else {
                this.selectedThreadId = null;
            }
            await this.$nextTick();
            this.loadMores.forEach((component) => component.refreshScrollEl());
        },
        'displayedList.id': function onDisplayListChange() {
            this.selectedThreadId = null;
        },
        inResponsiveMode(val) {
            if (val) {
                this.responsiveDisplay = true;
                this.showThreadResp = false;
            } else {
                this.onResize();
            }
        },
    },
    mounted() {
        if (!this.inResponsiveMode) {
            this.$nextTick(() => {
                this.onResize();
            });
        }
    },
};
</script>

<style scoped>

.o-emails-main {
    &__side {
        max-height: calc(100vh - 200px);
        max-width: 300px;
        top: 100px;

        @apply
            mr-2
            overflow-y-auto
            pr-4
            shrink-0
            sticky
            w-1/3
        ;

        &--resp {
            max-height: 100%;

            @apply
                max-w-full
                mr-0
                overflow-y-visible
                pr-0
                w-full
            ;
        }
    }

    &__back {
        @apply
            py-1
        ;
    }
}

</style>
