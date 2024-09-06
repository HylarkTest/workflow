<template>
    <div class="o-email-full">

        <div class="mx-4">
            <div class="flex justify-end text-xs italic text-cm-500 mb-1">
                {{ dateFormatted }}
            </div>

            <div class="flex justify-between">
                <div class="flex">
                    <EmailFrom
                        class="mr-3"
                        :email="email"
                    >
                    </EmailFrom>

                    <div class="text-sm">
                        <div
                            v-if="from"
                            class="font-semibold"
                            :title="fromEmail"
                        >
                            {{ from }}
                        </div>

                        <div
                            v-if="toLength"
                            class="flex"
                        >
                            To:
                            <span class="ml-1 flex flex-wrap">
                                <span
                                    v-for="(person, index) in to"
                                    :key="index"
                                    :class="{ 'mr-1': index < toLength - 1 }"
                                    :title="person.address"
                                >
                                    {{ person.name }}<!--
                                     --><template
                                        v-if="index < toLength - 1"
                                    >
                                        {{ ',' }}
                                    </template>
                                </span>
                            </span>
                        </div>
                        <div
                            v-if="cc && cc.length"
                            class="flex"
                        >
                            cc:
                            <span class="ml-1 flex flex-wrap">
                                <span
                                    v-for="(person, index) in cc"
                                    :key="index"
                                    :class="{ 'mr-1': index < cc.length - 1 }"
                                    :title="person.address"
                                >
                                    {{ person.name }}<!--
                                     --><template
                                        v-if="index < cc.length - 1"
                                    >
                                        {{ ',' }}
                                    </template>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <i
                    v-if="hasHighPriority"
                    class="fas fa-exclamation text-peach-600 mr-1 text-2xl"
                >
                </i>
            </div>
        </div>

        <div
            v-if="explicitAttachments.length"
            class="mt-2 -m-1 px-4 flex flex-wrap"
        >
            <div
                v-for="(file, index) in explicitAttachments"
                :key="index"
                class="py-1 px-3 bg-cm-100 rounded-md text-xssm flex items-center"
            >
                {{ file.name }}

                <!-- eslint-disable-next-line -->
                <a
                    class="ml-2"
                    rel="noreferrer noopener"
                    :href="file.link"
                    target="_blank"
                >
                    <IconHover
                        class="c-icon-hover--sm"
                        icon="far fa-download"
                        iconColor="text-primary-600"
                    >
                    </IconHover>
                </a>
            </div>
        </div>

        <div
            v-dompurify-html="html"
            class="o-email-full__email show-html revert-tailwind"
        >
        </div>
    </div>
</template>

<script>

import EmailFrom from './EmailFrom.vue';
import IconHover from '@/components/buttons/IconHover.vue';
import Email from '@/core/models/Email.js';

export default {
    name: 'EmailFull',
    components: {
        IconHover,
        EmailFrom,
    },
    mixins: [
    ],
    props: {
        email: {
            type: Email,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        from() {
            return this.email.fromName();
        },
        to() {
            return this.email.to;
        },
        toLength() {
            return this.to.length;
        },
        cc() {
            return this.email.cc;
        },
        ccNames() {
            return this.cc?.map((person) => {
                return person.name;
            });
        },
        attachments() {
            return this.email.attachments || [];
        },
        explicitAttachments() {
            return this.attachments.filter((attachment) => {
                return !attachment.isInline;
            });
        },
        fromUser() {
            return this.email.isFromAccountOwner();
        },
        date() {
            return this.email.date;
        },
        dateFormatted() {
            return this.$dayjs(this.date).format('LL LT');
        },
        html() {
            return this.email.htmlWithInlineAttachments();
        },
        priority() {
            return this.email.priority;
        },
        hasHighPriority() {
            return this.priority === 1;
        },
        fromEmail() {
            return this.email.from?.address;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.o-email-full {
    @apply
        flex
        flex-col
    ;

    &__email {
        /* stylelint-disable-next-line */
        font-family: auto;

        @apply
            flex-1
            h-full
            mt-6
            overflow-y-auto
            px-4
        ;
    }
}

</style>
