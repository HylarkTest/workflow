<template>
    <component
        :is="outerEl"
        class="o-preset-page hover:shadow-primary-200"
        :class="isSelected ? 'border-primary-600' : 'border-transparent'"
    >
        <img
            class="o-preset-page__image"
            :src="imageSource"
        >

        <div class="mb-6">
            <div class="rounded-xl bg-cm-00 p-3 -my-6 relative x-over">
                <h3 class="text-center text-smbase font-semibold mb-2">
                    {{ pageName }}
                </h3>

                <p class="text-xs text-cm-500">
                    {{ pageDescription }}
                </p>
            </div>
        </div>

        <div
            v-if="isSelected"
            class="o-preset-page__check circle-center"
            :title="$t('common.selected')"
        >
            <i
                class="fas fa-check"
            >
            </i>
        </div>
    </component>
</template>

<script>

export default {
    name: 'PresetPage',
    components: {

    },
    mixins: [
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
        isSelected: Boolean,
        outerEl: {
            type: String,
            default: 'ButtonEl',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        imageSource() {
            return `${import.meta.env.VITE_API_URL}/images/defaultPages/${this.pageIdFormatted}.jpg`;
        },
        pageId() {
            return this.page.id;
        },
        pageIdFormatted() {
            return _.camelCase(this.pageId);
        },
        pageName() {
            return this.page.pageName || this.page.name;
        },
        descriptionPath() {
            return `defaultPages.${this.camelPageType}.${_.camelCase(this.page.id)}`;
        },
        pageType() {
            return this.page.pageType;
        },
        camelPageType() {
            return _.camelCase(this.pageType);
        },
        pageDescription() {
            return this.$t(this.descriptionPath);
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.o-preset-page {
    @apply
        border
        border-solid
        relative
        rounded-xl
        shadow-lg
    ;

    &__image {
        height: 140px;
        transition: 0.2s ease-in-out;

        @apply
            max-w-full
            object-cover
            rounded-t-xl
            w-full
        ;
    }

    &__check {
        right: 6px;
        top: 6px;

        @apply
            absolute
            bg-primary-600
            h-8
            text-cm-00
            w-8
            z-over
        ;
    }
}

</style>
