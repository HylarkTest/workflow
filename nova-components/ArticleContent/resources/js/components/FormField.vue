<template>
    <DefaultField :errors="errors" :field="currentField" :full-width-content="fullWidthContent"
                  :show-help-text="showHelpText">
        <template #field>
            <div class="max-w-none">
                <Editor
                    v-model="value"
                    :config="options"
                />
            </div>
        </template>
    </DefaultField>
</template>

<script>
import 'jquery-resizable-dom';
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import 'trumbowyg/dist/ui/trumbowyg.css';
import Editor from './Editor';

import 'trumbowyg/dist/plugins/noembed/trumbowyg.noembed.min.js';
import 'trumbowyg/dist/plugins/emoji/trumbowyg.emoji.min.js';
import 'trumbowyg/dist/plugins/upload/trumbowyg.upload.min.js';
import 'trumbowyg/dist/plugins/fontfamily/trumbowyg.fontfamily.min.js';
import 'trumbowyg/dist/plugins/colors/trumbowyg.colors.min.js';
import 'trumbowyg/dist/plugins/fontsize/trumbowyg.fontsize.min.js';
import '../trumbowygPlugins/articleSearch/articleSearch.js';
import '../trumbowygPlugins/imageManipulate/imageManipulate.js';

export default {
    mixins: [FormField, HandlesValidationErrors],

    components: {
        Editor,
    },

    props: ['resourceName', 'resourceId', 'field'],

    computed: {
        options() {
            return {
                ...this.field.options,
                plugins: {
                    ...this.field.options.plugins,
                    colors: {
                        colorList: [
                            'fee8e6', // peach 100
                            'd72d1d', // peach 600
                            'defcee', // emerald 100
                            '10a25e', // emerald 600
                            'fefccd', // gold 100
                            'cc9200', // gold 600
                            'ebefff', // azure 100
                            '4229ff', // azure 600,
                            'f3f5f7', // gray 100
                            '516176', // gray 600
                            '333c47', // gray 900
                            '000000', // black
                            'ffffff', // white
                        ],
                    },
                },
            };
        },
    },

    methods: {
        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
          this.value = this.field.value || ''
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
          formData.append(this.field.attribute, this.value || '')
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
          this.value = value
        },
    },
}
</script>

<style lang="scss">
.trumbowyg-editor {
    color: black;
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

    h2 {
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
        max-width: 500px;
        width: 100%;
    }
}
</style>
