<template>
    <div class="o-tip-tap-input relative">
        <TipTapEditor
            :content="formValue"
            :bgColor="bgColor"
            :editorClasses="editorClasses"
            :isReadOnly="isReadOnly"
            @update:content="emitInput"
        >
        </TipTapEditor>

        <CharactersRemaining
            v-if="showCharsRemaining"
            class="text-right mt-1"
            :length="characterCount"
            :maxLength="10000"
        >
        </CharactersRemaining>

        <transition name="t-fade">
            <AlertTooltip
                v-if="error"
                :alertPosition="{ bottom: '0', right: '-20px' }"
            >
                {{ error }}
            </AlertTooltip>
        </transition>
    </div>
</template>

<script>

import TipTapEditor from './TipTapEditor.vue';
import CharactersRemaining from '@/components/assets/CharactersRemaining.vue';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';

import formWrapperChild from '@/vue-mixins/formWrapperChild.js';

export default {
    name: 'TipTapInput',
    components: {
        TipTapEditor,
        CharactersRemaining,
        AlertTooltip,
    },
    mixins: [
        formWrapperChild,
    ],
    props: {
        bgColor: {
            type: String,
            default: 'white',
            validator(val) {
                return ['white', 'gray'].includes(val);
            },
        },
        editorClasses: {
            type: Array,
            default: () => ['min-h-[200px]', 'max-h-[500px]'],
        },
        isReadOnly: Boolean,
        error: {
            type: String,
            default: '',
        },
        showCharsRemaining: Boolean,
    },
    data() {
        return {
        };
    },
    computed: {
        characterCount() {
            if (!this.formValue) {
                return 0;
            }

            let textLength = 0;
            this.formValue.content.forEach((rootblock) => {
                textLength += this.getContentLength(rootblock, 0);
            });

            const rootblockCount = this.formValue.content.length;
            return textLength + rootblockCount - 1;
        },
    },
    methods: {
        getContentLength(node, runningCount) {
            let count = runningCount;
            if (_.has(node, 'content')) {
                node.content.forEach((childNode) => {
                    count = this.getContentLength(childNode, count);
                });
            } else if (_.has(node, 'text')) {
                count += node.text.length;
            }
            return count;
        },
    },
};
</script>

<style>
.o-tip-tap-editor {
    &__content {
        @apply
            p-1
            rounded-lg
        ;
    }

    &__buttons {
        @apply
            flex
            gap-[2px]
        ;
    }
}
.tiptap-h1 {
    font-size: 2em;
}
.tiptap-h2 {
    font-size: 1.5em;
}
.tiptap-h3 {
    font-size: 1.17em;
}
.tiptap-h4 {
    font-size: 1em;
}
.tiptap-h5 {
    font-size: 0.83em;
}
.tiptap-h6 {
    font-size: 0.67em;
}
</style>
