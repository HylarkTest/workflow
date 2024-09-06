<template>
    <div
        v-if="folder"
        class="o-support-folder"
    >
        <div class="text-center mb-16">
            <h1 class="text-4xl text-primary-700 font-semibold">
                {{ folder.name }}
            </h1>

            <p class="italic text-cm-500">
                {{ articlesLength }} articles
            </p>
        </div>

        <div class="flex gap-4 flex-wrap">
            <SupportArticleMini
                v-for="article in articles"
                :key="article.friendlyUrl"
                :article="article"
                class="flex-1 min-w-full xxs:min-w-300p"
            >
            </SupportArticleMini>
        </div>
    </div>
</template>

<script>

import SupportArticleMini from '@/components/supportSite/SupportArticleMini.vue';

import { getFolder } from '@/core/repositories/supportRepository.js';

export default {
    name: 'SupportFolder',
    components: {
        SupportArticleMini,
    },
    mixins: [
    ],
    props: {
        id: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            folder: null,
        };
    },
    computed: {
        folderId() {
            return this.id;
        },
        articlesLength() {
            return this.articles?.length;
        },
        articles() {
            return this.folder.articles;
        },
    },
    methods: {
        async getFolder() {
            if (this.folderId) {
                this.folder = await getFolder(this.folderId);
            }
        },
    },
    watch: {
        folderId() {
            this.getFolder();
        },
    },
    created() {
        this.getFolder();
    },
};
</script>

<style scoped>

/*.o-support-folder {

} */

</style>
