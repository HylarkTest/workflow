<template>
    <ButtonEl
        class="o-email-item cursor-pointer transition-2eio"
        :class="isSelectedClass"
    >
        <div
            v-if="isSelected"
            class="h-full w-1 bg-primary-300 rounded-full min-h-full absolute top-0 left-0"
        >
            &nbsp;
        </div>

        <div class="flex min-w-0">
            <EmailFrom
                class="mr-2 shrink-0"
                size="sm"
                :email="item"
            >
            </EmailFrom>

            <div class="flex-1 min-w-0">
                <div class="flex justify-between mb-1">
                    <span
                        v-if="isDraft"
                        class="text-peach-600 italic"
                    >
                        (Draft)
                    </span>
                    <div
                        class="mr-2 break-words min-w-0"
                        :class="isUnread ? 'font-bold' : 'font-medium'"
                    >
                        {{ peopleJoined }}
                    </div>

                    <div class="flex">
                        <i
                            v-if="hasAttachments"
                            class="fal fa-paperclip text-cm-400 ml-1"
                        >
                        </i>

                        <i
                            v-if="hasHighPriority"
                            class="fas fa-exclamation text-peach-600 ml-1"
                        >
                        </i>

                        <i
                            v-if="isFlagged"
                            class="fa-solid ml-1.5"
                            :class="flagClasses"
                        >
                        </i>
                    </div>
                </div>
                <div
                    v-if="subject"
                    class="text-xs font-semibold mb-1 break-words"
                    :class="isUnread ? 'text-primary-600' : 'text-cm-500'"
                >
                    {{ subject }}
                </div>

                <div class="text-cm-600 text-xs break-words">
                    {{ text }}
                </div>
            </div>
        </div>

        <div class="flex justify-end text-xs italic text-cm-500">
            {{ dateFormatted }}
        </div>

        <FeatureSource
            v-if="!isInMailbox"
            class="justify-end"
            :featureItem="item"
            listKey="mailbox"
        >
        </FeatureSource>

        <div
            v-if="associationsLength"
            class="flex flex-wrap gap-2 justify-end mt-1"
        >
            <ConnectedRecord
                v-for="association in associations"
                :key="association.id"
                :isMinimized="true"
                :item="association"
            >
            </ConnectedRecord>
        </div>

        <!-- <div
            v-if="associatedAddressesLength"
        >
            <a
                v-for="address in associatedAddresses"
                :key="address"
                :href="'mailto:' + address"
            >
                {{ address }}
            </a>
        </div> -->

    </ButtonEl>
</template>

<script>

import EmailFrom from './EmailFrom.vue';
import FeatureSource from '@/components/features/FeatureSource.vue';

export default {
    name: 'EmailItem',
    components: {
        EmailFrom,
        FeatureSource,
    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        selectedThreadId: {
            type: String,
            default: null,
        },
        isInMailbox: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        associations() {
            return this.item.associations;
        },
        associationsLength() {
            return this.associations.length;
        },
        // associatedEmails() {
        //     return this.item.associationSources?.filter((source) => source.source === 'ADDRESS');
        // },
        // associatedAddresses() {
        //     return this.associatedEmails?.map((email) => email.address);
        // },
        // associatedAddressesLength() {
        //     return this.associatedAddresses?.length;
        // },
        isSelected() {
            return this.selectedThreadId === this.item.id;
        },
        isSelectedClass() {
            return this.isSelected ? 'shadow-lg shadow-primary-400/20' : 'hover:shadow-xl';
        },
        subject() {
            return this.item.subject;
        },
        from() {
            return this.item.from.fromName();
        },
        people() {
            return this.item.correspondentNames();
        },
        peopleJoined() {
            return this.people.join(', ');
        },
        isFlagged() {
            return this.item.isFlagged;
        },
        isDraft() {
            return this.item.isDraft;
        },
        flagClasses() {
            if (this.item.account.provider === 'MICROSOFT') {
                return 'text-peach-600 fa-flag-swallowtail';
            }
            return 'text-gold-600 fa-star';
        },
        text() {
            return this.item.text;
        },
        date() {
            return this.item.date;
        },
        dateFormatted() {
            return this.$dayjs(this.date).fromNow();
        },
        priority() {
            return this.item.priority;
        },
        hasHighPriority() {
            return this.priority === 1;
        },
        isUnread() {
            return !this.item.isSeen;
        },
        hasAttachments() {
            return this.item.hasAttachments;
        },

    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.o-email-item {
    @apply
        px-3
        py-3
        relative
        rounded-lg
        text-sm
    ;
}

</style>
