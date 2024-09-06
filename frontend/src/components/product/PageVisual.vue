<template>
    <div
        class="o-page-visual relative"
    >
        <!-- <div>
            <img
                class="o-page-visual__image"
                :src="imageSource"
            >
        </div> -->
        <div class="p-4">
            <div class="flex justify-center mb-2">
                <div class="bg-primary-200 h-12 w-12 circle-center shadow-lg">
                    <i
                        class="fa-duotone fa-fw text-xl"
                        :class="page.symbol"
                        :style="duotoneColors(accentColor)"
                    >
                    </i>
                </div>
            </div>

            <p class="font-semibold text-center">
                {{ page.pageName || page.name }}

            </p>

            <p
                class="text-xs text-cm-600 text-center mt-1"
            >
                {{ pageDescription }}
            </p>
        </div>
        <button
            v-if="pairMatchesLength"
            class="o-page-visual__match"
            :class="bgColorClass"
            type="button"
            @click="openRefine"
        >
            {{ hasMultipleMatches ? 'I have potential matches' : 'I have a potential match' }}
        </button>

        <div
            v-if="page.hasBeenMerged || page.newMainPage"
            class="o-page-visual__match bg-cm-600"
        >
            <i
                class="far fa-merge mr-1"
            >
            </i>
            {{ page.hasBeenMerged ? 'Merged' : 'New after merge' }}
        </div>

    </div>
</template>

<script>

import providesColors from '@/vue-mixins/style/providesColors.js';

// import { generateText } from '@/core/helpers/waffleGenerator.js';

// import FeatureItem from '@/components/product/FeatureItem.vue';

const images = [
    'contacts.png',
    'sales.png',
    'crm.png',
];

export default {
    name: 'PageVisual',
    components: {
        // FeatureItem,
    },
    mixins: [
        providesColors,
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
        tagGroups: {
            type: Array,
            default: () => [],
        },
        categories: {
            type: Array,
            default: () => [],
        },
        mergePairsObj: {
            type: [Object, null],
            required: true,
        },
    },
    emits: [
        'openRefine',
    ],
    data() {
        return {

        };
    },
    computed: {
        imageSource() {
            const random = _.random(0, 2);
            const image = images[random];
            return `/images/thumbnails/${image}`;
        },
        // descriptionPath() {
        //     return `defaultPages.${this.camelPageType}.${_.camelCase(this.page.id)}`;
        // },
        // pageType() {
        //     return this.page.pageType;
        // },
        // camelPageType() {
        //     return _.camelCase(this.pageType);
        // },
        pageDescription() {
            // return this.$t(this.descriptionPath);
            return this.page.description;
        },
        pairMatches() {
            const mergeKeys = _.keys(this.mergePairsObj);
            return _.intersection(mergeKeys, this.page.mergeIds);
        },
        pairMatchesLength() {
            return this.pairMatches.length;
        },
        firstMatch() {
            return this.mergeObjects?.[0];
        },
        hasMultipleMatches() {
            return this.pairMatchesLength > 1;
        },
        mergeObjects() {
            return this.pairMatches.map((match) => {
                return this.mergePairsObj[match];
            });
        },
        bgColor() {
            if (this.hasMultipleMatches) {
                return 'gold';
            }
            return this.firstMatch?.color;
        },
        bgColorClass() {
            return `bg-${this.bgColor}-600`;
        },
    },
    methods: {
        getGroup(tagGroup) {
            return _.find(this.tagGroups, { id: tagGroup });
        },
        getTags(tagGroup) {
            const group = this.getGroup(tagGroup);
            return group.tags;
        },
        relationshipWord(type, index) {
            const word = type.split('_TO_')[index];
            return word;
        },
        getCategories(category) {
            return _.find(this.categories, { id: category }).items;
        },
        pageId(pageId) {
            return _.camelCase(pageId);
        },
        openRefine() {
            this.$emit('openRefine', this.mergeObjects);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-page-visual {
    @apply
        bg-primary-100
        h-full
        rounded-lg
    ;

    &__match {
        @apply
            absolute
            font-bold
            px-2
            py-0.5
            -right-2
            rounded-full
            text-cm-00
            text-xs
            -top-2
        ;
    }

    /*&__image {
        height: 120px;

        @apply
            mx-auto
        ;
    }*/
}

</style>
