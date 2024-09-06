<template>
    <div
        v-dompurify-html:embed="content"
        class="o-support-article-content"
    >
    </div>
</template>

<script>

export default {
    name: 'SupportArticleContent',
    components: {
    },
    mixins: [
    ],
    props: {
        content: {
            type: String,
            required: true,
        },
    },
};
</script>

<style>

/*
Cannot use tailwind @apply here as this is used in Nova which does not have
access to tailwind utilities.
 */
.o-support-article-content {
    font-family: Figtree, sans-serif !important;

    em {
        font-style:  italic;
    }

    ol {
        list-style: revert;
        margin-left: 40px;
    }

    ul {
        list-style: revert;
        margin-left: 40px;
    }

    h1 {
        font-size: 1.75rem;
        font-weight: 600;
        line-height: 2.125rem;
        margin-bottom: 0.75rem;
    }

    h2 {
        color: var(--hl-primary-color-900);
        font-size: 1.5rem;
        line-height: 2rem;
        margin-bottom: 0.5rem;
    }

    h3 {
        color: var(--hl-primary-color-600);
        font-size: 1.125rem;
        line-height: 1.75rem;
        margin-bottom: 0.25rem;
    }

    h4 {
        color: var(--hl-cm-color-600);
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.5rem;
    }

    a, .article-link {
        color: var(--hl-primary-color-500);
        cursor: pointer;
        /* stylelint-disable plugin/no-unsupported-browser-features */
        text-decoration: revert;
        text-decoration-line: underline;

        &:hover {
            color: var(--hl-primary-color-400);
        }
    }

    p {
        font-family: Figtree, sans-serif !important;
        font-size: 16px !important;
        line-height: 1.375;
    }

    span {
        font-family: Figtree, sans-serif !important;
    }

    iframe {
        aspect-ratio: 16 / 9;
        height: unset;
        margin: auto;
        max-width: 800px;
        width: 100%;
    }
}

</style>
