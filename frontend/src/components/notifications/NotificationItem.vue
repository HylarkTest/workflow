<template>
    <div class="o-notification-item">
        <div class="o-notification-item__main">
            <div
                v-if="notification.isNew()"
                class="o-notification-item__new"
            >
            </div>

            <div class="mr-4 relative">
                <ImageOrFallback
                    class="h-16 w-16"
                    :class="backgroundColor"
                    imageClass="rounded-xl"
                    :image="image"
                    :icon="channelIcon"
                    iconClass="text-2xl"
                >
                </ImageOrFallback>

                <div
                    v-if="image"
                    class="o-notification-item__badge centered"
                    :class="channelColor"
                >
                    <i
                        :class="channelIcon"
                    >
                    </i>
                </div>
            </div>

            <div class="flex-1 flex flex-col">
                <div class="flex items-start justify-between">
                    <div class="font-semibold mb-1 mr-6">
                        {{ notification.title }}
                    </div>

                    <button
                        v-if="link"
                        class="o-notification-item__link"
                        :class="channelColor"
                        type="button"
                    >
                        Learn more
                    </button>
                </div>

                <div class="text-cm-600 text-sm">
                    <template
                        v-if="!expanded"
                    >
                        {{ notification.preview }}
                    </template>

                    <div
                        v-if="expanded"
                        v-dompurify-html="fullContent"
                    >
                    </div>

                    <button
                        v-if="fullContent"
                        class="font-semibold hover:underline"
                        :class="expanded ? 'mt-2' : 'ml-1'"
                        type="button"
                        @click="toggleExpanded"
                    >
                        {{ expansionText }}
                    </button>
                </div>

                <div class="flex justify-end flex-1 items-end">
                    <div
                        v-t="dateAtTime"
                        class="text-xs text-cm-500"
                    >
                    </div>
                </div>
            </div>
        </div>

        <button
            v-t="`common.${cleared ? 'unclear' : 'clear'}`"
            class="o-notifications-page__clear text-xs bg-primary-100"
            type="button"
            @click="toggleClear"
        >
        </button>
    </div>
</template>

<script>

const channelInfo = {
    NEW_FEATURES: {
        colorClasses: 'bg-turquoise-100 text-turquoise-600',
        icon: 'fa-rectangle-history-circle-plus',
    },
    TIPS: {
        colorClasses: 'bg-violet-100 text-violet-600',
        icon: 'fa-info-circle',
    },
    ACCOUNT: {
        colorClasses: 'bg-rose-100 text-rose-600',
        icon: 'fa-user',
    },
};

export default {
    name: 'NotificationItem',
    components: {

    },
    mixins: [
    ],
    props: {
        notification: {
            type: Object,
            required: true,
        },
        cleared: Boolean,
    },
    emits: [
        'clear',
        'unclear',
    ],
    data() {
        return {
            expanded: false,
        };
    },
    computed: {
        link() {
            return this.notification.link;
        },
        date() {
            return this.notification.createdAt;
        },
        dateAdjusted() {
            return utils.dateWithTz(this.date);
        },
        dateAtTime() {
            return {
                path: 'common.dates.dateAtTime',
                args: {
                    date: this.dateAdjusted.format('MMM D'),
                    time: this.dateAdjusted.format('LT'),
                },
            };
        },
        backgroundColor() {
            return this.image ? '' : this.channelColor;
        },
        channel() {
            return this.notification.channel;
        },
        channelDetails() {
            return channelInfo[this.channel];
        },
        channelColor() {
            return this.channelDetails.colorClasses;
        },
        channelIcon() {
            return `far ${this.channelDetails.icon}`;
        },
        image() {
            return this.notification.image;
        },
        expansionText() {
            return this.expanded ? 'Minimize' : 'Continue reading';
        },
        fullContent() {
            return this.notification.fullContent;
        },
    },
    methods: {
        toggleExpanded() {
            this.expanded = !this.expanded;
        },
        toggleClear() {
            const event = this.cleared ? 'unclear' : 'clear';
            this.$emit(event, this.notification);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-notification-item {
    @apply
        flex
        items-start
    ;

    &__main {
        @apply
            bg-cm-00
            flex
            flex-1
            items-start
            mr-4
            p-5
            relative
            rounded-xl
        ;
    }

    &__new {
        height: 8px;
        left: 10px;
        top: 10px;
        width:  8px;

        @apply
            absolute
            bg-peach-500
            rounded-full
            shadow-center
            shadow-peach-400
        ;
    }

    &__badge {
        bottom: -4px;
        right:  -4px;

        @apply
            absolute
            h-8
            rounded-full
            text-sm
            w-8
        ;
    }

    &__link {
        transition: 0.2s ease-in-out;

        @apply
            font-semibold
            px-3
            py-1
            rounded-lg
            text-xs
        ;

        &:hover {
            @apply
                shadow
            ;
        }
    }
}

</style>
