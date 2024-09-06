<template>
    <section class="o-space-visual">
        <h2 class="o-space-visual__title">
            {{ space.name }}
        </h2>

        <div class="mb-8">
            <h4
                v-t="'registration.confirm.selectedUses'"
                class="o-space-visual__secondary"
            >
            </h4>
            <ul>
                <li
                    v-for="contributor in space.contributors"
                    :key="contributor.val"
                    class="o-space-visual__contributor"
                >
                    <i
                        class="fas fa-check mr-2 text-primary-600"
                    >
                    </i>

                    <p
                        v-md-text="$t(contributorName(contributor))"
                        class="text-cm-700 text-smbase"
                    >
                    </p>
                </li>
            </ul>
        </div>

        <div
            v-if="hasSamePairs || werePagesModified"
            class="bg-secondary-100 p-4 rounded-xl mb-8 border-solid border border-secondary-600"
        >
            <p class="font-semibold mb-1">
                Fine-tune your account!
            </p>
            <template
                v-if="hasSamePairs"
            >
                <p class="text-sm text-cm-600">
                    A few of your chosen templates include potentially similar record types.
                </p>

                <p class="text-sm text-cm-600">
                    You can leave these record types separate or merge them together,
                    depending on how you want to use them.
                </p>
            </template>
            <p
                v-if="!hasSamePairs && werePagesModified"
            >
                You've merged all available record types.
            </p>

            <div class="mt-4 flex">
                <button
                    v-if="hasSamePairs"
                    class="button button-secondary mr-2"
                    type="button"
                    @click="openRefine(samePairsArr)"
                >
                    Review similar records
                </button>

                <button
                    v-if="werePagesModified"
                    class="button button-gray"
                    type="button"
                    @click="resetToDefault"
                >
                    Reset to default
                </button>
            </div>
        </div>

        <div class="">
            <h4
                v-t="'common.pages'"
                class="o-space-visual__secondary"
            >
            </h4>

            <ul>
                <li
                    v-for="folder in orderedFolders"
                    :key="folder[0]"
                    class="o-space-visual__folder"
                >
                    <div
                        v-if="folder[0] !== 'undefined'"
                        class="flex items-center mb-2 py-1"
                    >
                        <i
                            class="fal fa-folder mr-3"
                        >
                        </i>

                        <h5
                            class="o-space-visual__tertiary"
                        >
                            {{ folderLabel(folder[0]) }}
                        </h5>
                    </div>

                    <div
                        class="o-space-visual__pages"
                    >

                        <div
                            v-for="page in folder[1]"
                            :key="page.id"
                            class="o-space-visual__item w-full md:w-1/2 lg:w-1/3"
                        >
                            <PageVisual
                                :page="page"
                                :mergePairsObj="samePairs"
                                class="o-space-visual__page"
                                v-bind="$attrs"
                                @openRefine="openRefine"
                            >
                            </PageVisual>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <RefineMatchesModal
            v-if="isModalOpen"
            :matches="editedMatches"
            :samePairs="samePairs"
            @closeModal="closeRefine"
            @updateNewPages="updateSpaceAfterMerge"
        >
        </RefineMatchesModal>
    </section>
</template>

<script>

import PageVisual from '@/components/product/PageVisual.vue';
import RefineMatchesModal from '@/components/customize/RefineMatchesModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

const colorArr = [
    'emerald',
    'violet',
    'turquoise',
    'sky',
];

export default {
    name: 'SpaceVisual',
    components: {
        PageVisual,
        RefineMatchesModal,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        space: {
            type: Object,
            required: true,
        },
        base: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'updateSpaceAfterMerge',
        'resetMerge',
    ],
    data() {
        return {
            editedMatches: null,
        };
    },
    computed: {
        folders() {
            return _(this.pages).groupBy((page) => {
                return page.folder || undefined;
            }).value();
        },
        formattedFolders() {
            return _.toPairs(this.folders);
        },
        orderedFolders() {
            return _(this.formattedFolders).sortBy((folder) => {
                if (!folder[0]) {
                    return 0;
                }
                return folder[1].length;
            }).value();
        },
        baseType() {
            return this.base.baseType;
        },
        baseTypeFormatted() {
            return _.camelCase(this.baseType);
        },
        // folderPages() {
        //     return this.pages.filter((page) => {
        //         return page.folder;
        //     });
        // },
        // noFolderPages() {
        //     return this.pages.filter((page) => {
        //         return !page.folder;
        //     });
        // },
        pages() {
            return this.space.pages;
        },
        mergeablePages() {
            return this.pages.filter((page) => {
                return page.mergeIds?.length
                    && !page.hasBeenMerged
                    && !page.newMainPage
                    && !page.subset;
            });
        },
        samePairs() {
            if (!this.mergeablePages?.length) {
                return null;
            }

            const possibleMergeIds = _(this.mergeablePages).flatMap((page) => {
                return page.mergeIds;
            }).uniq().value();
            const possiblePairs = {};
            let colorIndex = 0;
            possibleMergeIds.forEach((id) => {
                const idPages = this.mergeablePages.filter((page) => {
                    return page.mergeIds.includes(id);
                });
                if (idPages.length > 1) {
                    possiblePairs[id] = {
                        color: colorArr[colorIndex],
                        pages: idPages,
                        mergeVal: id,
                    };
                    colorIndex += 1;
                }
            });
            return possiblePairs;
        },
        samePairsArr() {
            return _.toArray(this.samePairs);
        },
        hasSamePairs() {
            return !_.isEmpty(this.samePairs);
        },
        werePagesModified() {
            return this.pages.some((page) => {
                return page.hasBeenMerged;
            });
        },
    },
    methods: {
        folderLabel(key) {
            return key;
        },
        contributorName(contributor) {
            const formattedVal = _.camelCase(contributor.val);
            return `registration.uses.headers.${this.baseTypeFormatted}.${formattedVal}`;
        },
        closeRefine() {
            this.editedMatches = null;
            this.closeModal();
        },
        openRefine(matches) {
            this.editedMatches = matches;
            this.openModal();
        },
        updateSpaceAfterMerge(newPages) {
            this.$emit('updateSpaceAfterMerge', { newPages, space: this.space });
        },
        resetToDefault() {
            this.$emit('resetMerge', this.space);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-space-visual {
    &__title {
        @apply
            font-semibold
            mb-4
            text-2xl
        ;
    }

    &__secondary {
        @apply
            font-semibold
            mb-1
            text-lg
        ;
    }

    &__tertiary {
        @apply
            font-semibold
            text-cm-700
            text-sm
        ;
    }

    &__contributor {
        @apply
            flex
            items-center
            my-1
        ;
    }

    &__circle {
        height: 30px;
        width: 30px;

        @apply
            bg-primary-600
            mr-4
            rounded-full
            text-cm-00
        ;
    }

    &__folder:not(:last-child) {
        @apply
            mb-6
        ;
    }

    &__pages {
        @apply
            flex
            flex-wrap
            -m-2
        ;
    }

    &__item {
        @apply
            p-2
        ;
    }
}

</style>
