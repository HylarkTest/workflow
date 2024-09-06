<template>
    <div class="o-support-home">
        <div class="flex">
            <BirdImage
                class="h-28 mr-8 xxs:h-36"
                whichBird="Book_Bird_300dpi.png"
            >
            </BirdImage>

            <div class="mt-2">
                <h2 class="font-bold text-3xl text-primary-800 mb-4">
                    Welcome to the Hylark knowledge base!
                </h2>

                <p class="text-cm-600 max-w-md">
                    Your one-stop shop for information about Hylark and how to use it,
                    taking you all the way from a brand new account to advanced customizations.
                </p>
            </div>
        </div>

        <div
            v-if="frequentlyUsedTopics?.length"
            class="mt-10 flex items-start mb-16 flex-wrap xxs:flex-nowrap"
        >
            <label class="mt-1 mr-4 font-medium mb-2 xxs:mb-0">
                Suggestions:
            </label>

            <div class="flex flex-wrap gap-2">
                <button
                    v-for="topic in frequentlyUsedTopics"
                    :key="topic.id"
                    class="button-secondary--light button-rounded"
                    type="button"
                    @click="openSearch(topic)"
                >
                    {{ topic.name }}
                </button>
            </div>
        </div>

        <div
            v-if="popularCategories?.length"
            class="mt-10"
        >
            <p
                class="font-bold text-lg text-azure-800 mb-1"
            >
                <i
                    class="fa-solid fa-star mr-1 text-gold-600"
                >
                </i>
                Most popular
            </p>

            <div class="flex flex-wrap -m-2">
                <div
                    v-for="category in popularCategories"
                    :key="category.id"
                    class="w-full p-2 lg:w-1/2"
                >
                    <div class="p-4 border border-solid border-cm-200 rounded-xl h-full">
                        <div class="font-bold text-azure-700 mb-4">
                            {{ category.name }}
                        </div>

                        <div class="ml-4">
                            <router-link
                                v-for="article in category.articles"
                                :key="article.friendlyUrl"
                                :to="{ name: 'support.article', params: { friendlyUrl: article.friendlyUrl } }"
                                class="o-support-home__popular hover:underline transition-2eio"
                            >
                                <i
                                    class="text-cm-300 mr-2"
                                    :class="getIcon(article)"
                                >
                                </i>

                                <span class="text-azure-400 font-medium">
                                    {{ article.title }}
                                </span>
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="flex flex-wrap gap-8 mt-10"
        >
            <div
                v-if="recommendedArticles.length"
                class="flex-1 min-w-full xxs:min-w-300p"
            >
                <p
                    class="font-bold text-lg mb-1"
                >
                    <i
                        class="fa-regular fa-thumbs-up mr-1 text-emerald-600"
                    >
                    </i>
                    Recommended articles
                </p>

                <div class="flex flex-col gap-2">
                    <SupportArticleMini
                        v-for="article in recommendedArticles"
                        :key="article.friendlyUrl"
                        :article="article"
                    >
                    </SupportArticleMini>
                </div>
            </div>
            <div
                v-if="recentArticles?.length"
                class="flex-1 min-w-full xxs:min-w-300p"
            >
                <p
                    class="font-bold text-lg text-azure-800 mb-1"
                >
                    Recent articles
                </p>

                <div class="flex flex-col gap-2">
                    <SupportArticleMini
                        v-for="article in recentArticles"
                        :key="article.friendlyUrl"
                        :article="article"
                    >
                    </SupportArticleMini>
                </div>
            </div>
        </div>

        <SupportSearch
            v-if="isModalOpen"
            :presetSearchTerm="presetSearchTerm"
            @closeModal="closeSearch"
        >
        </SupportSearch>
    </div>
</template>

<script>

import SupportSearch from '@/components/supportSite/SupportSearch.vue';
import SupportArticleMini from '@/components/supportSite/SupportArticleMini.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import {
    getPopularCategories,
    getRecentArticles,
    getRecommendedArticles,
    getPopularTopics,
    getTopics,
} from '@/core/repositories/supportRepository.js';

import { getSupportKeyWordIcon } from '@/core/display/supportIcons.js';

export default {
    name: 'SupportHome',
    components: {
        SupportSearch,
        SupportArticleMini,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {

    },
    data() {
        return {
            presetSearchTerm: '',
            recentArticles: [],
            recommendedArticles: [],
            frequentlyUsedTopics: [],
            topics: [],
            popularCategories: [],
        };
    },
    computed: {
    },
    methods: {
        openSearch(term) {
            this.presetSearchTerm = term.name;
            this.openModal();
        },
        closeSearch() {
            this.closeModal();
            this.presetSearchTerm = '';
        },
        getIcon(article) {
            return getSupportKeyWordIcon(article);
        },
    },
    async created() {
        this.recentArticles = await getRecentArticles();
        this.recommendedArticles = await getRecommendedArticles();
        this.frequentlyUsedTopics = await getPopularTopics();
        this.topics = await getTopics();
        this.popularCategories = await getPopularCategories();
    },
};
</script>

<style scoped>

.o-support-home {

    &__popular {
        @apply
            flex
            items-baseline
            my-2
            text-primary-500
            text-sm
        ;
    }

}

</style>
