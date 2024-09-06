<template>
    <RouterLink
        class="o-nav-page"
        :to="pageLink"
        :class="{ 'o-nav-page--selected': onLink }"
    >
        <i
            class="fal fa-fw mr-2 mt-1"
            :class="[page.symbol, colorClass]"
        >
        </i>

        <span class="u-hyphen min-w-0">
            {{ pageName }}
        </span>
    </RouterLink>
</template>

<script>

import checksNavLinks from '@/vue-mixins/checksNavLinks.js';

export default {
    name: 'NavPage',
    components: {

    },
    mixins: [
        checksNavLinks,
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        pageVal() {
            return this.page.val || null;
        },
        onLink() {
            return this.isOnLink(this.page, this.pageVal);
        },
        pageLink() {
            return this.getLink(this.page, this.pageVal);
        },
        colorClass() {
            return this.onLink ? 'text-primary-600' : 'text-cm-400';
        },
        pageName() {
            if (this.pageVal) {
                return this.$t(`links.${_.camelCase(this.pageVal)}`);
            }
            return this.page.name;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.o-nav-page {
    transition: 0.2s ease-in-out;

    @apply
        block
        flex
        px-2
        py-1
        rounded-lg
        w-full
    ;

    &:hover:not(.o-nav-page--selected) {
        @apply
            bg-cm-100
        ;
    }

    &--selected {
        @apply
            bg-primary-100
            font-semibold
            -mr-2
            text-primary-600
        ;
    }
}

</style>
