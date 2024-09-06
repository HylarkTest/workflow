<template>
    <Modal
        class="o-support-search"
        positioning="TOP"
        containerBgClass="bg-cm-100"
        :containerClass="containerClasses"
        v-bind="$attrs"
        @closeModal="$emit('closeModal')"
    >
        <template
            v-if="!viewedArticle"
        >
            <h1
                v-if="$slots.header"
                class="text-xl text-center font-bold mb-2"
            >
                <slot
                    name="header"
                >
                </slot>
            </h1>

            <div class="mb-5">
                <h2
                    class="font-smbase font-semibold text-cm-600 mb-2"
                >
                    Filter by topic
                </h2>
                <div
                    class="flex gap-2 flex-wrap"
                >
                    <ButtonEl
                        class="button-rounded"
                        :class="allTopicClass"
                        @click="selectAll"
                    >
                        All
                    </ButtonEl>

                    <ButtonEl
                        v-for="topic in topics"
                        :key="topic.id"
                        class="button-rounded"
                        :class="topicClass(topic)"
                        @click="toggleTopic(topic)"
                    >
                        {{ topic.name }}
                    </ButtonEl>
                </div>
            </div>

            <div class="mb-10">
                <InputBox
                    ref="searchInput"
                    v-model="searchTerm"
                    boxStyle="border"
                    :icon="icon"
                    :highlightIconOnFocus="true"
                    placeholder="Search knowledge base"
                >
                </InputBox>
            </div>

            <div>
                <p
                    v-if="showDefaults"
                    class="font-semibold text-azure-600 text-sm mb-2"
                >
                    {{ defaultText }}
                </p>

                <div
                    v-if="!isLoadingResults"
                    class="flex flex-col gap-2"
                >
                    <SupportArticleSummary
                        v-for="article in articlesSource"
                        :key="article.friendlyUrl"
                        :recentlyAdded="showDefaults"
                        :article="article"
                        :hasNoRouter="areArticlesInline"
                        @selectArticle="selectArticle"
                    >
                    </SupportArticleSummary>
                </div>

                <LoaderFetch
                    v-else
                    class="py-4"
                    :isFull="true"
                    :sphereSize="40"
                >
                </LoaderFetch>
            </div>

            <NoContentText
                v-if="showNoMatches"
                class="text-center"
                customIcon="far fa-magnifying-glass"
                :customHeaderPath="noResultsPath"
            >
            </NoContentText>

            <NoContentText
                v-if="searchTerm && !hasValidSearchTerm && !results"
                customIcon="far fa-font-case"
                customHeaderPath="support.search.noContent.twoChars"
            >
            </NoContentText>
        </template>

        <div
            v-else
        >
            <BackRounded
                class="mb-2"
                colorClasses="button-primary"
                :buttonTextPath="buttonTextPath"
                @click="closeArticle"
            >
            </BackRounded>

            <SupportArticle
                class="bg-cm-00 p-4 rounded-xl"
                :friendlyUrl="viewedArticle.friendlyUrl"
                :isArticleInline="areArticlesInline"
                @openArticle="openArticleFromUrl"
                @closeArticle="closeArticle"
            >
            </SupportArticle>
        </div>
    </Modal>
</template>

<script>

import SupportArticleSummary from './SupportArticleSummary.vue';
import SupportArticle from './SupportArticle.vue';
import BackRounded from '@/components/buttons/BackRounded.vue';

import { arrRemove } from '@/core/utils.js';

import {
    getRecentArticles,
    getTopics,
    searchArticles,
} from '@/core/repositories/supportRepository.js';

export default {
    name: 'SupportSearch',
    components: {
        SupportArticle,
        SupportArticleSummary,
        BackRounded,
    },
    mixins: [
    ],
    props: {
        presetSearchTerm: {
            type: String,
            default: '',
        },
        containerWidthClass: {
            type: String,
            default: 'w-[500px]',
        },
        relevantTopics: {
            type: [Array, null],
            default: null,
        },
        areArticlesInline: Boolean,
        viewedArticleFriendlyUrlProp: {
            type: String,
            default: '',
        },
    },
    emits: [
        'closeModal',
    ],
    data() {
        return {
            searchTerm: this.presetSearchTerm || '',
            recentArticles: [],
            results: null,
            isSearching: false,
            topics: [],
            viewedArticle: this.setInitialArticle(),
            filterTopics: [],
        };
    },
    computed: {
        buttonTextPath() {
            const url = this.viewedArticleFriendlyUrlProp;
            return url && (url === this.viewedArticle.friendlyUrl)
                ? 'support.toArticleSearch'
                : 'common.back';
        },
        isLoadingResults() {
            return this.isSearching && this.hasValidSearchTerm;
        },
        resultsLength() {
            return this.results?.length;
        },
        icon() {
            return {
                symbol: 'far fa-magnifying-glass',
                component: 'div',
                position: 'left',
            };
        },
        showDefaults() {
            return !this.results && !this.searchTerm;
        },
        articlesSource() {
            return this.showDefaults ? this.recentArticles : this.results;
        },
        hasValidSearchTerm() {
            return this.searchTerm.length >= 2;
        },
        showNoMatches() {
            return this.searchTerm
                && this.hasValidSearchTerm
                && !this.results?.length
                && !this.isSearching;
        },
        containerClasses() {
            return `p-6 bg-cm-100 ${this.containerWidthClass}`;
        },
        allTopicClass() {
            return !this.filterTopicsLength ? 'button-secondary' : 'button-secondary--light';
        },
        filterTopicsLength() {
            return this.filterTopics.length;
        },
        filterTopicNames() {
            return this.filterTopics.map((key) => {
                const topicObj = this.topics.find((topic) => topic.key === key);
                return topicObj.name;
            });
        },
        firstFilterTopicName() {
            return this.filterTopicNames[0];
        },
        defaultText() {
            if (this.filterTopicsLength === 1) {
                return `Recently added articles for "${this.firstFilterTopicName}"`;
            }
            if (this.filterTopicsLength > 1) {
                return 'Recently added articles for your selected topics';
            }
            return 'Recently added';
        },
        noResultsPath() {
            const path = this.filterTopicsLength ? 'noMatchesTopics' : 'noMatches';
            return `support.search.noContent.${path}`;
        },
    },
    methods: {
        focusOnSearch() {
            const input = this.$refs.searchInput;
            input?.focus();
        },
        // Method for searching articles using lodash debounce
        debounceSearch: _.debounce(async function debounceSearch() {
            if (this.searchTerm.length >= 2) {
                this.results = await searchArticles(this.searchTerm, this.filterTopics);
            } else {
                this.results = null;
            }
            this.isSearching = false;
        }, 100),
        selectArticle(article) {
            if (!this.areArticlesInline) {
                this.$emit('closeModal');
            } else {
                this.viewArticle(article);
            }
        },
        setInitialArticle() {
            if (this.viewedArticleFriendlyUrlProp) {
                return {
                    friendlyUrl: this.viewedArticleFriendlyUrlProp,
                };
            }
            return null;
        },
        viewArticle(article) {
            this.viewedArticle = article;
        },
        closeArticle() {
            this.viewedArticle = null;
        },
        selectAll() {
            this.filterTopics = [];
        },
        toggleTopic(topic) {
            if (this.isFilterTopic(topic)) {
                this.filterTopics = arrRemove(this.filterTopics, topic.key);
            } else {
                this.filterTopics = [...this.filterTopics, topic.key];
            }
        },
        isFilterTopic(topic) {
            return this.filterTopics.includes(topic.key);
        },
        topicClass(topic) {
            return this.isFilterTopic(topic) ? 'button-secondary' : 'button-secondary--light';
        },
        async fetchRecentArticles() {
            this.recentArticles = await getRecentArticles(this.filterTopics);
        },
        openArticleFromUrl(article) {
            this.viewedArticle = article;
        },
        getRelevantTopics() {
            const relevantTopics = this.topics?.filter((topic) => (
                this.relevantTopics?.includes(topic.key)
            ));
            return relevantTopics?.map((topic) => topic.key) || [];
        },
    },
    watch: {
        searchTerm: {
            immediate: true,
            handler() {
                this.isSearching = true;
                this.debounceSearch();
            },
        },
        filterTopics() {
            this.isSearching = true;
            this.debounceSearch();
            this.fetchRecentArticles();
        },
        topics: {
            immediate: true,
            handler() {
                this.filterTopics = this.getRelevantTopics();
            },
        },
    },
    async created() {
        this.fetchRecentArticles();
        this.topics = await getTopics();
    },
    mounted() {
        this.focusOnSearch();
    },
};
</script>

<style>

/*.o-support-search {
}*/

</style>
