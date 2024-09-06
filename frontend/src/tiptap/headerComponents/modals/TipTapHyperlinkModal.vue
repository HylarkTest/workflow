<template>
    <Modal
        containerClass="p-8"
        @closeModal="closeModal"
    >
        <p
            v-t="'tiptap.addLink'"
            class="font-semibold text-xl mb-2"
        >
        </p>
        <div class="flex flex-col items-center">
            <FormWrapper
                :form="form"
                @submit="submitForm"
            >
                <div class="mb-4">
                    <InputLine
                        formField="url"
                        name="url"
                        :motion="true"
                        type="text"
                        autocomplete="off"
                    >
                        <template #label>
                            {{ $t('labels.url') }}
                        </template>
                    </InputLine>
                </div>

                <div class="mb-8">
                    <InputLine
                        formField="text"
                        name="text"
                        :motion="true"
                        type="text"
                        autocomplete="off"
                    >
                        <template #label>
                            {{ $t('labels.text') }}
                        </template>
                    </InputLine>
                </div>

                <div class="flex mt-2 justify-end">
                    <button
                        v-t="'common.add'"
                        type="submit"
                        class="button button-primary"
                        :class="{ unclickable: !hasUrl }"
                        :disabled="!hasUrl"
                    >
                    </button>
                </div>
            </FormWrapper>
        </div>
    </Modal>
</template>

<script>
import useTipTapEditor from '@/composables/useTipTapEditor.js';

export default {
    name: 'TipTapHyperlinkModal',
    props: {
        editor: {
            type: Object,
            required: true,
        },
        previousUrl: {
            type: String,
            default: '',
        },
        selection: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'closeModal',
    ],
    setup(props) {
        const {
            getTextFromSelection,
            runCommands,
        } = useTipTapEditor(props);

        return {
            runCommands,
            getTextFromSelection,
        };
    },
    data() {
        // let previousUrlFormatted = this.previousUrl;
        // if (this.previousUrl.length) {
        //     if (this.previousUrl.startsWith('//')) {
        //         previousUrlFormatted = `https:${this.previousUrl}`;
        //     } else if (!this.previousUrl.startsWith('https://')) {
        //         previousUrlFormatted = `https://${this.previousUrl}`;
        //     }
        // }

        return {
            form: this.$form({
                url: this.previousUrl,
                text: this.getTextFromSelection(this.selection),
            }),
        };
    },
    computed: {
        hasNoSelectionRange() {
            return this.selection.from === this.selection.to;
        },
        hasText() {
            return this.form.text.length;
        },
        hasUrl() {
            return this.form.url.length;
        },
    },
    methods: {
        hasDoubleSlashes(url) {
            const doubleSlashesRegex = /\/\//;
            return doubleSlashesRegex.test(url);
        },
        previousUrlFormatted() {
            if (this.previousUrl.startsWith('//')) {
                return this.previousUrl.slice(2);
            }
            return this.previousUrl;
        },
        closeModal() {
            this.$emit('closeModal');
        },
        submitForm() {
            const content = this.hasText ? this.form.text : this.form.url;

            const newSelection = {
                from: this.selection.from,
                to: this.selection.from + content.length,
            };

            let formattedHref = this.form.url;

            if (!this.hasDoubleSlashes(this.form.url)) {
                formattedHref = `https://${this.form.url}`;
            }

            this.runCommands((commands) => {
                commands.insertContentAt(this.selection, content);
                commands.setTextSelection(newSelection);
                commands.setLink({ href: formattedHref });
            });

            this.closeModal();
        },
    },
};
</script>

<style>
/* .o-tip-tap-hyperlink-modal {
} */
</style>
