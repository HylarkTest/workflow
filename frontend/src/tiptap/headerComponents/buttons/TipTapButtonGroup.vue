<template>
    <div class="c-tip-tap-button-group o-tip-tap-editor__buttons">
        <TipTapButton
            v-for="option in getOptionsGroup(groupKey)"
            :key="option.key"
            :option="option"
            :isActive="option.isActive"
            :isDeactivated="isGroupDeactivated || option.isDeactivated"
            @handleClick="option.action"
        >
        </TipTapButton>
    </div>
</template>

<script>
import useTipTapEditor from '@/composables/useTipTapEditor.js';
import useTipTapEditorOptions from '@/composables/useTipTapEditorOptions.js';

import TipTapButton from '@/tiptap/headerComponents/buttons/TipTapButton.vue';

export default {
    name: 'TipTapButtonGroup',
    components: {
        TipTapButton,
    },
    props: {
        groupKey: {
            type: String,
            required: true,
        },
        editor: {
            type: Object,
            required: true,
        },
        isGroupDeactivated: Boolean,
    },
    emits: [
        'openModal',
    ],
    setup(props, context) {
        const {
            isActiveNode,
            runCommands,
        } = useTipTapEditor(props);

        const openModal = (e) => context.emit('openModal', e);

        const {
            getOptionsGroup,
        } = useTipTapEditorOptions(props, openModal);

        return {
            isActiveNode,
            runCommands,
            getOptionsGroup,
        };
    },
};
</script>

<style scoped>
/* .c-tip-tap-button-group {
} */
</style>
