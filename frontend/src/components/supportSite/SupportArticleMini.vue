<template>
    <router-link
        :to="{ name: 'support.article', params: { friendlyUrl: url } }"
        class="o-support-article-mini hover:shadow-md shadow-primary-400/20"
    >
        <div
            v-if="dateLabel"
            class="absolute px-1 text-xxs rounded font-semibold button-primary top-0 right-0"
        >
            <template
                v-if="isNew"
            >
                New
            </template>
            <template
                v-else
            >
                Updated
            </template>
        </div>
        <div
            class="font-medium"
        >
            {{ title }}
        </div>
    </router-link>
</template>

<script>

import interactsWithSupportArticle from '@/vue-mixins/support/interactsWithSupportArticle.js';

export default {
    name: 'SupportArticleMini',
    components: {

    },
    mixins: [
        interactsWithSupportArticle,
    ],
    props: {
        article: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        createdDaysAgo() {
            return this.$dayjs(this.createdAt).diff(this.$dayjs(), 'day');
        },
        isNew() {
            return this.createdDaysAgo < 31;
        },
        updatedDaysAgo() {
            return this.$dayjs(this.updatedAt).diff(this.$dayjs(), 'day');
        },
        isRecentlyUpdated() {
            return this.updatedDaysAgo < 31;
        },
        dateLabel() {
            return this.isRecentlyUpdated || this.isNew;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.o-support-article-mini {
    transition: 0.2s ease-in-out;

    @apply
        bg-azure-100
        pb-3
        pt-5
        px-5
        relative
        rounded-lg
        text-sm
    ;
}

</style>
