<template>
    <component
        :is="hasNoRouter ? 'ButtonEl' : 'RouterLink'"
        :to="{ name: 'support.article', params: { friendlyUrl: url } }"
        class="o-support-article-summary hover:shadow-azure-400/40 transition-2eio"
        @click="$emit('selectArticle', article)"
    >
        <div
            v-if="icon"
            class="text-xl mr-4 text-cm-300 shrink-0"
        >
            <i
                :class="icon"
            >
            </i>
        </div>

        <div class="flex-1">
            <div>
                <div class="font-semibold">
                    {{ title }}
                </div>
                <div class="text-cm-500 text-xs">
                    {{ description }}
                </div>
            </div>

            <div
                v-if="topicsLength"
                class="flex flex-wrap gap-2 justify-end mt-2"
            >
                <div
                    v-for="topic in topics"
                    :key="topic.id"
                    class="rounded-full text-xs px-2 text-azure-600 bg-azure-100"
                >
                    {{ topic.name }}
                </div>
            </div>
        </div>
    </component>
</template>

<script>

import interactsWithSupportArticle from '@/vue-mixins/support/interactsWithSupportArticle.js';

import { getSupportKeyWordIcon } from '@/core/display/supportIcons.js';

export default {
    name: 'SupportArticleSummary',
    components: {

    },
    mixins: [
        interactsWithSupportArticle,
    ],
    props: {
        recentlyAdded: Boolean,
        article: {
            type: Object,
            required: true,
        },
        hasNoRouter: Boolean,
    },
    emits: [
        'selectArticle',
    ],
    data() {
        return {

        };
    },
    computed: {
        description() {
            return this.article.descriptionTrimmed;
        },
        icon() {
            const recent = this.recentlyAdded ? 'recentlyAdded' : null;
            return getSupportKeyWordIcon(this.article, recent);
        },

    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.o-support-article-summary {
    @apply
        bg-cm-00
        flex
        px-3
        py-2
        rounded-lg
        shadow-md
        text-sm
    ;
}

</style>
