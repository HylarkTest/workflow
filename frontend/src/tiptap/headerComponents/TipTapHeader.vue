<template>
    <div class="o-tip-tap-header w-full">
        <div
            v-for="(section, index) in sections"
            :key="section"
            class="o-tip-tap-header__section"
        >
            <component
                ref="section"
                :is="allOptions[section].component"
                :editor="editor"
                :isGroupDeactivated="allOptions[section].isGroupDeactivated"
                :groupKey="section"
                @openModal="openOptionModal($event)"
            >
            </component>

            <div
                v-if="index !== sections.length - 1"
                class="o-tip-tap-header__separator"
            >
            </div>
        </div>
    </div>

    <component
        v-if="isModalOpen"
        :is="modalComponent"
        :editor="editor"
        v-bind="modalProps"
        @closeModal="closeModal"
    >
    </component>
</template>

<script>
import TipTapHeading from '@/tiptap/headerComponents/popups/TipTapHeading.vue';
import TipTapColor from '@/tiptap/headerComponents/popups/TipTapColor.vue';
import TipTapButtonGroup from '@/tiptap/headerComponents/buttons/TipTapButtonGroup.vue';
import TipTapHyperlinkModal from '@/tiptap/headerComponents/modals/TipTapHyperlinkModal.vue';

import useTipTapEditorOptions from '@/composables/useTipTapEditorOptions.js';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

const sectionsPreferences = [
    // TODO: Allow for customization to toolbar; Hide/reorder sections
    'HEADING',
    'STYLE',
    'COLOR',
    'SUPERSCRIPT',
    'ALIGNMENT',
    'INDENT',
    'BLOCK',
    'LIST',
    'RESET',
];

export default {
    name: 'TipTapHeader',
    components: {
        TipTapHeading,
        TipTapColor,
        TipTapButtonGroup,
        TipTapHyperlinkModal,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        editor: {
            type: Object,
            required: true,
        },
    },
    setup(props) {
        const {
            allOptions,
        } = useTipTapEditorOptions(props);

        return {
            allOptions,
        };
    },
    data() {
        return {
            sections: [],
            modalComponent: null,
            modalProps: null,
        };
    },
    computed: {
    },
    methods: {
        openOptionModal({ modalComponent, props = {} }) {
            this.modalComponent = modalComponent;
            this.modalProps = props;
            this.openModal();
        },
    },
    created() {
        this.sections = sectionsPreferences;
    },
};
</script>

<style scoped>
.o-tip-tap-header {
    @apply
        border-b
        border-cm-200
        border-solid
        flex
        flex-wrap
        items-center
        overflow-hidden
        px-2
        text-sm
    ;

    &__section {
        @apply
            flex
            my-1
        ;
    }

    &__separator {
        @apply
            border-cm-200
            border-l
            border-solid
            h-7
            mx-2
        ;
    }
}
</style>
