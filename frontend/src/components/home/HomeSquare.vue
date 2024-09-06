<template>
    <RouterLink
        class="o-home-square shadow-primary-600/20 relative"
        :to="pageLink(page)"
    >
        <div
            class="shadow-lg rounded-xl relative"
            :class="imageClass"
        >
            <template
                v-if="pageImage"
            >
                <div
                    class="o-home-square__contrast"
                >

                </div>
                <img
                    class="h-full w-full object-cover rounded-xl"
                    :src="pageImage"
                />
            </template>

            <div
                v-else
                class="centered bg-primary-100 h-full w-full rounded-xl"
            >
                <i
                    class="fa-light fa-fw mr-2 text-4xl text-primary-400"
                    :class="page.symbol"
                >
                </i>
            </div>

            <div
                class="absolute bottom-0 w-full"
            >
                <div class="relative w-full">
                    <div
                        class="o-home-square__overlay"
                    >
                    </div>

                    <div
                        class="flex items-baseline relative px-2 py-1"
                    >
                        <i
                            class="fa-light fa-fw mr-2"
                            :class="page.symbol"
                        >
                        </i>

                        <p class="font-semibold u-ellipsis">
                            {{ page.name }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showContent"
            class="p-3 bg-cm-00 rounded-b-xl flex-1"
        >
            <template
                v-if="recentItemsLength"
            >
                <p
                    v-t="'common.recent'"
                    class="font-bold mb-2"
                >
                </p>
                <div
                    class=""
                >
                    <ItemListing
                        v-for="item in recentItems"
                        :key="item.id"
                        :item="item"
                        class="mb-1 last:mb-0"
                        :pageContext="page"
                    >
                    </ItemListing>
                </div>
            </template>
            <template
                v-if="pageItem"
            >
                <p
                    class="font-bold mb-2"
                >
                    Displaying
                </p>
                <ItemListing
                    :item="pageItem"
                    :pageContext="page"
                >
                </ItemListing>
            </template>
        </div>

        <div
            class="o-home-square__extras min-w-0 max-w-full"
        >
            <div
                class="o-home-square__tag mx-1 shrink-0"
                :class="pageLabel.colorClasses"
                :title="pageLabel.tooltip"
            >
                <i
                    v-if="pageLabel.icon"
                    class="fa-regular mr-1"
                    :class="pageLabel.icon"
                >
                </i>
                {{ pageLabel.title }}
            </div>

            <div class="flex items-baseline min-w-0">
                <div
                    v-if="folder"
                    class="o-home-square__folder"
                >
                    <i class="fa-regular fa-folder mr-2 text-secondary-500">
                    </i>
                    <p class="u-ellipsis">
                        {{ folderName }}
                    </p>
                </div>
                <button
                    class="button--xs button-primary--medium mt-2 mr-2"
                    title="Customize page"
                    type="button"
                    @click.stop.prevent="openPageEdit('PAGE')"
                >
                    <i
                        class="fa-regular fa-sliders-simple text-primary-500 text-xs"
                    >
                    </i>
                </button>
            </div>
        </div>
    </RouterLink>
</template>

<script>

import ItemListing from '@/components/assets/ItemListing.vue';

import interactsWithPageItem from '@/vue-mixins/customizations/interactsWithPageItem.js';
import providesSpaceFolderHelpers from '@/vue-mixins/providesSpaceFolderHelpers.js';

import { symbols } from '@/core/display/typenamesList.js';
import { typeColors } from '@/composables/useDataTypes.js';

export default {
    name: 'HomeSquare',
    components: {
        ItemListing,
    },
    mixins: [
        interactsWithPageItem,
        providesSpaceFolderHelpers,
    ],
    props: {
        space: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'openPageEdit',
    ],
    data() {
        return {

        };
    },
    computed: {
        spaceName() {
            return this.space.name;
        },
        folder() {
            return this.page.folder;
        },
        folderName() {
            return this.getFolderName(this.folder);
        },
        pageImage() {
            return this.page.image;
        },
        recentItems() {
            return this.page.mostRecentItems;
        },
        recentItemsLength() {
            return this.recentItems.length;
        },
        labelTitle() {
            if (this.mappingObj) {
                return this.mappingObj.name;
            }
            return this.pageTypeName;
        },
        labelTooltip() {
            if (this.mappingObj) {
                return 'This is a records page';
            }
            return 'This is a feature page';
        },
        pageLabelName() {
            return this.pageTypeName;
        },
        pageLabel() {
            return {
                colorClasses: this.labelColorClass,
                icon: this.pageTypeSymbol,
                title: this.labelTitle,
                tooltip: this.labelTooltip,
            };
        },
        imageClass() {
            return this.showContent
                ? 'h-28'
                : 'min-h-[7rem] flex-1 max-h-[244px]';
        },
        labelColor() {
            return this.mappingObj
                ? typeColors.ENTITY
                : typeColors.FEATURE_PAGE;
        },
        labelColorClass() {
            return `bg-${this.labelColor}-100 text-${this.labelColor}-500`;
        },
        pageTypeSymbol() {
            if (this.mappingObj) {
                return symbols.BLUEPRINT;
            }
            return symbols[this.pageType];
        },
        showContent() {
            return !!this.pageItem || !!this.recentItemsLength;
        },
        pageItem() {
            return this.page.item;
        },
    },
    methods: {
        pageLink(page) {
            return page.route;
        },
        openPageEdit(selectedView = 'PAGE') {
            const page = {
                ...this.page,
                space: this.space,
            };
            this.$emit('openPageEdit', { page, selectedView });
        },
    },
    created() {
        // this.colors = colors;
    },
};
</script>

<style scoped>

.o-home-square {
    transition: 0.2s ease-in-out;

    @apply
        flex
        flex-col
        h-full
        rounded-xl
        shadow-lg
        w-full
    ;

    &:hover {
        @apply
            shadow-xl
        ;
    }

    &__contrast {
        @apply
            absolute
            bg-cm-1000
            h-full
            left-0
            opacity-20
            rounded-xl
            top-0
            w-full
            z-over
        ;
    }

    &__overlay {
        @apply
            absolute
            bg-cm-00
            h-full
            left-0
            opacity-80
            rounded-b-xl
            top-0
            w-full
            z-0
        ;
    }

    &__extras {
        @apply
            absolute
            flex
            items-center
            justify-between
            left-0
            top-0
            w-full
            z-over
        ;
    }

    &__tag {
        padding: 1px 8px;

        @apply
            font-semibold
            rounded-full
            text-xxs
        ;
    }

    &__folder {
        @apply
            bg-secondary-100
            inline-flex
            items-center
            min-w-0
            mr-1
            mt-1
            px-1
            py-0.5
            rounded
            text-cm-600
            text-xs
        ;
    }
}

</style>
