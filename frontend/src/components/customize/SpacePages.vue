<template>
    <div class="o-space-pages">
        <button
            class="button button-primary mb-8"
            type="button"
            @click="openFullDialog"
        >
            <i
                class="fal fa-memo mr-1"
            >
            </i>

            Add a new page
        </button>

        <SettingsHeaderLine
            v-if="hasFolders"
        >
            <template
                #header
            >
                Current pages
            </template>

            <div>
                <div
                    v-for="folder in space.folders"
                    :key="folder.folder"
                    class="mb-6"
                >
                    <div
                        v-if="folder.folder"
                        class="flex items-center text-cm-500 mb-2"
                    >
                        <i
                            class="fal fa-folder mr-2"
                        >
                        </i>

                        <h3>
                            {{ getFolderName(folder.folder) }}
                        </h3>
                    </div>

                    <div class="ml-4">
                        <PageLine
                            v-for="page in folder.pages"
                            :key="page.id"
                            class="mb-1"
                            :page="page"
                        >

                        </PageLine>
                    </div>
                </div>
            </div>
        </SettingsHeaderLine>

        <PageWizardDialog
            v-if="isDialogOpen"
            :space="space"
            @closeFullDialog="closeFullDialog"
        >
        </PageWizardDialog>
    </div>
</template>

<script>

import PageLine from './PageLine.vue';

import interactsWithNewPageDialog from '@/vue-mixins/customizations/interactsWithNewPageDialog.js';

export default {
    name: 'SpacePages',
    components: {
        PageLine,
    },
    mixins: [
        interactsWithNewPageDialog,
    ],
    props: {
        space: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        hasFolders() {
            return this.space.folders.length > 0;
        },
    },
    methods: {
        getFolderName(folder) {
            return folder.slice(0, -1);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-space-pages {
}*/

</style>
