<template>
    <div
        class="o-use-mini transition-2eio"
        :title="stripped"
    >
        <img
            class="o-use-mini__image"
            :src="imageSrc"
        />

        <p
            v-if="showName"
            v-md-text="title"
            class="text-smbase px-2 flex-1"
        >
        </p>

        <slot>
        </slot>
    </div>
</template>

<script>

import { parseMarkdown, stripTags } from '@/core/utils.js';

export default {
    name: 'UseMini',
    components: {

    },
    mixins: [
    ],
    props: {
        use: {
            type: Object,
            required: true,
        },
        showName: Boolean,
        base: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        formattedVal() {
            return _.camelCase(this.use.val);
        },
        imageSrc() {
            return `${import.meta.env.VITE_API_URL}/images/stockUses/${this.formattedVal}.jpg`;
        },
        title() {
            return this.$t(`registration.uses.headers.${this.baseTypeFormatted}.${this.formattedVal}`);
        },
        textTitle() {
            return this.title.replace(/<[^>]*>/g, '');
        },
        parsed() {
            return parseMarkdown(this.textTitle);
        },
        stripped() {
            return stripTags(this.parsed);
        },
        baseType() {
            return this.base.baseType;
        },
        baseTypeFormatted() {
            return _.camelCase(this.baseType);
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

.o-use-mini {
    @apply
        bg-primary-100
        border
        border-primary-200
        border-solid
        flex
        items-center
        rounded-md
    ;

    &__image {
        height: 30px;
        object-fit: cover;
        transition: 0.2s ease-in-out;
        width: 40px;

        @apply
            rounded-md
        ;
    }
}

</style>
