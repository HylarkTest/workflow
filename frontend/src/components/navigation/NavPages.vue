<template>
    <div
        class="o-nav-pages"
        :class="paddingClass"
    >
        <div
            v-for="space in groupedPages"
            :key="space.id"
            class="mb-8"
        >
            <h4
                class="o-nav-pages__name"
            >
                {{ space.name }}
            </h4>

            <template v-if="hasFolders(space)">
                <NavFolder
                    v-for="folder in space.folders"
                    :key="folder.folder"
                    v-model:closedFolders="closedFolders"
                    class="my-1"
                    :folder="folder"
                    :isSingle="space.folders.length === 1"
                >
                    <div
                        :class="{ '-mx-2': !folder.folder }"
                    >
                        <NavPage
                            v-for="page in folder.pages"
                            :key="page.id"
                            :page="page"
                            class="o-nav-pages__page"
                            @click="selectPage"
                        >
                        </NavPage>
                    </div>
                </NavFolder>
            </template>

            <template v-else>
                <AddPages
                    class="py-1 px-2"
                    buttonSize="xs"
                    textSize="xs"
                    headerCustomClass="mb-2"
                >
                </AddPages>
            </template>
        </div>

        <div>
            <h4
                v-t="'common.features'"
                class="o-nav-pages__name"
            >
            </h4>

            <div
                class="-mx-2"
            >
                <NavPage
                    v-for="page in featurePages"
                    :key="page.id"
                    :page="page"
                    class="o-nav-pages__page"
                >
                </NavPage>
            </div>
        </div>

        <div class="mt-6 relative">
            <BirdImage
                class="o-nav-pages__bird"
                whichBird="LookBehindBird_72dpi.png"
            >
            </BirdImage>

            <InfoBox
                class="o-nav-pages__customize"
            >
                <div
                    class="mb-2 text-lg uppercase font-bold text-secondary-700"
                >
                    Make it yours
                </div>

                <RouterLink
                    v-t="'links.customize'"
                    :to="{ name: 'customizePage' }"
                    class="o-nav-pages__more"
                >
                </RouterLink>
            </InfoBox>
        </div>
    </div>
</template>

<script>

import NavFolder from './NavFolder.vue';
import NavPage from './NavPage.vue';
import { featurePages } from '@/core/display/typenamesList.js';
import InfoBox from '@/components/branding/InfoBox.vue';
import AddPages from '@/components/customize/AddPages.vue';

import checksNavLinks from '@/vue-mixins/checksNavLinks.js';
import saveState from '@/vue-mixins/saveState.js';
import providesColors from '@/vue-mixins/style/providesColors.js';

export default {
    name: 'NavPages',
    components: {
        AddPages,
        InfoBox,
        NavFolder,
        NavPage,
    },
    mixins: [
        checksNavLinks,
        providesColors,
        saveState,
    ],
    props: {
        spaces: {
            type: Array,
            required: true,
        },
        paddingClass: {
            type: String,
            default: 'px-5 py-3',
        },
    },
    saveState: {
        propertiesForSave: [
            'closedFolders',
        ],
    },
    emits: [
        'selectPage',
    ],
    data() {
        return {
            closedFolders: [],
        };
    },
    computed: {
        featurePages() {
            return featurePages;
        },
        groupedPages() {
            return this.spaces.map((space) => {
                return {
                    id: space.id,
                    name: space.name,
                    folders: this.groupedByFolder(space.pages),
                };
            });
        },
    },
    methods: {
        groupedByFolder(pages) {
            const folders = _.groupBy(pages, 'folder');
            const folderKeys = _.keys(folders);
            return _(folderKeys).map((folderKey) => {
                return {
                    folder: folderKey,
                    pages: folders[folderKey],
                };
            }).sortBy('folder').value();
        },
        hasFolders(space) {
            return space.folders?.length;
        },
        selectPage() {
            this.$emit('selectPage');
        },
    },
    created() {
    },
};
</script>

<style scoped>

.o-nav-pages {
    @apply
        max-h-screen
        overflow-y-auto
        text-xssm
    ;

    &__name {
        @apply
            font-semibold
            mb-1
            text-cm-400
            text-xs
        ;
    }

    &__page {
        @apply
            my-1
        ;
    }

    &__bird {
        height:  50px;
        right:  0;
        top: -30px;

        @apply
            absolute
            z-over
        ;
    }

    &__customize {
        @apply
            block
            p-4
        ;

        /*&:hover {
            @apply
            ;
        }*/
    }

    &__more {
        transition: 0.2s ease-in-out;

        @apply
            bg-cm-00
            block
            border
            border-secondary-600
            border-solid
            font-semibold
            p-2
            rounded-xl
            text-center
            text-cm-400
        ;

        &:hover {
            @apply
                shadow
                text-cm-500
            ;
        }
    }
}

</style>
