<template>
    <div class="o-support-side relative">
        <div
            class="pl-20 pr-8"
        >
            <router-link
                :to="{ name: 'support.home' }"
                class="o-support-side__header mb-8"
                :class="{ 'o-support-side__header--active': onHome }"
            >
                <div
                    class="o-support-side__square"
                >
                    <i
                        class="fa-regular fa-home fa-fw"
                    >
                    </i>
                </div>

                <p class="font-medium">
                    Home
                </p>
            </router-link>

            <div>
                <div
                    class="o-support-side__header mb-2"
                >
                    <div
                        class="o-support-side__square"
                    >
                        <i
                            class="fa-regular fa-books fa-fw"
                        >
                        </i>
                    </div>

                    <p class="font-medium">
                        Categories
                    </p>
                </div>

                <div
                    class=""
                >
                    <div
                        v-for="category in displayedCategories"
                        :key="category.id"
                        class="mb-2 last:mb-0"
                        :class="{ 'o-support-side__section--open': isCategoryOpen(category.id) }"
                    >
                        <div class="flex justify-between">
                            <div class="o-support-side__category">
                                {{ category.name }}
                            </div>

                            <button
                                class="o-support-side__toggler"
                                type="button"
                                @click="toggleCategory(category.id)"
                            >
                                <i
                                    class="far fa-angle-down"
                                    :class="angleClass(category.id)"
                                >
                                </i>
                            </button>
                        </div>

                        <div
                            v-if="isCategoryOpen(category.id)"
                            class="ml-4 flex flex-col"
                        >
                            <router-link
                                v-for="folder in displayedFolders(category)"
                                :key="folder.id"
                                :to="{ name: 'support.folder', params: { id: folder.id } }"
                                class="o-support-side__folder mb-2 last:mb-0 hover:bg-cm-100"
                                :class="selectedFolderClass(folder.id)"
                            >
                                <span>
                                    {{ folder.name }}
                                </span>

                                <!-- <span
                                    class="italic text-xs text-gray-500"
                                >
                                    ({{ articlesLength(folder) }} articles)
                                </span> -->
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="o-support-side__prompt shadow-primary-400/40"
        >
            <p
                class="block mb-2"
            >
                Not finding what you are looking for?
            </p>

            <button
                class="button--sm button-primary"
                type="button"
                @click="openModal"
            >
                <i class="far fa-message-pen mr-1">
                </i>
                Contact us
            </button>
        </div>

        <SupportContactModal
            v-if="isModalOpen"
            @closeModal="closeModal"
            @closeFormModal="closeModal"
        >
        </SupportContactModal>
    </div>
</template>

<script>

import SupportContactModal from '@/components/support/SupportContactModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import { getCategories } from '@/core/repositories/supportRepository.js';

export default {
    name: 'SupportSide',
    components: {
        SupportContactModal,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {

    },
    data() {
        return {
            openSections: [],
            categories: [],
        };
    },
    computed: {
        route() {
            return this.$route;
        },
        routeName() {
            return this.$route.name;
        },
        routeParams() {
            return this.$route.params;
        },
        onFolder() {
            return this.routeName === 'support.folder';
        },
        onHome() {
            return this.routeName === 'support.home';
        },
        displayedCategories() {
            return this.categories.filter((category) => {
                return category.folders.length && this.foldersHaveArticles(category);
            });
        },
        categoryIds() {
            return this.displayedCategories.map((category) => category.id);
        },
    },
    methods: {
        displayedFolders(category) {
            return category.folders.filter((folder) => this.folderHasArticles(folder));
        },
        foldersHaveArticles(category) {
            return category.folders.some((folder) => this.folderHasArticles(folder));
        },
        folderHasArticles(folder) {
            return folder.articles.length;
        },
        populateOpen() {
            this.openSections = this.categoryIds;
        },
        angleClass(id) {
            return this.isCategoryOpen(id) ? 'fa-angle-up' : 'fa-angle-down';
        },
        isCategoryOpen(id) {
            return this.openSections.includes(id);
        },
        toggleCategory(id) {
            if (this.isCategoryOpen(id)) {
                const index = _.indexOf(this.openSections, id);
                this.openSections.splice(index, 1);
            } else {
                this.openSections.push(id);
            }
        },
        isSelectedFolder(folder) {
            // eslint-disable-next-line
            return this.onFolder && (this.routeParams.id == folder.id);
        },
        selectedFolderClass(id) {
            return this.isSelectedFolder({ id }) ? 'text-primary-600 font-semibold' : '';
        },
        // articlesLength(folder) {
        //     return folder.articles?.length;
        // },
    },
    async created() {
        this.categories = await getCategories();
        this.populateOpen();
    },
};
</script>

<style scoped>

.o-support-side {
    max-width: 300px;
    width: 300px;

    @apply
        bg-cm-50
        border-cm-200
        border-r
        border-solid
        pb-32
        pt-10
    ;

    &__header {
        @apply
            flex
            items-center
            text-smbase
        ;

        &--active {
            @apply
                text-primary-600
            ;

            .o-support-side__square {
                @apply
                    bg-primary-100
                    text-primary-600
                ;
            }
        }
    }

    &__square {
        @apply
            bg-cm-200
            flex
            h-8
            items-center
            justify-center
            mr-3
            rounded-md
            text-base
            text-cm-600
            w-8
        ;
    }

    &__section {
        &--open {
            @apply
                mb-6
            ;

            /*.o-support-side__folder {
                @apply
                    bg-gradient-to-r
                    from-azure-100
                    to-transparent
                ;
            }*/

            .o-support-side__toggler {
                @apply
                    bg-azure-200
                    text-azure-600
                ;
            }
        }
    }

    &__category {
        @apply
            font-semibold
            px-1
            py-0.5
            text-cm-400
            text-xssm
            uppercase
        ;
    }

    &__folder {
        @apply
            flex
            flex-col
            leading-tight
            px-2
            py-0.5
            rounded
            text-xssm

        ;
    }

    &__toggler {
        transition: background-color 0.2s ease-in-out;

        @apply
            bg-cm-00
            flex
            h-4
            items-center
            justify-center
            ml-2
            rounded
            text-sm
            w-4
        ;

        &:hover {
            @apply
                bg-cm-200
            ;
        }
    }

    &__prompt {
        @apply
            bg-azure-100
            border-azure-200
            border-b
            border-r
            border-solid
            border-t
            bottom-4
            fixed
            flex-col
            inline-flex
            items-center
            leading-snug
            left-0
            p-4
            rounded-r-lg
            text-sm
        ;
    }
}

</style>
