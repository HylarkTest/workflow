<template>
    <ButtonEl
        class="o-page-card-potential"
        @click="selectPage"
    >
        <img
            class="o-page-card-potential__image"
            :src="imageSource"
        >
        <div class="o-page-card-potential__title">
            <div class="bg-cm-100 opacity-70 absolute w-full h-full rounded-b-xl">
                &nbsp;
            </div>
            <p class="relative text-center text-smbase font-semibold py-1.5 px-2">
                {{ pageName }}
            </p>
        </div>
    </ButtonEl>
</template>

<script>

export default {
    name: 'PageCardPotential',
    components: {
    },
    mixins: [
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'selectPage',
    ],
    data() {
        return {

        };
    },
    computed: {
        pageName() {
            return this.page.pageName || this.page.name;
        },
        imageSource() {
            return `${import.meta.env.VITE_API_URL}/images/defaultPages/${this.pageIdFormatted}.jpg`;
        },
        pageId() {
            return this.page.id;
        },
        pageIdFormatted() {
            return _.camelCase(this.pageId);
        },
    },
    methods: {
        selectPage() {
            this.$emit('selectPage', this.page);
        },
    },
    created() {
    },
};
</script>

<style scoped>
.o-page-card-potential {
    @apply
        relative
        rounded-xl
    ;

    &__image {
        transition: 0.2s ease-in-out;

        @apply
            h-full
            object-cover
            rounded-xl
            w-full
        ;

        &:hover {
            @apply
                opacity-75
            ;
        }
    }

    &__title {
        @apply
            absolute
            bottom-0
            w-full
        ;
    }
}
</style>
