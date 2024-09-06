<template>
    <div
        class="o-notifications-page"
    >
        <LayoutPage
            :headerProps="headerProps"
            :isLoading="isLoading"
        >
            <div class="flex flex-col h-full">
                <div
                    class="flex mb-4 flex-wrap -m-1"
                >
                    <ButtonEl
                        v-for="channel in channels"
                        :key="channel.val"
                        class="o-notifications-page__filter"
                        :class="selectedClass(channel.val, 'selectedChannel')"
                        @click="selectOption(channel.val, 'selectedChannel')"
                    >
                        <span
                            v-t="getPath(channel.val)"
                        >
                        </span>

                        <div
                            v-if="channel.count"
                            class="o-notifications-page__count centered"
                        >
                            {{ channel.count }}
                        </div>

                    </ButtonEl>
                </div>

                <div
                    class="o-notifications-page__lists px-8"
                >
                    <ButtonEl
                        v-for="list in lists"
                        :key="list.val"
                        class="o-notifications-page__list"
                        :class="{ 'o-notifications-page__list--selected': isSelected(list.val, 'selectedList') }"
                        @click="selectOption(list.val, 'selectedList')"
                    >
                        <span
                            v-t="getPath(list.val)"
                        >
                        </span>

                        <div
                            v-if="list.count"
                            class="o-notifications-page__count centered"
                        >
                            {{ list.count }}
                        </div>
                    </ButtonEl>
                </div>

                <div class="flex-1 overflow-y-auto px-8">
                    <div
                        v-if="showClearAll"
                        class="flex justify-end"
                    >
                        <button
                            v-t="'common.clearAll'"
                            class="o-notifications-page__clear mb-2 text-sm"
                            type="button"
                            @click="clearAll"
                        >
                        </button>
                    </div>

                    <NoContentText
                        v-if="!hasNotificationsOnList"
                        class="mt-10"
                        sourceList="SYSTEM_SET"
                        :specificObjectVal="specificNoContentVal"
                        :customMessagePath="noContentMessagePath"
                        :customIcon="noContentIcon"
                    >
                    </NoContentText>

                    <LoadMore
                        :hasNext="hasMore"
                        @nextPage="showMore"
                    >
                        <div
                            v-for="items in notificationsByDates"
                            :key="items.date"
                            class="mb-6"
                        >
                            <div
                                class="o-notifications-page__time"
                            >
                                {{ dateHeader(items.date) }}
                            </div>

                            <div>
                                <NotificationItem
                                    v-for="notification in items.notifications"
                                    :key="notification.id"
                                    class="mb-2"
                                    :notification="notification"
                                    :cleared="isArchive"
                                    @clear="clearNotification(notification)"
                                    @unclear="unclearNotification(notification)"
                                >

                                </NotificationItem>
                            </div>
                        </div>
                    </LoadMore>

                    <!-- Re-add updated loaders -->
                    <!-- <ScreenNotifications
                        v-if="hasMore"
                    >
                    </ScreenNotifications> -->
                </div>
            </div>

        </LayoutPage>
    </div>
</template>

<script>

import NotificationItem from './NotificationItem.vue';
import NOTIFICATIONS from '@/graphql/notifications/queries/Notifications.gql';

import LayoutPage from '@/components/layout/LayoutPage.vue';
import LoadMore from '@/components/data/LoadMore.vue';

import { fromNowWithToday } from '@/core/helpers/dateHelpers.js';

import {
    clearAllNotifications,
    clearNotification,
    initializeNotifications, markNotificationsAsSeen,
    unclearNotification,
} from '@/core/repositories/notificationsRepository.js';
import { reloadUser } from '@/core/repositories/userRepository.js';

export default {
    name: 'NotificationsPage',
    components: {
        LoadMore,
        LayoutPage,
        NotificationItem,
    },
    mixins: [
    ],
    props: {

    },
    apollo: {
        notifications: {
            query: NOTIFICATIONS,
            variables() {
                const variables = {
                    channel: this.selectedChannel,
                };

                if (this.selectedList === 'UNREAD') {
                    variables.filter = 'ONLY_UNCLEARED';
                } else if (this.selectedList === 'ARCHIVE') {
                    variables.filter = 'ONLY_CLEARED';
                }

                return variables;
            },
            update: initializeNotifications,
        },
    },
    data() {
        return {
            selectedList: null,
            selectedChannel: 'ALL',
            fetchingMore: false,
            headerProps: {
                name: this.$t('links.notifications'),
                iconProp: 'far fa-bell',
            },
        };
    },
    computed: {
        isLoading() {
            return this.$apollo.loading;
        },
        isFetchingMore() {
            return this.fetchingMore;
        },
        isAll() {
            return this.selectedChannel === 'ALL';
        },
        hasNotificationsOnList() {
            return this.notifications?.notifications.length;
        },
        showClearAll() {
            return this.isUnread && this.hasNotificationsOnList;
        },
        isUnread() {
            return this.selectedList === 'UNREAD';
        },
        isArchive() {
            return this.selectedList === 'ARCHIVE';
        },
        notificationsByDates() {
            if (!this.notifications) {
                return [];
            }
            const dates = [];
            this.notifications.notifications.forEach((notification) => {
                const date = this.$dayjs(notification.createdAt).format('YYYY-MM-DD');
                const dateExists = _.find(dates, { date });
                if (dateExists) {
                    dateExists.notifications.push(notification);
                } else {
                    dates.push({
                        date,
                        notifications: [notification],
                    });
                }
            });
            return dates;
        },
        lists() {
            const channel = this.selectedChannel;
            const channelInfo = _.find(this.channels, ['val', channel]);
            return [
                {
                    val: 'UNREAD',
                    count: channelInfo?.count,
                },
                {
                    val: 'ARCHIVE',
                },
            ];
        },
        hasMore() {
            return this.notifications?.notifications.__NotificationConnection.pageInfo.hasNextPage;
        },
        channels() {
            if (!this.notifications) {
                return [];
            }
            return [
                {
                    val: 'ALL',
                    count: this.notifications.notifications.__NotificationConnection.meta.unclearedCount,
                },
                ...this.notifications.notifications.__NotificationConnection.meta.channels.map((channelInfo) => {
                    return {
                        val: channelInfo.channel,
                        count: channelInfo.unclearedCount,
                    };
                }),
            ];
        },
        specificNoContentVal() {
            return this.isArchive ? 1 : 3;
        },
        noContentMessagePath() {
            let path = _.camelCase(this.selectedList);
            if (!this.isAll) {
                path = path.concat('Channel');
            }
            return `noContent.notifications.${path}`;
        },
        noContentIcon() {
            return this.isUnread ? 'fa-bell-on' : '';
        },
    },
    methods: {
        getPath(val) {
            return `notifications.${_.camelCase(val)}`;
        },
        isSelected(val, source) {
            return val === this[source];
        },
        selectedClass(val, source) {
            return { 'o-notifications-page__filter--selected': this.isSelected(val, source) };
        },
        selectOption(val, source) {
            this[source] = val;
        },
        async handleNotification(operation, notification = null) {
            await operation(notification);
            reloadUser();
        },
        clearAll() {
            this.handleNotification(clearAllNotifications);
        },
        clearNotification(notification) {
            this.handleNotification(clearNotification, notification);
        },
        unclearNotification(notification) {
            this.handleNotification(unclearNotification, notification);
        },
        showMore() {
            if (this.fetchingMore) {
                return;
            }
            this.fetchingMore = true;
            this.$apollo.queries.notifications.fetchMore({
                variables: {
                    after: this.notifications?.notifications.__NotificationConnection.pageInfo.endCursor,
                },
            }).finally(() => {
                this.fetchingMore = false;
            });
        },
        dateHeader(date) {
            return fromNowWithToday(date);
        },
    },
    created() {
        this.selectedList = this.lists[0].val;
    },
    unmounted() {
        markNotificationsAsSeen();
    },
};
</script>

<style scoped>

.o-notifications-page {
    &__filter {
        transition: 0.2s ease-in-out;

        @apply
            bg-cm-100
            flex
            font-semibold
            items-center
            m-1
            px-4
            py-1
            rounded-lg
            text-cm-500
            text-sm
        ;

        &:hover:not(.o-notifications-page__filter--selected) {
            @apply
                shadow-lg
            ;
        }

        &--selected {
            @apply
                bg-cm-00
                shadow-lg
                shadow-primary-200
                text-primary-600
            ;
        }
    }

    &__count {
        height:  19px;
        min-width: 19px;

        @apply
            bg-primary-200
            font-semibold
            ml-2
            p-1
            rounded-md
            text-primary-600
            text-xs
        ;
    }

    &__lists {
        @apply
            border-b
            border-cm-300
            border-solid
            flex
            justify-center
            mb-4
            w-full
        ;
    }

    &__list {
        transition:  background-color 0.2s ease-in-out;

        @apply
            flex
            items-center
            px-5
            py-1
            rounded-t-lg
            text-cm-500
        ;

        &:hover:not(.o-notifications-page__list--selected) {
            @apply
                bg-cm-100
            ;
        }

        &--selected {
            @apply
                font-semibold
                text-cm-800
            ;
        }
    }

    &__time {
        @apply
            font-semibold
            py-1
            text-cm-400
        ;
    }

    & :deep(.o-notifications-page__clear) {
        transition: 0.2s ease-in-out;

        @apply
            font-semibold
            px-4
            py-1
            rounded-lg
            text-primary-600
        ;

        &:hover {
            @apply
                bg-primary-600
                text-cm-00
            ;
        }
    }
}

</style>
