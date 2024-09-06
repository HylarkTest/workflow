<template>
    <div
        class="o-support-article"
    >
        <LoaderFetch
            v-if="!article"
            class="py-10"
            :isFull="true"
            :sphereSize="40"
        >
        </LoaderFetch>

        <template
            v-else
        >
            <div class="flex flex-wrap">
                <DateLabel
                    class="mr-6"
                    :date="updatedAt"
                    :includeLabel="true"
                    :fullTime="true"
                    mode="UPDATED_AT"
                >
                </DateLabel>
                <DateLabel
                    :date="liveAt"
                    :includeLabel="true"
                    :fullTime="true"
                >
                </DateLabel>
            </div>
            <div
                v-if="topicsLength"
                class="flex flex-wrap gap-2 mt-3"
            >
                <div
                    v-for="topic in topics"
                    :key="topic.id"
                    class="rounded-full text-xs px-2 text-azure-600 bg-azure-100"
                >
                    {{ topic.name }}
                </div>
            </div>
            <h1 class="text-4xl font-bold text-azure-900 mt-4">
                {{ title }}
            </h1>

            <SupportArticleContent
                ref="description"
                :content="rawDescription"
                class="mt-4"
            ></SupportArticleContent>

            <hr
                class="my-8"
            />

            <div class="o-support-article__helpful">
                <p class="font-semibold">
                    Was this article helpful?
                </p>

                <div
                    class="flex gap-4 mt-2"
                >
                    <button
                        v-if="!hasVotedDown"
                        class="button text-emerald-600 bg-emerald-200 transition-2eio hover:bg-emerald-100"
                        :class="{ unclickable: hasVotedUp }"
                        type="button"
                        @click="thumbsUp"
                    >
                        <i class="fa-regular fa-thumbs-up mr-1">
                        </i>
                        Yes
                    </button>
                    <button
                        v-if="!hasVotedUp"
                        class="button text-peach-600 bg-peach-200 transition-2eio hover:bg-peach-100"
                        :class="{ unclickable: hasVotedDown }"
                        type="button"
                        @click="thumbsDown"
                    >
                        <i class="fa-regular fa-thumbs-down mr-1">
                        </i>
                        No
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>

<script>

import interactsWithSupportArticle from '@/vue-mixins/support/interactsWithSupportArticle.js';

import {
    getArticle,
    incrementViewCount,
    thumbsUp,
    thumbsDown,
} from '@/core/repositories/supportRepository.js';
import LoaderFetch from '@/components/loaders/LoaderFetch.vue';
import SupportArticleContent from '@/components/supportSite/SupportArticleContent.vue';

export default {
    name: 'SupportArticle',
    components: {
        SupportArticleContent,
        LoaderFetch,
    },
    mixins: [
        interactsWithSupportArticle,
    ],
    props: {
        friendlyUrl: {
            type: String,
            required: true,
        },
        isArticleInline: Boolean,
    },
    emits: [
        'openArticle',
    ],
    data() {
        return {
            article: null,
            hasVotedUp: false,
            hasVotedDown: false,
        };
    },
    computed: {
        rawDescription() {
            return this.article?.description;
        },
    },
    methods: {
        async getArticle() {
            if (this.friendlyUrl) {
                this.article = await getArticle(this.friendlyUrl);
            }
        },
        async incrementViewCount() {
            await incrementViewCount(this.friendlyUrl);
        },
        async thumbsUp() {
            if (!this.hasVotedUp) {
                this.hasVotedUp = true;
                await thumbsUp(this.friendlyUrl);
            }
        },
        async thumbsDown() {
            if (!this.hasVotedDown) {
                this.hasVotedDown = true;
                await thumbsDown(this.friendlyUrl);
            }
        },
        processDescription() {
            // Create a temporary container to hold the HTML content
            const container = this.$refs.description.$el;

            // Find and replace all <a> elements with <span> elements
            const links = container.querySelectorAll('a[data-ref="internal-link"]');
            // const links = tempContainer.querySelectorAll('a[class="article-link"]');
            links.forEach((link) => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const friendlyUrl = link.href.split('/').pop();
                    if (this.isArticleInline) {
                        this.openArticle(friendlyUrl);
                    } else {
                        this.$router.push({ name: 'support.article', params: { friendlyUrl } });
                    }
                });
            });
        },
        openArticle(friendlyUrl) {
            this.$emit('openArticle', { friendlyUrl });
        },
    },
    watch: {
        friendlyUrl() {
            this.getArticle();
        },
        async rawDescription() {
            await this.$nextTick();
            await this.$nextTick();
            this.processDescription();
        },
    },
    created() {
        this.getArticle();
        this.incrementViewCount();
    },
};
</script>

<style>

.o-support-article {
    &__helpful {
        @apply
            bg-cm-100
            flex
            flex-col
            items-center
            p-4
            rounded-xl
        ;
    }
}

</style>
